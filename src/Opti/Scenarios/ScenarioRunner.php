<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 20/04/2017
 * Time: 23:02
 */

namespace Opti\Scenarios;


use Opti\File\File;
use Psr\Log\LoggerInterface;

class ScenarioRunner
{
    protected $logger;
    protected $tools;

    protected $file;

    /**
     * Scenario constructor.
     *
     * @param LoggerInterface $logger
     * @param array $tools
     * @param File $file
     */
    public function __construct(LoggerInterface &$logger, &$tools, $file)
    {
        $this->logger = $logger;
        $this->tools = $tools;
        $this->file = $file;
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
                $step->fromFile($this->file);
            } else {
                $step->fromPrevStep($prevStep);
            }

            $step->run();

            $prevStep = $step;

            $this->logger->info($stepConfigString . ' -> ' . $step->getOutputSize());
        }

        return $prevStep;
    }
}