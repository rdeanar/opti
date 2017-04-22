<?php

namespace Opti;

use Opti\Scenarios\ScenarioRunner;
use Opti\Scenarios\Step;
use Opti\Tools\BaseTool;
use Opti\Tools\Convert;
use Opti\Tools\Identify;
use Opti\Tools\Jpegoptim;
use Opti\Utils\TempFile;
use Psr\Log\LoggerInterface;

class ImageOpti
{
    const FORMAT_JPEG = 'JPEG';
    const FORMAT_PNG = 'PNG';

    protected $tools = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $scenarios = [
        self::FORMAT_JPEG => [
            ['convert:jpeg85', 'convert:default'],
            ['jpegoptim:jpeg85'],
        ],
        //self::FORMAT_PNG  => [],
    ];

    /**
     * ImageOpti constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface &$logger)
    {
        $this->logger = $logger;
        $this->initTools();
    }


    protected function initTools()
    {
        $this->addTool('convert', Convert::class);
        $this->addTool('identify', Identify::class);
        $this->addTool('jpegoptim', Jpegoptim::class);
    }

    protected function addTool($name, $class)
    {
        if (!array_key_exists($name, $this->tools)) {
            $this->tools[$name] = new $class($this->logger);
        }
    }

    /**
     * Get Tool by name
     *
     * @param $name
     *
     * @return BaseTool
     * @throws \Exception
     */
    protected function getTool($name)
    {
        if (array_key_exists($name, $this->tools)) {
            return $this->tools[$name];
        }

        throw new \Exception('Tool ' . $name . ' not found');
    }

    public function process($sourceFilePath)
    {
        try {
            $startTime = microtime(true);
            try {
                $format = $this->getTool('identify')->run('default', ['input' => $sourceFilePath]);
            } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
                $this->logger->error('Can not determinate image format in file: ' . $sourceFilePath . ' Skip.');
                return;
            }

            $this->logger->info('Format detected: ' . $format);

            if (!$this->isFormatValid($format)) {
                $this->logger->error('No tool registered for this format. Skip.');
                return;
            }

            $runner = new ScenarioRunner($this->logger, $this->tools, $sourceFilePath);

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
                copy($mostEffectiveStep->getOutputPath(), $sourceFilePath);
            } else {
                $this->logger->alert('File ' . $sourceFilePath . ' can not be reduced.');
            }

        } finally {
            TempFile::clearAll();
        }
    }

    protected function isFormatValid($format)
    {
        return array_key_exists($format, $this->scenarios);
    }
}