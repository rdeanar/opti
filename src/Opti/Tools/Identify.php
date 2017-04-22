<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 17/04/2017
 * Time: 22:03
 */

namespace Opti\Tools;

/**
* https://www.imagemagick.org/script/identify.php
*/
class Identify extends BaseTool
{
    public $binPath = '/usr/local/bin/identify';

    public $template = '{options} {input}';

    public $configs = [
        'default' => [
            '-format %m',
        ],
    ];
}