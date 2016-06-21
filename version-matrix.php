<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * The script renders a "version comparison matrix" for all available installers.
 * This allows a user to quickly notice, if a certain software is packaged and which version.
 */

require __DIR__ . '/src/InstallerRegistries.php';
require __DIR__ . '/src/InstallerRegistryArrayHelper.php';
require __DIR__ . '/src/VersionMatrixRenderer.php';

$softwareRegistry    = include __DIR__ . '/registry/wpnxm-software-registry.php';
$installerRegistries = InstallerRegistries::getInstallerRegistries();

$renderer = new VersionMatrixRenderer($softwareRegistry, $installerRegistries);
$renderer->render();