<?php

namespace Opti\tests;

use Opti\File\File;
use Opti\Scenarios\Step;

class StepTest extends TestCase
{

    protected function getStep()
    {
        $config = [
            'bin'      => 'ls',
            'template' => '{options}',
            'configs'  => [
                'default' => [
                    '-lah',
                ],
            ],
        ];

        $logger = new \Psr\Log\NullLogger();
        $tool = new \Opti\Tools\ConfigurableTool($logger);
        $tool->configure($config);

        $step = new Step($logger, $tool, 'default');

        return $step;
    }

    public function testFromFile()
    {
        $step = $this->getStep();

        $filePath = $this->getFilePathFromDataDirectory('images' . DIRECTORY_SEPARATOR . 'Definition_of_Free_Cultural_Works_logo_notext.png');

        $file = new File($filePath);

        $step->fromFile($file);

        $input = $step->getInput();

        $this->assertNotEmpty($input->getPath());
        $this->assertFileExists($input->getPath());
    }
}