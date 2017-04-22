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
    public $binPath;

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

        $this->logger->info('Run command:' . $command);

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
    private function buildCommand($options, $arguments)
    {
        if (is_array($options)) {
            $options = implode(' ', $options);
        }

        $replacements = array_merge(
            ['options' => $options],
            $arguments
        );

        $search = array_map(function ($value) {
            return '{' . $value . '}';
        }, array_keys($replacements));

        $command = $this->binPath . ' ' . str_replace($search, array_values($replacements), $this->template);

        if ($this->allowPipe) {
            $command = $this->pipePrefix . ' ' . $command . ' ' . $this->pipeSuffix;
        }

        return $command;
    }
}