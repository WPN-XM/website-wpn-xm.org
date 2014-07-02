<?php
   /**
    * WPИ-XM Server Stack
    * Jens-André Koch © 2010 - onwards
    * http://wpn-xm.org/
    *
    *        _\|/_
    *        (o o)
    +-----oOO-{_}-OOo------------------------------------------------------------------+
    |                                                                                  |
    |    LICENSE                                                                       |
    |                                                                                  |
    |    WPИ-XM Serverstack is free software; you can redistribute it and/or modify    |
    |    it under the terms of the GNU General Public License as published by          |
    |    the Free Software Foundation; either version 2 of the License, or             |
    |    (at your option) any later version.                                           |
    |                                                                                  |
    |    WPИ-XM Serverstack is distributed in the hope that it will be useful,         |
    |    but WITHOUT ANY WARRANTY; without even the implied warranty of                |
    |    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                 |
    |    GNU General Public License for more details.                                  |
    |                                                                                  |
    |    You should have received a copy of the GNU General Public License             |
    |    along with this program; if not, write to the Free Software                   |
    |    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA    |
    |                                                                                  |
    +----------------------------------------------------------------------------------+
    */

/**
 * Header redirection script
 * -------------------------
 * The script provides a header response to a software and version request
 * in form of a header redirection to the download url.
 *
 * @author Tobias Fichtner <github@tobiasfichtner.com>
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
        // does the requested version exist?
        if (!empty($v) && array_key_exists($v, $registry[$s]) && array_key_exists($p, $registry[$s][$v])) {
            // yes, return download url
            header("Location: " . $registry[$s][$v][$p]); // e.g. $registry['nginx']['1.2.1']['5.5'];
        } elseif(array_key_exists($p, $registry[$s]['latest']['url'])) {
            // no, requested version not existing, return latest version for php default version instead
            header("Location: " . $registry[$s]['latest']['url'][$p]); // e.g. $registry['nginx']['latest']['url']['5.5'];
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
