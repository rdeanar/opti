<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 18/04/2017
 * Time: 14:23
 */

namespace Opti;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Application extends BaseApplication
{

    public function __construct()
    {
        static $shutdownRegistered = false;

        if (!$shutdownRegistered) {
            $shutdownRegistered = true;

            register_shutdown_function(function () {
                $lastError = error_get_last();

                if ($lastError && $lastError['message'] &&
                    (strpos($lastError['message'], 'Allowed memory') !== false /*Zend PHP out of memory error*/ ||
                        strpos($lastError['message'], 'exceeded memory') !== false /*HHVM out of memory errors*/)) {
                    echo "\n". 'Shit happens.';
                }
            });
        }

        parent::__construct('Composer', '0.0.1');
    }

    /**
     * Initializes all the composer commands.
     */
    protected function getDefaultCommands()
    {
        $commands = array_merge(parent::getDefaultCommands(), array(
            new Commands\OptimizeCommand(),
        ));

        return $commands;
    }
}