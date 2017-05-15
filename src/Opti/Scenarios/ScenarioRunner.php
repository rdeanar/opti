<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 20/04/2017
 * Time: 23:02
 */

namespace Opti\Scenarios;


use Psr\Log\LoggerInterface;

class ScenarioRunner
{
    protected $logger;
    protected $tools;
    protected $format;

    protected $inputFilePath;

    /**
     * Scenario constructor.
     *
     * @param LoggerInterface $logger
     * @param string $format detected input format
     * @param array $tools
     * @param string $inputFilePath
     */
    public function __construct(LoggerInterface &$logger, &$tools, $format, $inputFilePath)
    {
        $this->logger = $logger;
        $this->tools = $tools;
        $this->format = $format;
        $this->inputFilePath = $inputFilePath;
    }


    /**
     * @param $scenario
     *
     * @return null|Step
     */
    public function runScenario($scenario)
    {
        if (!is_array($scenario)) {
            $scenario = array_map('trim', explode(',', $scenario));
        }

        if (empty($scenario)) {
            $this->logger->error('Scenario is empty');
        } else {
            $this->logger->info('Run scenario ' . implode(' -> ', $scenario));
        }

        $prevStep = null;

        foreach ($scenario as $stepConfigString) {
            list($toolName, $configName) = explode(':', $stepConfigString);

            if (!array_key_exists($toolName, $this->tools)) {
                $this->logger->error('Tool not declared: ' . $toolName);
                return null;
            }

            $step = new Step($this->logger, $this->tools[$toolName], $configName);
            if (is_null($prevStep)) {
                $step->fromFile($this->inputFilePath);
            } else {
                $step->fromPrevStep($prevStep);
            }

            $step->run($this->format);

            $prevStep = $step;

            $this->logger->info($stepConfigString . ' -> ' . $step->getOutputSize());
        }

        return $prevStep;
    }
}