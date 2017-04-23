<?php

namespace Opti\tests\data;

use Opti\Tools\BaseTool;

class BaseToolTestClass extends BaseTool
{
    /**
     * @inheritdoc
     */
    public function buildCommand($options, $arguments)
    {
        return parent::buildCommand($options, $arguments);
    }
}