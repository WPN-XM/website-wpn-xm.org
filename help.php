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
    '1' => 'Welcome',
    '2' => 'License-Agreement',
    '3' => 'Password',
    '4' => 'Information',
    '5' => 'User-Information',
    '6' => 'Select-Destination-Location',
    '7' => 'Select-Components',
    '8' => 'Select-Start-Menu-Folder',
    '9' => 'Select-Tasks',
   '10' => 'Ready-To-Install',
   '11' => 'Preparing-To-Install',
   '12' => 'Installing',
   '13' => 'Information',
   '14' => 'Setup-Completed'
);

// map for wizard types with correct case
$wizardTypes = array(
    'webinstaller' => 'Webinstaller',
    'allinone' => 'AllInOne',
    'bigpack' => 'BigPack'
);

if(!empty($type) && !empty($version) && !empty($language) && !empty($page))
{
    // build URL
    // example URL: https://github.com/WPN-XM/WPN-XM/wiki/Installation-Wizard-Webinstaller-v0.6.0-de#Welcome
    $baseURL = 'https://github.com/WPN-XM/WPN-XM/wiki/';
    $helpURL = $baseURL . 'Installation-Wizard-' . $wizardTypes[$type] . '-v' . $version . '-' . $language . '#' . $wizardPages[$page];

    // redirect
    header("Location: " . $helpURL);
} else {
    header("HTTP/1.0 404 Not Found");
}