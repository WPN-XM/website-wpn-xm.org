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
 * Update Check - Response
 * -----------------------
 * The script provides a json response to a update-check request
 * for individual or all components of the WPN-XM Server Stack.
 *
 * Example request:
 * 1) updatecheck.php?s=nginx&v=1.2.1
 * 2) updatecheck.php?s=all
 */

// load software components registry
$registry = include __DIR__ . '/registry/wpnxm-software-registry.php';

// ensure registry array is available
if (!is_array($registry)) {
    header("HTTP/1.0 504 Service Unavailable");
}

// $_GET['s'] = software component
$s = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);
// $_GET['v'] = your current version
$v = filter_input(INPUT_GET, 'v', FILTER_SANITIZE_STRING);
// fallback, if no version was set - this makes requests without "v" parameter possible
$v = (!empty($v)) ? $v : '0.0.0';

// request all software components with name/website/latestversion as JSON
if (!empty($s) && $s === 'all') {

    // reduce the registry (drop all version numbers and their URLs, but keep name/website/latestversion)
    $data = array();
    foreach ($registry as $software => $details) {
        $data[$software]['name'] = isset($details['name']) ? $details['name'] : '';
        $data[$software]['website'] =  isset($details['website']) ? $details['website'] : '';
        $data[$software]['latest'] = $details['latest'];
    }

    // send response as json message
    header('Content-Type: application/json');
    echo json_encode($data);
    exit(0);
}

// does the requested software exist in our registry?
if (!empty($s) && array_key_exists($s, $registry) ) {
    // compare versions
    if (version_compare($v, $registry[$s]['latest']['version'], '<') ) {

       // prepare json data
       $data = array (
            'software'       => $s,
            'your_version'   => $v,
            'latest_version' => $registry[$s]['latest']['version'],
            'href'           => $registry[$s]['latest']['url'],
            'message'        => 'You are running an old version of ' . $s . ' and should update immediately.'
        );

    } else {
        // prepare json data
        $data = array('message' => 'You are running the latest version.');

    }
    // send response as json message
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    echo 'Request Error. Specify parameters "s" and "v".';
}
