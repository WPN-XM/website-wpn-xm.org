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
 * Downloads Listing Script for wpn-xm.org
 * ---------------------------------------
 * The script provides helper methods to generate a dynamic download list
 * based on files found in a specific downloads folder.
 */

/**
 * Formats filesize in human readable way.
 *
 * @param file $file
 * @return string Formatted Filesize.
 */
function filesize_formatted($file)
{
    $size = filesize($file);
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;

    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

/**
 * Builds a md5 checksum for a file and writes it to a file for later reuse.
 *
 * @param string $filename
 * @return string md5 file checksum
 */
function md5_checksum($filename)
{
    $md5 = '';

    $path = pathinfo($filename);
    $dir = __DIR__ . '/' . $path['dirname'] . '/checksums/';
    if (is_dir($dir) === false) { mkdir($dir); }
    $md5ChecksumFile = $dir . $path['filename'] . '.md5';

    if (is_file($md5ChecksumFile) === true) {
         return file_get_contents($md5ChecksumFile);
    } else {
         $md5 = md5_file($filename);
         file_put_contents($md5ChecksumFile, $md5);
    }

    return $md5;
}

/**
 * Builds a sha1 checksum for a file and writes it to a file for later reuse.
 *
 * @param string $filename
 * @return string sha1 file checksum
 */
function sha1_checksum($filename)
{
    $sha1 = '';

    $path = pathinfo($filename);
    $dir = __DIR__ . '/' . $path['dirname'] . '/checksums/';
    if (is_dir($dir) === false) { mkdir($dir); }
    $sha1ChecksumFile = $dir . $path['filename'] . '.sha1';

    if (is_file($sha1ChecksumFile) === true) {
         $sha1 = file_get_contents($sha1ChecksumFile);
    } else {
         $sha1 = sha1_file($filename);
         file_put_contents($sha1ChecksumFile, $sha1);
    }

    return $sha1;
}

# http website and base path for downloads
$website = 'http://wpn-xm.org/';

// ----- Gather details for all available files

$downloads = array();
$details = array();

# scan folder for files
foreach (glob("./downloads/*.exe") as $filename) {

    // file
    $file = str_replace('./downloads/', '', $filename);
    $details['file'] = $file;

    // size
    $details['size'] = filesize_formatted($filename);

    // version
    if (preg_match("#(\d+\.\d+(\.\d+)*)#", $file, $matches)) {
        $version = $matches[0];
    }
    $details['version'] = $version;

    // platform
    if (preg_match("#(w32|w64)#", $file, $matches)) {
        $details['platform'] = $matches[0];
    }

    // md5 & sha1 hashes / checksums
    $details['md5'] = md5_checksum(substr($filename, 2));
    $details['sha1'] = sha1_checksum(substr($filename, 2));

    // download URL
    $details['download_url'] = $website . 'downloads/' . $file;

    // download link
    $details['link'] = '<a href="' . $details['download_url'] . '">' . $file . '</a>';

    // release notes, e.g. https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.5.3
    $details['release_notes'] = '<a class="btn btn-large btn-info"'
            . 'href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v' . $version . '">Release Notes</a>';

    // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/0.5.2/changelog.txt
    $details['changelog'] = '<a class="btn btn-large btn-info"'
            . 'href="https://github.com/WPN-XM/WPN-XM/blob/' . $version . '/changelog.txt">Changelog</a>';

    // component list with version numbers
    // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
    $details['github_tag'] = '<a class="btn btn-large btn-info"'
            . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $version . '">Github Tag</a>';

    // date
    $details['date'] = date('d.m.Y', filectime($filename));

    // add to downloads array
    $downloads[] = $details;

    // reset array for next loop
    $details = array();
}

// ----- Gather some general data for the downloads list
// order downloads - latest version first
arsort($downloads);

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
// var_dump($downloads);
// ----- GET
// accept "type" as a get parameter, e.g. index.php?type=json
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

// send download list as json
if (!empty($type) && ($type === 'json')) {
    header('Content-Type: application/json');
    echo json_encode($downloads);
} else {
  // send html page

  /*
     // Latest Version: <b><?= $downloads['latest_version']; </b>
     // Released: <b><?= $downloads[0]['date']; </b>
  */

    unset($downloads['versions'], $downloads['latest_version']);
    $version = '0.0.0';
    $onlyOneW32 = $onlyOneW64 = true;

    $html = '<table border="1">';

    foreach ($downloads as $download) {

        // print version only once for all files of that version
        if ($version != $download['version']) {
            $version = $download['version'];

            $html .= '<tr><td width="50%" style="vertical-align: bottom;"><h2>';
            $html .= 'WPN-XM v' . $version . '&nbsp;&nbsp;&nbsp;';
            $html .= '<small>' . $new_date = date('d M Y', strtotime($download['date'])) . '</small>';
            $html .= '</h2></td>';

            // print release notes, changelog, github tag once per version
            $html .= '<td>';
            $html .= $download['release_notes'] . '&nbsp;';
            $html .= $download['changelog']. '&nbsp;';
            $html .= $download['github_tag'];
            $html .= '</td></tr>';

            // activate platform rendering after version number change
            $onlyOneW32 = $onlyOneW64 = true;
        }

        // platform w32/w64
        if (isset($download['platform']) === true) { // old releases don't have a platform set
            if ($download['platform'] === 'w32' && $onlyOneW32 === true) {
                $html .= '<tr><td colspan=3>Windows 32-bit</td></tr>';
                $onlyOneW32 = false;
            }
            if ($download['platform'] === 'w64' && $onlyOneW64 === true) {
                $html .= '<tr><td colspan=3>Windows 64-bit</td></tr>';
                $onlyOneW64 = false;
            }
        }

        // download details
        $html .= '<td colspan="2">';
        $html .= '<table border=1>';
        $html .= '<th rowspan="4">';
        $html .= '<a class="btn btn-success btn-large" href="' . $download['download_url'] .'">' . $download['file'] . '</a></th>';
        $html .= '<tr><td>Size</td><td><span class="bold">' . $download['size'] . '</span></td></tr>';
        $html .= '<tr><td>MD5</td><td>' . $download['md5'] . '</td></tr>';
        $html .= '<tr><td>SHA-1</td><td>' . $download['sha1'] . '</td></tr>';

        // Components
        $html .= '<tr><td colspan="3">Components</td></tr>';
        $html .= '<tr><td colspan="3">';
        $registry_file = __DIR__ . '/wpnxm-software-registry-' . $version . '.csv';
        if (is_file($registry_file) === true) {
            $csvData = file_get_contents($registry_file);
            $lines = explode("\n", $csvData);
            array_pop($lines);
            $csvArray = array();
            foreach ($lines as $line) {
                $csvArray[] = str_getcsv($line);
            }
        }
        if (isset($csvArray) === true) {
            $c = count($csvArray)-1;
            foreach ($csvArray as $i => $component) {
                $html .= '<span style="font-weight:bold;">' . ucfirst($component[0]) . '</span> ' . $component[3];
                if ($c != $i) { $html .= ', '; }
            }
        }
        $html .= '</td></tr>';

        //$html .= '<tr><td>' . $download['link'] . '</td></tr>';
        //$html .= '<tr><td>Released: ' . $download['date'] . '</td></tr>';
        //$html .= '<tr><td>' . $download['file'] . '</td></tr>';
        //$html .= '<tr><td>v' . $download['version'] . '</td></tr>';
        //$html .= '<tr><td>' . $download['platform'] . '</td></tr>';
        //$html .= '<tr><td>' . $download['download_url'] . '</td></tr>';
        //$html .= '<tr><td>' . $download['release_notes'] . '</td></tr>';
        //$html .= '<tr><td>' . $download['changelog'] . '</td></tr>';
        //$html .= '<tr><td>' . $download['github_tag'] . '</td></tr>';
        $html .= '</table>';
        $html .= '</td></tr>';
    }
    $html .= '</table><br/>';

    header('Content-Type: text/html; charset=utf-8');
    echo $html;
}
?>