<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 18/04/2017
 * Time: 16:01
 */

namespace Opti\Commands;

use Opti\Exceptions\InvalidOutputPathException;
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

                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to custom config file to load'),
                new InputOption('verbose', 'v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug.'),
                new InputOption('no-colors', '', InputOption::VALUE_NONE, 'Force no colors in output'),
                new InputOption('out', 'o', InputOption::VALUE_OPTIONAL, 'Output file path or directory for output. In case batch mode can be only directory.'),

                new InputArgument('files', InputArgument::IS_ARRAY | InputArgument::OPTIONAL),
            ])
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $input->getArgument('files');
        $inputFileContent = '';

        if (empty($files)) {
            if (0 === ftell(STDIN)) {
                while (!feof(STDIN)) {
                    $inputFileContent .= fread(STDIN, 1024);
                }
                if (empty($inputFileContent)) {
                    throw new \RuntimeException("Content from STDIN is empty.");
                }
            } else {
                throw new \RuntimeException("Please provide a filename or pipe template content to STDIN.");
            }
        }

        $logger = new ConsoleOutputLogger($output, !$input->getOption('no-colors'));
        $optimizer = new Opti($logger);

        $configFile = $input->getOption('config');

        if (!is_null($configFile)) {
            $optimizer->configureFromFile($configFile);
        }


        if (!empty($files)) {

            $output->writeln([
                'Optimize images',
                '===============',
                '',
            ]);

            try {
                $this->checkAndSetupOutput($optimizer, $input->getOption('out'), count($files) === 1);
            } catch (InvalidOutputPathException $e) {
                $output->writeln('<fg=red>' . $e->getMessage() . '</>');
                return 1;
            }

            foreach ($input->getArgument('files') as $path) {
                $optimizer->processFile($path);
            }
        } else {
            try {
                $this->checkAndSetupOutput($optimizer, $input->getOption('out'), true, true);
            } catch (InvalidOutputPathException $e) {
                $output->writeln('<fg=red>' . $e->getMessage() . '</>');
                return 1;
            }

            if (!$optimizer->isOutputSet()) {
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);

                $result = fputs(STDOUT, empty($outputFileContent) ? $inputFileContent : $outputFileContent);

                if (!$result) {
                    fputs(STDERR, 'Unable to write to stdout.');
                }

            } else {
                $optimizer->processContent($inputFileContent);
            }
        }
    }

    /**
     * Checks output path and sets it to Opti
     *
     * @param Opti $opti
     * @param string $output
     * @param bool $single Is single file or batch mode
     * @param bool $stdin Is STDIN used
     *
     * @throws InvalidOutputPathException
     */
    protected function checkAndSetupOutput(&$opti, $output, $single, $stdin = false)
    {
        if (!empty($output)) {

            if (!$single) {
                if (!is_dir($output)) {
                    throw new InvalidOutputPathException('In batch mode output can be only exists directory');
                }
            }

            if ($stdin && is_dir($output)) {
                throw new InvalidOutputPathException('For STDIN output must be only path to file');
            }

            $checkWritableDir = is_dir($output) ? $output : (file_exists($output) ? $output : dirname($output));

            if (!is_writable($checkWritableDir)) {
                throw new InvalidOutputPathException('Output is not writable. Check: ' . $checkWritableDir);
            }

            $opti->setOutputTo($output);
        }
    }
}