<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 23/04/2017
 * Time: 21:51
 */

namespace Opti\tests;


class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return bool Returns `true` if tests runs on the Windows OS
     */
    public function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Get real path of file inside `data` directory
     *
     * @param string $filePath
     *
     * @return string
     */
    public function getFilePathFromDataDirectory($filePath)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $filePath;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getTempFilePath($name)
    {
        $tmpDir = sys_get_temp_dir();

        return $tmpDir . DIRECTORY_SEPARATOR . $name;
    }


    /**
     * Copy file from [[path]] to temp file and return new path
     *
     * @param string $path
     *
     * @return string
     * @throws \Exception
     */
    public function copyFileToTempDir($path)
    {
        if (!file_exists($path)) {
            throw new \Exception('copyFileToTempDir: File not found');
        }

        $name = basename($path);

        $pathTemp = $this->getTempFilePath($name);

        copy($path, $pathTemp);

        return $pathTemp;
    }
}