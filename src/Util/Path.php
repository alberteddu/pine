<?php

namespace Pine\Util;

/**
 * @package Pine\Util
 */
class Path
{
    /**
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath($path)
    {
        return realpath($path);
    }

    /**
     * @param $dir
     *
     * @return bool|null
     */
    public static function isDirectoryEmpty($dir)
    {
        if (!is_readable($dir)) return NULL;

        return (count(scandir($dir)) == 2);
    }
}
