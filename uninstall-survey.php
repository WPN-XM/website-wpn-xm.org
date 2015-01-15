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
 * WPN-XM Uninstallation Survey
 * ----------------------------
 *
 * This script forwards to our Google Form.
 *
 * The link to this script is opened from the InnoSetup Uninstaller during the uninstallation procedure.
 * The APPVERSION of the InnoSetup application is passed to the script (as value of the GET request parameter "version").
 * Thr version is used to build the form-prefilling URL for our "Uninstallation Survey" Google Form.
 *
 * Example: http://wpn-xm.org/uninstall-survey.php?version=MAJOR.MINOR.PATCH
 *
 * Google Form
 * https://docs.google.com/forms/d/1woBYQ04KsWYHZXJ2RBQVbi4kzx-FU7fCnpniaKanGKI/viewform?entry.18516131=MAJOR.MINOR.PATCH
 */

// $_GET['version'] = version of the WPN-XM Uninstaller
$version = filter_input(INPUT_GET, 'version', FILTER_SANITIZE_STRING);

if(preg_match('#(\d+\\.)?(\d+\\.)?(\\*|\d+)#', $version) === 1)
{
	header('Location: https://docs.google.com/forms/d/1woBYQ04KsWYHZXJ2RBQVbi4kzx-FU7fCnpniaKanGKI/viewform?entry.18516131=' . $version);
} else {
	header('HTTP/1.0 404 Not Found');
}