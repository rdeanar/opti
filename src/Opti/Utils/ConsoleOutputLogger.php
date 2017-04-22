<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 21/04/2017
 * Time: 18:48
 */

namespace Opti\Utils;


use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputLogger extends AbstractLogger
{
    private $output;
    private $traceLevel;
    private $traceAllowed = [];

    /**
     * Logger constructor.
     *
     * @param OutputInterface $output
     * @param string $traceLevel
     */
    public function __construct(&$output, $traceLevel = LogLevel::ERROR)
    {
        $this->output = $output;
        $this->traceLevel = $traceLevel;

        $this->calculateAllowed($traceLevel);
    }


    private function calculateAllowed($traceLevelMax)
    {
        $levels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];

        foreach ($levels as $level) {
            array_push($this->traceAllowed, $level);

            if ($level == $traceLevelMax) {
                break;
            }
        }

    }

    public function log($level, $message, array $context = array())
    {
        if (!in_array($level, $this->traceAllowed)) {
            return;
        }

        // TODO paint $level
        $message = '[ ' . strtoupper($level) . ' ] ' . $message;

        $this->output->writeln($message);
    }
}