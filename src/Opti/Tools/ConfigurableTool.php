<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 23/04/2017
 * Time: 00:54
 */

namespace Opti\Tools;


class ConfigurableTool extends BaseTool
{
    /**
     * @inheritdoc
     */
    public function configure($config, $strict = true)
    {
        return parent::configure($config, $strict);
    }
}