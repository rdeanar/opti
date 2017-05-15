<?php

namespace Opti\tests;

use Opti\tests\data\OptiTestClass as Opti;

class OptiTest extends TestCase
{
    /**
     * @return Opti
     */
    protected function getOpti()
    {
        $logger = new \Psr\Log\NullLogger();
        $opti = new Opti($logger);

        return $opti;
    }

    public function testConfigureToolsFromProperty()
    {
        $opti = $this->getOpti();

        $config = [
            'tools' => [
                'test' => [
                    'bin'      => 'testbinpath',
                    'template' => '{options} {input}',
                    'configs'  => [
                        'testconfig' => [
                            '--test',
                        ],
                    ],
                ],
            ],
        ];

        $opti->configure($config);

        $tool = $opti->getTool('test');
        $this->assertNotEmpty($tool);

        // Check tool configs
        $this->assertEquals($config['tools']['test']['configs'], $tool->configs);
        $this->assertEquals($config['tools']['test']['bin'], $tool->bin);
        $this->assertEquals($config['tools']['test']['template'], $tool->template);
    }

    public function testConfigureScenariosReplaceFromProperty()
    {
        $opti = $this->getOpti();

        $config = [
            'scenarios' => [
                Opti::FORMAT_PNG => [
                    'test:testconfig',
                ],
            ],
        ];

        $opti->configure($config);

        $this->assertEquals($config['scenarios'], $opti->getScenarios());
    }


    public function testConfigureScenariosAddFromProperty()
    {
        $opti = $this->getOpti();

        $config = [
            'scenarios+' => [
                Opti::FORMAT_PNG => [
                    'test:testconfig',
                ],
            ],
        ];

        $opti->configure($config);

        $scenarios = $opti->getScenarios();

        // Assert not a copy
        $this->assertNotEquals($config['scenarios+'], $opti->getScenarios());

        foreach ($config['scenarios+'] as $format => $chains) {
            $this->assertArrayHasKey($format, $scenarios);
            foreach ($chains as $chain) {
                $this->assertContains($chain, $scenarios[$format]);
            }
        }
    }

    public function testReadConfigFromFile()
    {
        $opti = $this->getOpti();

        $configPath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'config.yml';

        $config = $opti->readConfigFromFile($configPath);

        $expectedConfig = [
            'scenarios' => [
                'JPEG' => [
                    'test:test',
                ],
            ],
            'tools'     => [
                'test' => [
                    'bin'      => 'testbin',
                    'template' => '{options} {input}',
                    'configs'  => [
                        'test' => [
                            '-testoption',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedConfig, $config, 'Config match');
    }
}