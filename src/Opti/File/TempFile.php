<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 18/04/2017
 * Time: 23:56
 */

namespace Opti\File;


class TempFile
{
    /**
     * @var array of all created temp files
     */
    public static $temp_files = [];

    /**
     * Generate full path to temp file.
     * If memory FS available, temp file will be created inside of it
     *
     * @param null|string $ext If not `null` returned temp file name will be with given extension
     *
     * @return bool|string
     */
    public static function getTempFilePath($ext = null)
    {
        $dir = '/dev/shm';
        if (!is_dir($dir)) {
            $dir = sys_get_temp_dir();
        }

        $path = tempnam($dir, 'Opti_');
        array_push(self::$temp_files, $path);

        if (!empty($ext)) {
            $path .= '.' . strtolower($ext);
            array_push(self::$temp_files, $path);
        }

        return $path;
    }

    /**
     * Unlink all created temp files
     */
    public static function clearAll()
    {
        foreach (self::$temp_files as $path) {
            @unlink($path);
        }
        self::$temp_files = [];
    }

    /**
     * Detect file extension by its content.
     * Useful for tools, which can not process files without extensions.
     *
     * @param $content
     *
     * @return bool|mixed|string
     */
    public static function getStreamFileExtension($content)
    {
        $finfo = new \finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($content);

        $check_svg = false;

        if ($mime === false) {
            $check_svg = true;
        }

        $mime = explode('; ', $mime);
        $mime = array_shift($mime);

        if ($mime == 'text/plain') {
            $check_svg = true;
        }

        if ($check_svg) {
            if (strpos($content, '</svg>') !== false) {
                return 'svg';
            } else {
                return false;
            }
        } else {
            $repository = new \Dflydev\ApacheMimeTypes\PhpRepository;
            $extensions = $repository->findExtensions($mime);

            return empty($extensions) ? false : array_shift($extensions);
        }
    }


    /**
     * @param null|string $ext
     * @param null|string $content
     *
     * @return File
     */
    public static function create($ext = null, $content = null)
    {
        if (is_null($ext) && !is_null($content)) {
            $ext = self::getStreamFileExtension($content);
        }

        $path = self::getTempFilePath($ext);

        $file = new File($path);
        if (!is_null($content)) {
            $file->setContent($content, true);
        }

        return $file;
    }
}