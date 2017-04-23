<?php

namespace Opti\tests;

use Opti\tests\data\BaseToolTestClass;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ToolTest extends TestCase
{

    public function testConfigureStrictException()
    {
        $this->expectException(\Exception::class);

        $config = [
            'bin'      => 'ls',
            'template' => '{options}',
            // missing `configs`
        ];

        $logger = new \Psr\Log\NullLogger();
        $tool = new \Opti\Tools\ConfigurableTool($logger);
        $tool->configure($config);
    }


    public function testConfigureStrictSuccess()
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

        foreach (['bin', 'template', 'configs'] as $property) {
            $this->assertEquals($config[$property], $tool->{$property});
        }
    }


    /**
     * @depends testConfigureStrictSuccess
     */
    public function testCommandBuild()
    {
        $config = [
            'bin'      => 'ls',
            'template' => '{options} {path}',
            'configs'  => [
                'default' => [
                    '-lah',
                ],
            ],
        ];

        $logger = new \Psr\Log\NullLogger();
        $tool = new BaseToolTestClass($logger);
        $tool->configure($config);

        $command = $tool->buildCommand($config['configs']['default'], ['path' => '~/']);

        $this->assertEquals('ls -lah ~/', $command);
    }


    /**
     * @depends testCommandBuild
     */
    public function testRunNonExistentCommandWithFail()
    {
        $this->expectException(ProcessFailedException::class);

        $config = [
            'bin'      => 'non-existent-command',
            'template' => '{options} {path}',
            'configs'  => [
                'default' => [
                    '-lah',
                ],
            ],
        ];

        $logger = new \Psr\Log\NullLogger();
        $tool = new BaseToolTestClass($logger);
        $tool->configure($config);

        $tool->run('default', ['path' => '~/']);
    }


    /**
     * @depends testCommandBuild
     */
    public function testRunSuccess()
    {
        $config = [
            'bin'      => $this->isWindows() ? 'dir' : 'ls',
            'template' => '{options} {path}',
            'configs'  => [
                'default' => [
                    '',
                ],
            ],
        ];

        $logger = new \Psr\Log\NullLogger();
        $tool = new BaseToolTestClass($logger);
        $tool->configure($config);

        $output = $tool->run('default', ['path' => __DIR__]);

        $this->assertNotFalse(strpos($output, basename(__FILE__)), 'Output contains current file name');
    }
}