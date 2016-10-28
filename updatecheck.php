<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * Update Check - Response
 * -----------------------
 * The script provides a json response to a update-check request
 * for individual or all components of the WPN-XM Server Stack.
 *
 * Example requests:
 * 1) updatecheck.php?s=nginx
 *    When you don't specify the URL parameter "v", then "v" will be "0.0.0" (fallback).
 * 2) updatecheck.php?s=nginx&v=1.2.1
 * 3) updatecheck.php?s=all
 */

// load software components registry
$registry = include __DIR__ . '/registry/wpnxm-software-registry.php';

// ensure registry array is available
if (!is_array($registry)) {
    header('HTTP/1.0 504 Service Unavailable');
}

// $_GET['s'] = software component
$s = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);
// $_GET['v'] = your current version
$v = filter_input(INPUT_GET, 'v', FILTER_SANITIZE_STRING);
// fallback, if no version was set - this makes requests without "v" parameter possible
$v = (!empty($v)) ? cleanVersionString($v) : '0.0.0';

if (!empty($s)) {
    $s = cleanSoftwareString($s);
}

// request all software components with name/website/latestversion as JSON
if ($s === 'all') {
    $data = reduceRegistry($registry);
    sendJsonResponse($data);
}
// does the requested software exist in our registry?
elseif (array_key_exists($s, $registry)) {
    if (version_compare($v, $registry[$s]['latest']['version'], '<')) {
       $data = [
            'software'       => $s,
            'your_version'   => $v,
            'latest_version' => $registry[$s]['latest']['version'],
            'url'            => $registry[$s]['latest']['url'],
            'message'        => 'You are running an old version of ' . $s . ' and should update immediately.',
        ];
    } else {
        $data = ['message' => 'You are running the latest version.'];
    }

    sendJsonResponse($data);
} else {
    echo 'Request Error. Specify parameters "s" and "v".';
}

// ----------------------------------------------------------------------------

/**
 * Cleans the version string using a character whitelist.
 */
function cleanSoftwareString($version)
{
    return preg_replace("/[^a-zA-Z0-9-_]+/", "", $version); 
}

/**
 * Cleans the version string using a character whitelist.
 */
function cleanVersionString($version)
{
    return preg_replace("/[^a-zA-Z0-9-+.]+/", "", $version); 
}

/**
 * Returns a reduced registry array.
 * This drops all version numbers and their URLs.
 * And returns only name, website, latest version and url for each component.
 */
function reduceRegistry($registry)
{
    $data = [];
    foreach ($registry as $software => $details) {
        $data[$software]['name']    = $details['name'];
        $data[$software]['website'] = $details['website'];
        $data[$software]['latest']  = $details['latest'];
    }
    return $data;
}

/**
 * Send JSON response
 */
function sendJsonResponse($json)
{
    header('Content-Type: application/json');
    echo json_encode($json);
    exit(0);
}
