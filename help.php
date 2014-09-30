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
 * Help
 *
 * The script provides a header response to a help page request
 * in form of a header redirection to the wiki url of that help page.
 *
 * URL for Installation Wizard Help Button:
 * http://wpn-xm.org/help.php?section=install-wizard&type=webinstaller&page=1&version=0.6.0&language=de
 */

// fetch $_GET parameters

// $_GET['section'] = the help section requested (installation-wizard, ...)
$section = filter_input(INPUT_GET, 'section', FILTER_SANITIZE_STRING);
// $_GET['page'] = the page of the installation wizard
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
// $_GET['version'] = version
$version = filter_input(INPUT_GET, 'version', FILTER_SANITIZE_STRING);
// $_GET['language'] = language
$language = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_STRING);
// $_GET['type'] = type of installation wizard (lite, bigpack, allinone, web)
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

// map for wizard page integers to their full page names
// see http://www.jrsoftware.org/ishelp/index.php?topic=wizardpages
$wizardPages = array(
    '1' => 'welcome',
    '2' => 'license-agreement',
    '3' => 'password',
    '4' => 'information',
    '5' => 'user-information',
    '6' => 'select-destination-location',
    '7' => 'select-components',
    '8' => 'select-start-menu-folder',
    '9' => 'select-tasks',
   '10' => 'ready-to-install',
   '11' => 'preparing-to-install',
   '12' => 'installing',
   '13' => 'information',
   '14' => 'setup-completed'
);

// map for wizard types with correct case
// see line with "#define InstallerType" in the iss files
$wizardTypes = array(
    'webinstaller' => 'Webinstaller',
    'allinone' => 'AllInOne',
    'bigpack' => 'BigPack',
    'lite' => 'Lite',
    // from v0.8.0 on
    'standard' => 'Standard',
    'full' => 'Full'
);

if(!empty($type) && !empty($version) && !empty($language) && !empty($page)) {
    // build URL
    // example URL: https://github.com/WPN-XM/WPN-XM/wiki/Installation-Wizard-Webinstaller-v0.6.0-de#Welcome
    $baseURL = 'https://github.com/WPN-XM/WPN-XM/wiki/';
    $helpURL = $baseURL . 'Installation-Wizard-' . $wizardTypes[$type] . '-v' . $version . '-' . $language . '#' . $wizardPages[$page];

    // redirect
    header("Location: " . $helpURL);
} else {
    header("HTTP/1.0 404 Not Found");
}
