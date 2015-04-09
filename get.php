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
        if (!empty($v) && array_key_exists($v, $registry[$s]) && array_key_exists($p, $registry[$s][$v][$bitsize])) {
            // yes, return download url
            header("Location: " . $registry[$s][$v][$bitsize][$p]); // e.g. $registry['phpext_xdebug']['1.2.1']['x86']['5.5'];
        } elseif(array_key_exists($p, $registry[$s]['latest']['url'][$bitsize])) {
            // no, requested version not existing, return latest version for php default version instead
            header("Location: " . $registry[$s]['latest']['url'][$bitsize][$p]); // e.g. $registry['phpext_xdebug']['latest']['url']['x86']['5.5'];
        } else {
            // software does not exist, download will fail.
            header("HTTP/1.0 404 Not Found");
        }
    } elseif($s === 'php' or $s === 'php-x64') {
        // it's either a specific version "v" or
        // the latest patch version of a "major.minor" version "p"

        // does the requested version exist?
        if (!empty($v) && array_key_exists($v, $registry[$s])) {
            // yes, return download url
            header("Location: " . $registry[$s][$v]); // e.g. $registry['php']['1.2.1'];
        } else {
            // $_GET['p'] = php version, default version is php 5.5
            $p = ($p = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_STRING)) ? $p : '5.5';

            // we have to get the latest "patch version" of a given "major.minor" version.
            $minVersionConstraint = $p; // 5.4, 5.5, 5.6
            $maxVersionConstraint = $minVersionConstraint . '.99'; // 5.4.99, 5.5.99
            $version = getLatestVersion($s, $minVersionConstraint, $maxVersionConstraint);
            header("Location: " . $registry[$s][$version]);
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

/**
 * Returns the latest version of a component given a min max version constraint.
 * For example, latest version for the component PHP means "latest version for PHP5.4, PHP5.5, PHP5.6".
 * It's a request for the "latest patch version" of a given "major.minor" version.
 */
function getLatestVersion($component, $minConstraint = null, $maxConstraint = null)
{
    global $registry;

    if ($minConstraint === null && $maxConstraint === null) {
        return $registry[$component]['latest']['version'];
    }

    // determine latest version for a component given a min/max constraint
    $software = $registry[$component];
    // remove all non-version stuff
    unset($software['name'], $software['latest'], $software['website']);
    // the array is already sorted.
    // get rid of (version => url) and use (idx => version)
    $software = array_keys($software);
    // reverse array, in order to have the highest version number on top.
    $software = array_reverse($software);
    // reduce array to values in constraint range
    foreach ($software as $url => $version) {
      if (version_compare($version, $minConstraint, '>=') === true && version_compare($version, $maxConstraint, '<') === true) {
          #echo 'Version v' . $version . ' is greater v' . $minConstraint . '(MinConstraint) and smaller v' . $maxConstraint . '(MaxConstraint).<br>';
      } else {
          unset($software[$url]);
      }
    }

    // pop off the first element
    $latestVersion = array_shift($software);

    return $latestVersion;
}