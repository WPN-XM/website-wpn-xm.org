<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2014 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * Header redirection script
 * -------------------------
 * The script provides a header response to a software and version request
 * in form of a header redirection to the download url.
 */

// load software components registry
$registry = include __DIR__ . '/registry/wpnxm-software-registry.php';

// ensure registry array is available
if (!is_array($registry)) {
    header("HTTP/1.0 404 Not Found");
}

// $_GET['s'] = software component
$s = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);
// $_GET['v'] = version
$v = filter_input(INPUT_GET, 'v', FILTER_SANITIZE_STRING);
$v = ($v === 'latest') ? null : $v; // unset any latest requests, it defaults to latest

// does the requested software exist in our registry?
if (!empty($s) && array_key_exists($s, $registry)) {

    /**
     * If the software component is a PHP extension, then
     * we have to take the php_version into account, when fetching the url.
     * The version => url relationship has one level more: version => php_version => url.
     */
    if(strpos($s, 'phpext_') !== false) {
        // $_GET['p'] = php version for extensions, default version is php 5.5
        $p = ($p = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_STRING)) ? $p : '5.5';
        // $_GET['bitsize'] = php bitsize for extensions, default version is x86
        $bitsize = ($bitsize = filter_input(INPUT_GET, 'bitsize', FILTER_SANITIZE_STRING)) ? $bitsize : 'x86';

        // does the requested version exist?
        if (!empty($v) && array_key_exists($v, $registry[$s]) && array_key_exists($p, $registry[$s][$v][$bitsize][$p])) {
            // yes, return download url
            header("Location: " . $registry[$s][$v][$bitsize][$p]); // e.g. $registry['phpext_xdebug']['1.2.1']['x86']['5.5'];
        } elseif(array_key_exists($p, $registry[$s]['latest']['url'][$bitsize])) {
            // no, requested version not existing, return latest version for php default version instead
            header("Location: " . $registry[$s]['latest']['url'][$bitsize][$p]); // e.g. $registry['phpext_xdebug']['latest']['url']['x86']['5.5'];
        } else {
            // software does not exist, download will fail.
            header("HTTP/1.0 404 Not Found");
        }
    } else {
        // Normal version => url relationships

        // does the requested version exist?
        if (!empty($v) && array_key_exists($v, $registry[$s])) {
            // yes, return download url
            header("Location: " . $registry[$s][$v]); // e.g. $registry['nginx']['1.2.1'];
        } else {
            // no, requested version not existing, return latest version instead
            header("Location: " . $registry[$s]['latest']['url']); // e.g. $registry['nginx']['latest']['url'];
        }
    }
} else {
    // software does not exist, download will fail.
    header("HTTP/1.0 404 Not Found");
}
