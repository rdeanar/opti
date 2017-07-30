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
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var bool
     */
    protected $color;

    /**
     * Logger constructor.
     *
     * @param OutputInterface $output
     * @param bool $color
     */
    public function __construct(&$output, $color = true)
    {
        $this->output = $output;
        $this->color = $color;
    }

    /**
     * @param string $logLevel
     *
     * @return int
     */
    protected function getMessageVerbosity($logLevel)
    {
        $verbose = [
            LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ALERT     => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::CRITICAL  => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ERROR     => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::WARNING   => OutputInterface::VERBOSITY_VERBOSE,
            LogLevel::NOTICE    => OutputInterface::VERBOSITY_VERBOSE,
            LogLevel::INFO      => OutputInterface::VERBOSITY_VERY_VERBOSE,
            LogLevel::DEBUG     => OutputInterface::VERBOSITY_DEBUG,
        ];

        return array_key_exists($logLevel, $verbose) ? $verbose[$logLevel] : OutputInterface::VERBOSITY_NORMAL;
    }


    protected function paintLevel($message, $level)
    {
        if (!$this->color) {
            return $message;
        }

        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
                return '<fg=red>' . $message . '</>';
                break;
            case LogLevel::WARNING:
                return '<fg=yellow>' . $message . '</>';
                break;
            case LogLevel::NOTICE:
                return '<fg=blue>' . $message . '</>';
                break;
            case LogLevel::INFO:
                return '<fg=cyan>' . $message . '</>';
                break;
            case LogLevel::DEBUG:
                return '<fg=green>' . $message . '</>';
                break;
        }

        return $message;
    }

    public function log($level, $message, array $context = array())
    {
        $verbosity = $this->getMessageVerbosity($level);

        $message = '[ ' . $this->paintLevel(strtoupper($level), $level) . ' ] ' . $message;

        $this->output->writeln($message, $verbosity);
    }
}