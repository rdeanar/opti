<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 18/04/2017
 * Time: 23:56
 */

namespace Opti\Utils;


class TempFile
{
    public static $files = [];

    public static function getTempFilePath()
    {
        $dir = '/dev/shm';
        if (!is_dir($dir)) {
            $dir = sys_get_temp_dir();
        }

        $path = tempnam($dir, 'Opti_');

        array_push(self::$files, $path);

        return $path;
    }

    public static function clearAll()
    {
        foreach (self::$files as $path) {
            @unlink($path);
        }
    }
}