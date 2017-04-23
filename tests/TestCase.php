<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 23/04/2017
 * Time: 21:51
 */

namespace Opti\tests;


class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @return bool Returns `true` if tests runs on the Windows OS
     */
    public function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}