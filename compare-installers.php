<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

require __DIR__ . '/src/InstallerRegistryArrayHelper.php';
require __DIR__ . '/src/InstallerRegistries.php';
require __DIR__ . '/src/InstallerRegistryComparator.php';
require __DIR__ . '/src/InstallerCompareViewRenderer.php';

function get($key, $defaultValue) {
    $value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
    return $value ? $value : $defaultValue;
}

// --------------------------------------------------------------------------
// handle "compare-installers.php?action=get-installers&version=x.y.z"

$action = get('action', 'default');

if($action === 'get-installers') {
    $version = get('version', '0.8.6');   
    $installers = InstallerRegistries::getInstallerNamesForVersion($version);
    $d = [];
    foreach($installers as $key => $value) {
        $d[] = ["text" => $value, "value" => $value];
    }
    echo json_encode($d);
    exit;
}

// --------------------------------------------------------------------------
// handle "compare-installers.php?installerA=full-0.8.6-php5.6-w64&installerB=full-next-php5.6-w64"

$installerA  = get('installerA', 'full-0.8.6-php5.6-w64');
$installerB  = get('installerB', 'full-next-php7.2-w64');

$comparer = new InstallerRegistryComparator;
$comparer->installerRegistryA = InstallerRegistries::loadRegistry($installerA);
$comparer->installerRegistryB = InstallerRegistries::loadRegistry($installerB);
$comparer->compare();

$renderer = new InstallerCompareViewRenderer;
$renderer->versionA = InstallerRegistries::getPartsOfInstallerFilename($installerA)['version'];
$renderer->versionB = InstallerRegistries::getPartsOfInstallerFilename($installerB)['version'];
$renderer->installerNameA = $installerA;
$renderer->installerNameB = $installerB;
$renderer->comparison = $comparer->result;
echo $renderer->render();