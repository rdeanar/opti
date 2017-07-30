<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 30/07/2017
 * Time: 10:45
 */

namespace Opti\File;


class File
{
    const FORMAT_JPEG = 'JPEG';
    const FORMAT_PNG = 'PNG';
    const FORMAT_SVG = 'SVG';
    const FORMAT_GIF = 'GIF';

    /**
     * @var null|string
     */
    protected $path;
    protected $content;
    protected $size;
    protected $mime;
    protected $ext;
    protected $format;
    protected $isTemp;

    protected $mimeToFormat = [
        'image/jpeg'    => self::FORMAT_JPEG,
        'image/png'     => self::FORMAT_PNG,
        'image/svg+xml' => self::FORMAT_SVG,
        'image/gif'     => self::FORMAT_GIF,
    ];

    public function __construct($path = null)
    {
        if (!is_null($path)) {
            $this->path = $path;
        }
    }

    /**
     * Return path to file
     *
     * @return string
     */
    public function __toString()
    {
        return $this->path;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * @param bool $force
     *
     * @return int
     */
    public function getSize($force = false)
    {
        if (!$force && $this->size) {
            return $this->size;
        }

        if ($this->isExists()) {
            if ($force) {
                clearstatcache(dirname($this->path));
            }
            $this->size = filesize($this->path);
            return $this->size;
        } else {
            return 0;
        }
    }

    /**
     * @return null|string
     */
    public function getMime()
    {
        if (!$this->mime) {
            if ($this->isExists()) {
                $mime = mime_content_type($this->path);

                $mime = explode('; ', $mime);
                $mime = array_shift($mime);

                if ($mime === 'text/plain') {
                    if (strpos($this->getContent(), '</svg>') !== false) {
                        $mime = 'image/svg+xml';
                    }
                }

            } else {
                $mime = null;
            }
            $this->mime = $mime;
        }

        return $this->mime;
    }

    /**
     * Get file format
     *
     * @return null|string
     */
    public function getFormat()
    {
        if (!$this->format) {
            $mime = $this->getMime();

            if (empty($mime) || !array_key_exists($mime, $this->mimeToFormat)) {
                return null;
            }

            $this->format = $this->mimeToFormat[$mime];
        }
        return $this->format;
    }

    /**
     * Get file extension by [[path]]
     *
     * @return string
     */
    public function getExtension()
    {
        if (!$this->ext) {
            $this->ext = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
        }

        return $this->ext;
    }

    public function getContent()
    {
        return file_get_contents($this->path);
    }


    public function isExists()
    {
        return file_exists($this->path);
    }


    // Setters

    /**
     * @param string $content
     * @param bool $write Write content immediately
     */
    public function setContent($content, $write = true)
    {
        $this->content = $content;

        if ($write) {
            $this->writeContent();
        }

        // Clear cache
        $this->mime = null;
        $this->format = null;
        $this->size = null;
    }


    /**
     * Writes [[content]] to [[path]]
     *
     * @param string $path Custom path to write content
     *
     * @throws \InvalidArgumentException
     *
     * @return int|bool
     */
    public function writeContent($path = null)
    {
        if (!empty($this->content)) {

            if (is_null($path)) {
                $path = $this->path;
            }

            if (!empty($path)) {
                return file_put_contents($path, $this->content);
            } else {
                throw new \InvalidArgumentException('Path must not be empty to write content');
            }
        }

        return false;
    }


    /**
     * @param null $path
     */
    public function setPath($path)
    {
        $this->path = $path;

        // Clear cache
        $this->mime = null;
        $this->format = null;
        $this->size = null;
        $this->content = null;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
}