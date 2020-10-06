<?php

namespace Francerz\GithubWebhook;

/**
 * Retrieved from https://www.php.net/manual/en/function.glob.php#119231
 *
 * @param [type] $pattern
 * @param integer $flags
 * @param boolean $traversePostOrder
 * @return void
 */
function rglob ($pattern, $flags = 0, $traversePostOrder = false)
{
    // Keep away the hassles of the rest if we don't use the wildcard anyway
    if (strpos($pattern, '/**/') === false) {
        return glob($pattern, $flags);
    }

    $patternParts = explode('/**/', $pattern);

    // Get sub dirs
    $dirs = glob(array_shift($patternParts) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);

    // Get files for current dir
    $files = glob($pattern, $flags);

    foreach ($dirs as $dir) {
        $subDirContent = rglob($dir . '/**/' . implode('/**/', $patternParts), $flags, $traversePostOrder);

        if (!$traversePostOrder) {
            $files = array_merge($files, $subDirContent);
        } else {
            $files = array_merge($subDirContent, $files);
        }
    }

    return $files;
};