<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 17/04/2017
 * Time: 22:03
 */

namespace Opti\Tools;


/**
* https://www.imagemagick.org/script/convert.php
*/
class Convert extends BaseTool
{
    public $binPath = 'convert';

    public $allowPipe = false; //true;

    public $pipeSuffix = '-';

    public $configs = [
        'jpeg85'  => [
            '-sampling-factor 4:2:0',
            '-strip',
            '-quality 85',
        ],
        'default' => [
            '-strip',
        ],
    ];
}