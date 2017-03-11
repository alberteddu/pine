<?php

/**
 * @see http://stackoverflow.com/a/15575293
 *
 * @return string
 */
function joinPaths() {
    $paths = array();

    foreach (func_get_args() as $arg) {
        if ($arg !== '') { $paths[] = $arg; }
    }

    return preg_replace('#/+#','/',join('/', $paths));
}
