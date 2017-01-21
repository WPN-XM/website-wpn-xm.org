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
 * Downloads Listing Script for wpn-xm.org
 * ---------------------------------------
 * The script provides helper methods to generate a dynamic download list
 * based on files found in a specific downloads folder and on Github Releases.
 */

require __DIR__ . '/src/DownloadsViewRenderer.php';
require __DIR__ . '/src/DownloadsGetGithubInstallers.php';
require __DIR__ . '/src/DownloadsGetLocalInstallers.php';

$gitHubInstallers  = new DownloadsGetGithubInstallers;
$localInstallers   = new DownloadsGetLocalInstallers;

// combine local and github releases download information
$downloads = array_merge($gitHubInstallers->get(), $localInstallers->get());

$releases = $localInstallers->getReleasesAndVersions($downloads);
unset($releases['versions'], $releases['latest_version'], $releases['latest_version_release_date']);

$renderer = new DownloadsViewRenderer($downloads, $releases['releases']);

// ----- GET
// accept "type" as a get parameter, e.g. index.php?type=json
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

// send download list as json
if (!empty($type) && ($type === 'json')) {
    $renderer->renderJson();
} else {
    $renderer->renderHtml();
}
