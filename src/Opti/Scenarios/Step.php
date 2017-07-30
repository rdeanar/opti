<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 18/04/2017
 * Time: 23:38
 */

namespace Opti\Scenarios;


use Opti\File\File;
use Opti\Tools\BaseTool;
use Opti\File\TempFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Step
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var BaseTool
     */
    protected $tool;

    /**
     * @var string
     */
    protected $configName;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var File
     */
    protected $inputFile;

    /**
     * @var File
     */
    protected $outputFile;

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

    /**
     * Configure via File object
     *
     * @param $file
     */
    public function fromFile($file)
    {
        $this->inputFile = $file;
    }

    /**
     * Configure vie previous step
     *
     * @param Step $step
     */
    public function fromPrevStep(Step $step)
    {
        $this->inputFile = &$step->outputFile;
    }

    /**
     * @return File input file object
     */
    public function getInput()
    {
        return $this->inputFile;
    }

    /**
     * @return File result file object
     */
    public function getOutput()
    {
        return $this->outputFile;
    }

    /**
     * @return int size of result file
     */
    public function getOutputSize()
    {
        return $this->outputFile->getSize();
    }


    public function run()
    {
        // Prepare
        $this->outputFile = TempFile::create($this->inputFile->getFormat());

        $error = false;
        try {
            $this->tool->run(
                $this->config,
                [
                    'input'  => $this->inputFile->getPath(),
                    'output' => $this->outputFile->getPath(),
                ]
            );

        } catch (ProcessFailedException $e) {
            $this->logger->debug($e->getMessage());
            $error = true;
        }

        if ($error || $this->outputFile->getSize(true) == 0) {
            $this->logger->debug('Error while processing file. Maybe it can not be shrinked? Used input file as output.');
            $this->outputFile = &$this->inputFile;
        }
    }
}