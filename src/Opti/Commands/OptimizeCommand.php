<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 18/04/2017
 * Time: 16:01
 */

namespace Opti\Commands;

use Opti\Opti;
use Opti\Utils\ConsoleOutputLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('optimize')
            // the short description shown while running "php bin/console list"
            ->setDescription('Optimize given images.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to optimize given images...');


        $this->setDefinition(
            new InputDefinition([
                new InputOption('foo', 'f'),
                //new InputOption('bar', 'b', InputOption::VALUE_REQUIRED),
                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL),
                new InputArgument('files', InputArgument::IS_ARRAY | InputArgument::REQUIRED),
            ])
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Optimize images',
            '============',
            '',
        ]);

        $logger = new ConsoleOutputLogger($output, LogLevel::DEBUG);
        $optimizer = new Opti($logger);

        $configFile = $input->getOption('config');

        if (!is_null($configFile)) {
            $optimizer->configureFromFile($configFile);
        }

        foreach ($input->getArgument('files') as $path) {
            $optimizer->process($path);
//            $output->writeln($result);
        }


//        var_export($input->getArguments());
//        var_export($input->getOptions());

        //$output->writeln('Username: ' . $input->getArgument('username'));
    }
}