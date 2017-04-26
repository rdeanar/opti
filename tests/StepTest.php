<?php

namespace Opti\tests;

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

        $step->fromFile(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Definition_of_Free_Cultural_Works_logo_notext.png');

        $this->assertNotEmpty($step->getInputSize());
    }
}