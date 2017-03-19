<?php

namespace Pine\Plugin;

use Exception;

class ThemeLocation
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $name
     * @param string $path
     */
    private function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * @param string $name
     * @param string $path
     *
     * @return ThemeLocation
     *
     * @throws Exception when directory does not exist or is not readable.
     */
    public static function create($name, $path)
    {
        if (!is_readable($path) || !is_dir($path)) {
            throw new Exception('Theme directory is not readable');
        }

        return new self($name, $path);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
