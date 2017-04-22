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
    public $allowPipe = false;

    public $binPath = 'jpegoptim';

    public $template = '{options} {input} --stdout > {output}';

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