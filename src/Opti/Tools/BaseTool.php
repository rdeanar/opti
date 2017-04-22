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

    protected $pipePrefix;
    protected $pipeSuffix = '-';

    /**
     * BaseTool constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface &$logger)
    {
        $this->logger = $logger;
    }

    public function run($config, $arguments)
    {
        if (!is_array($config)) {
            if (isset($this->configs[$config])) {
                $config = $this->configs[$config];
            } else {
                throw new \Exception('Not found configuration ' . $config . ' for ' . __CLASS__);
            }
        }

        $command = $this->buildCommand($config, $arguments);

        $this->logger->info('Run command:' . $command);

        $process = new Process($command);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    private function buildCommand($config, $arguments)
    {
        if (!empty($arguments)) {
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $config = array_merge($config, $arguments);
        }

        $command = $this->binPath . ' ' . implode(' ', $config);

        if ($this->allowPipe) {
            $command = $this->pipePrefix . ' ' . $command . ' ' . $this->pipeSuffix;
        }

        return $command;
    }
}