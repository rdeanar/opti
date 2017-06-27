<?php

namespace Opti;

use Opti\Scenarios\ScenarioRunner;
use Opti\Scenarios\Step;
use Opti\Tools\BaseTool;
use Opti\Tools\ConfigurableTool;
use Opti\Tools\Convert;
use Opti\Tools\Identify;
use Opti\Tools\Jpegoptim;
use Opti\Utils\File;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Opti
{
    const FORMAT_JPEG = 'JPEG';
    const FORMAT_PNG = 'PNG';
    const FORMAT_SVG = 'SVG';

    protected $tools = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $scenarios = [];

    /**
     * Opti constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface &$logger)
    {
        $this->logger = $logger;
        $this->initTools();

        $this->configureFromFile(__DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'config.yml');
    }

    /**
     * @return array
     */
    public function getScenarios()
    {
        return $this->scenarios;
    }

    protected function initTools()
    {
        $this->addTool('identify', Identify::class);
    }

    protected function addTool($name, $class, $definition = null)
    {
        if (!array_key_exists($name, $this->tools)) {
            $this->tools[$name] = new $class($this->logger);

            if ($definition) {
                $this->tools[$name]->configure($definition, true);
            }
        }
    }

    /**
     * Get Tool by name
     *
     * @param string $name
     * @param bool $silent If `true` no exception will be throwed if tool not found
     *
     * @return bool|BaseTool
     * @throws \Exception
     */
    public function getTool($name, $silent = false)
    {
        if (array_key_exists($name, $this->tools)) {
            return $this->tools[$name];
        }

        if ($silent) {
            return false;
        }

        throw new \Exception('Tool ' . $name . ' not registered');
    }

    /**
     * Reads file and populate config from it
     *
     * @param string $filePath
     */
    public function configureFromFile($filePath)
    {
        $this->logger->info('Configure from file: ' . $filePath);

        $config = $this->readConfigFromFile($filePath);
        if ($config) {
            $this->configure($config);
        }
    }

    /**
     * Read config from file and parse it
     * Return `false` if error occurs.
     *
     * @param $filePath
     *
     * @return bool
     */
    protected function readConfigFromFile($filePath)
    {
        if (!file_exists($filePath)) {
            $this->logger->error('Config file not found: ' . $filePath);
            return false;
        }

        try {
            $config = Yaml::parse(file_get_contents($filePath));

            if (!isset($config['opti'])) {
                $this->logger->error('No config found in file: ' . $filePath . '. Put all configuration under \'opti\' section.');
                return false;
            } else {
                return $config['opti'];
            }

        } catch (ParseException $e) {
            $this->logger->error("Unable to parse the YAML string: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fulfill settings from config
     *
     * @param $config
     * @param bool $replace
     */
    public function configure($config, $replace = false)
    {
        if (!empty($config['tools'])) {
            foreach ($config['tools'] as $name => $definition) {

                $this->logger->debug('Add new configurable tool: ' . $name);

                if ($tool = $this->getTool($name, true)) {
                    $tool->configure($definition, false);
                } else {
                    $this->addTool($name, ConfigurableTool::class, $definition);
                }
            }
        }

        if (!empty($config['scenarios'])) {
            $this->scenarios = $config['scenarios'];
            $this->logger->debug('Fulfill scenarios in replace mode');
        }

        if (!empty($config['scenarios+'])) {
            $this->scenarios = array_merge_recursive($this->scenarios, $config['scenarios+']);
            $this->logger->debug('Fulfill scenarios in append mode');
        }
    }


    /**
     * Optimize file by its content.
     * Returns `null` if file can not be shrinked, `false` if can not determine its type or optimized contend otherwise.
     *
     * @param string $content File content
     *
     * @return bool|null|string
     * @throws \Exception
     */
    public function processContent($content)
    {
        if (mb_strlen($content) == 0) {
            throw new \Exception('Empty content');
        }

        $ext = File::getStreamFileExtension($content);

        if (!$ext) {
            return false;
        }

        $filePath = File::getTempFilePath($ext);

        file_put_contents($filePath, $content);

        return $this->processFile($filePath, true);
    }

    /**
     * If `$return` if `false`, method returns nothing.
     * Otherwise it returns optimized file content. If image can not be shrinked returns `null`
     *
     * @param string $sourceFilePath
     * @param bool $return
     *
     * @return null|string|void
     */
    public function processFile($sourceFilePath, $return = false)
    {
        try {
            $startTime = microtime(true);
            try {
                $format = $this->getTool('identify')->run('default', ['input' => $sourceFilePath]);
            } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
                $this->logger->error('Can not determinate image format in file: ' . $sourceFilePath . ' Skip.');
                return;
            }

            $this->logger->info('Format detected: ' . $format . ' for file: ' . $sourceFilePath);

            if (!$this->isFormatRegistered($format)) {
                $this->logger->error('No tool registered for ' . $format . ' format. File: ' . $sourceFilePath . '. Skip.');
                return;
            }

            $runner = new ScenarioRunner($this->logger, $this->tools, $format, $sourceFilePath);

            /** @var null|Step $mostEffectiveStep */
            $mostEffectiveStep = null;
            foreach ($this->scenarios[$format] as $scenario) {
                $currentScenarioStep = $runner->runScenario($scenario);
                if (is_null($currentScenarioStep)) {
                    continue;
                }
                if (is_null($mostEffectiveStep) OR $mostEffectiveStep->getOutputSize() > $currentScenarioStep->getOutputSize()) {
                    $mostEffectiveStep = $currentScenarioStep;
                }
            }

            if (is_null($mostEffectiveStep)) {
                $this->logger->error('No one scenario was run.');
                return;
            }

            $this->logger->info('Most effective scenario: ' . $mostEffectiveStep->getOutputSize() . ' filePath: ' . $mostEffectiveStep->getOutputPath());

            $sourceFileSize = filesize($sourceFilePath);
            $destFileSize = $mostEffectiveStep->getOutputSize();

            $duration = round(microtime(true) - $startTime, 3);

            if ($sourceFileSize > $destFileSize) {
                $this->logger->alert('File ' . $sourceFilePath . ' reduced: ' . $sourceFileSize . ' -> ' . $destFileSize . ' ' . (round(100 * $destFileSize / $sourceFileSize, 2)) . '% in ' . $duration . 's');

                if ($return) {
                    return $mostEffectiveStep->getOutput();
                } else {
                    copy($mostEffectiveStep->getOutputPath(), $sourceFilePath);
                }
            } else {
                if ($return) {
                    return null;
                } else {
                    $this->logger->alert('File ' . $sourceFilePath . ' can not be reduced.');
                }
            }

        } finally {
            File::clearAll();
        }
    }

    protected function isFormatRegistered($format)
    {
        return array_key_exists($format, $this->scenarios);
    }
}