<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 18/04/2017
 * Time: 23:38
 */

namespace Opti\Scenarios;


use Opti\Tools\BaseTool;
use Opti\Utils\File;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Step
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BaseTool
     */
    private $tool;

    /**
     * @var string
     */
    private $configName;

    /**
     * @var array
     */
    private $config;

    /**
     * @var boolean
     */
    private $virtualInput;

    /**
     * @var string
     */
    private $inputFilePath;

    /**
     * @var string Content of file
     */
    private $inputFile;

    /**
     * @var integer
     */
    private $inputFileSize;

    /**
     * @var string
     */
    private $outpuFilePath;

    /**
     * @var string Content of file
     */
    private $outpuFile;

    /**
     * @var integer
     */
    private $outputFileSize;

    /**
     * Step constructor.
     *
     * @param LoggerInterface $logger
     * @param BaseTool $tool
     * @param string $configName
     */
    public function __construct(LoggerInterface &$logger, &$tool, $configName)
    {
        $this->logger = $logger;
        $this->tool = $tool;
        $this->configName = $configName;
        $this->config = $this->tool->configs[$configName];
    }

    public function fromFile($path)
    {
        $this->inputFilePath = $path;
        $this->inputFileSize = filesize($path);
        $this->virtualInput = false;
    }

    public function fromPrevStep(Step $step)
    {
        if ($step->isVirtual()) {
            $this->inputFile = $step->getOutput();
            $this->virtualInput = true;
        } else {
            // Maybe check if Tool can save to separate file and copy it
            $this->inputFilePath = $step->getOutputPath();
            $this->virtualInput = false;
        }

        $this->inputFileSize = $step->outputFileSize;
    }

    public function isVirtual()
    {
        return $this->tool->allowPipe;
    }

    public function getOutputPath()
    {
        return $this->outpuFilePath;
    }

    public function getOutput()
    {
        if (empty($this->outpuFile) && is_file($this->outpuFilePath)) {
            $this->outpuFile = file_get_contents($this->outpuFilePath);
        }
        return $this->outpuFile;
    }

    public function getInputSize()
    {
        return $this->inputFileSize;
    }

    public function getOutputSize()
    {
        return $this->outputFileSize;
    }

    public function run($format = null)
    {
        // Prepare
        if (!$this->isVirtual()) {
            $this->outpuFilePath = File::getTempFilePath($format);
        }

        // Process
        if ($this->virtualInput != $this->isVirtual() && $this->virtualInput) {
            $this->inputFilePath = File::getTempFilePath($format);
            file_put_contents($this->inputFilePath, $this->inputFile);
        }

        if (!$this->isVirtual()) {

            try {
                $this->tool->run(
                    $this->config,
                    [
                        'input'  => $this->inputFilePath,
                        'output' => $this->outpuFilePath,
                    ]
                );

                $this->outputFileSize = filesize($this->outpuFilePath);

            } catch (ProcessFailedException $e) {
                $this->logger->debug($e->getMessage());
                $this->outputFileSize = 0;
            }

            if ($this->outputFileSize == 0) {
                $this->logger->debug('Error while processing file. Use input file as output.');
                $this->outpuFilePath = $this->inputFilePath;
                $this->outputFileSize = $this->inputFileSize;
            }

        } else {
            throw new \Exception('Pipe is not implemented yet');
        }
    }
}