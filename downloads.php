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
 * Downloads list of wpn-xm.org
 * -------------------------------------------------
 * The script gathers the data for a dynamic download list
 * based on files found in a specific downloads folder.
 *
 * You might access the data as JSON by sending a GET 
 * request to this file, e.g. downloads.php?type=json.
 */

# constants
$website = 'http://wpn-xm.org';

// ----- Gather details for all available files

$downloads = array();
$details = array();

# scan folder for files
foreach (glob("./downloads/*.exe") as $filename) {

    // file
    $file = str_replace('./downloads/', '', $filename);
    $details['file'] = $file;

    // size
    $details['size'] = filesize($filename);

    // version
    if (preg_match("#(\d+\.\d+(\.\d+)*)#", $file, $matches)) {
        $version = $matches[0];
    }
    $details['version'] = $version;

    // platform
    if (preg_match("#(w32|w64)#", $file, $matches)) {
        $platform = $matches[0];
    }
    $details['platform'] = $platform;

    // md5 hash
    $details['md5'] = md5($filename);

    // download URL
    $details['download_url'] = $website . '/downloads/' . $file;

    // download link
    $details['link'] = '<a href="' . $details['download_url'] . '">' . $file . '</a>';

    // release notes, e.g. https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.5.3
    $details['release_notes'] = '<a href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v' . $version . '">Release Notes v' . $version . '</a>';

    // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/0.5.2/changelog.txt
    $details['changelog'] = '<a href="https://github.com/WPN-XM/WPN-XM/blob/' . $version . '/changelog.txt">Changelog</a>';

    // component list with version numbers
    // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
    $details['github_tag'] = '<a href="https://github.com/WPN-XM/WPN-XM/tree/' . $version . '">Github Tag</a>';

    // date
    $details['date'] = date('d.m.Y', filectime($filename));

    // add to downloads array
    $downloads[] = $details;

    // reset array for next loop
    $details = array();
}

// ----- Gather some general data for the downloads list

// order downloads - latest version first
usort($downloads, function($a, $b) {
    return $a['file'] - $b['file'];
});

// add "latest" as array key, referring to the latest version of WPN-XM
$downloads['latest_version'] = $downloads[0]['version'];

// add "versions", listing "all available version"
$versions = array();
foreach ($downloads as $download) {
    if (isset($download['version']) === true) {
        $versions[] = $download['version'];
    }
}
$downloads['versions'] = array_unique($versions);

// debug
//var_dump($downloads);

// ----- GET

// accept "type" as a get parameter, e.g. index.php?type=json
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

// send download list as json
if (!empty($type) && ($type === 'json')) {
    header('Content-Type: application/json');
    echo json_encode($downloads);
} else {
    header("HTTP/1.0 404 Not Found");
}