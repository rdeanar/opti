<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 17/04/2017
 * Time: 22:03
 */

namespace Opti\Tools;

/**
* https://github.com/glennr/jpegoptim
*/
class Jpegoptim extends BaseTool
{
    public $allowPipe = true;

    public $binPath = 'jpegoptim';

    public $pipeSuffix = '--stdout';

    public $configs = [
        'jpeg85'  => [
            '-p',
            '-s',
            '-m85',
        ],
        'default' => [
            '-s',
        ],
    ];
}