<?php

namespace Opti\Tools;


use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class BaseTool
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public $allowPipe = false;

    /**
     * @var string Path to binary
     */
    public $bin;

    /**
     * @var array tools configurations
     */
    public $configs = [];

    /**
     * @var string template of command call
     */
    public $template = '{options} {input} {output}';

    protected $pipePrefix;
    protected $pipeSuffix;

    /**
     * BaseTool constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface &$logger)
    {
        $this->logger = $logger;
    }

    public function run($options, $arguments)
    {
        if (!is_array($options)) {
            if (isset($this->configs[$options])) {
                $options = $this->configs[$options];
            } else {
                throw new \Exception('Not found configuration ' . $options . ' for ' . __CLASS__);
            }
        }

        $command = $this->buildCommand($options, $arguments);

        $this->logger->debug('Run command: ' . $command);

        $process = new Process($command);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @param $options
     * @param array $arguments [inputFilePath, outputFilePath]
     *
     * @return string
     */
    protected function buildCommand($options, $arguments)
    {
        if (is_array($options)) {
            $options = implode(' ', $options);
        }

        $arguments = array_map('escapeshellarg', $arguments);

        $replacements = array_merge(
            ['options' => $options],
            $arguments
        );

        $search = array_map(function ($value) {
            return '{' . $value . '}';
        }, array_keys($replacements));

        $command = $this->bin . ' ' . str_replace($search, array_values($replacements), $this->template);

        if ($this->allowPipe) {
            $command = $this->pipePrefix . ' ' . $command . ' ' . $this->pipeSuffix;
        }

        return $command;
    }

    /**
     * Fulfill by config
     *
     * @param array $config
     * @param bool $strict
     *
     * @return $this
     * @throws \Exception
     */
    public function configure($config, $strict = false)
    {
        foreach (['bin', 'template', 'configs'] as $option) {

            if (empty($config[$option])) {
                if ($strict) {
                    throw new \Exception('Required option is missing: ' . $option);
                }
            } else {
                $this->logger->debug('Option "' . $option . '" = ' . var_export($config[$option], true));

                if (is_array($config[$option])) {
                    $this->{$option} = array_merge($this->{$option}, $config[$option]);
                } else {
                    $this->{$option} = $config[$option];
                }
            }
        }

        return $this;
    }
}