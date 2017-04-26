<?php

namespace Opti\tests\data;

use Opti\Opti;

class OptiTestClass extends Opti
{
    /**
     * @inheritdoc
     */
    public function readConfigFromFile($filePath)
    {
        return parent::readConfigFromFile($filePath);
    }
}