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

    // WPNXM-0.5.4-BigPack-Setup - without PHP version constraint
    if(substr_count($file, '-') === 3) {
        if(preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup.exe/', $file, $matches)) {
            $details['version'] =  $matches['version'];
            $details['installer'] = $matches['installer'];
            $details['platform'] = 'w32';
        }
    }

    // WPNXM-0.5.4-BigPack-Setup-w32
        if(substr_count($file, '-') === 4) {
        if(preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup-(?<bitsize>.*).exe/', $file, $matches)) {
            $details['version'] =  $matches['version'];
            $details['installer'] = $matches['installer'];
            $details['platform'] = $matches['bitsize']; //w32|w64
        }
    }

    // WPNXM-0.8.0-Full-Setup-php54-w32
    if(substr_count($file, '-') === 5) {
        if(preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup-(?<phpversion>.*)-(?<bitsize>.*).exe/', $file, $matches)) {
            $details['version'] =  $matches['version'];
            $details['installer'] = $matches['installer'];
            $details['phpversion'] = $matches['phpversion'];
            $details['platform'] = $matches['bitsize']; //w32|w64
        }
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
            . 'href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v' . $details['version'] . '">Release Notes</a>';

    // put "v" in front to get a properly versionized tag, starting from version "0.8.0"
    $version = (version_compare($details['version'], '0.8.0')) ? $details['version'] : 'v' . $details['version'];

    // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/0.5.2/changelog.txt
    $details['changelog'] = '<a class="btn btn-large btn-info"'
            . 'href="https://github.com/WPN-XM/WPN-XM/blob/' . $details['version'] . '/changelog.txt">Changelog</a>';

    // component list with version numbers

    // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
    $details['github_tag'] = '<a class="btn btn-large btn-info"'
            . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $details['version'] . '">Github Tag</a>';

    // date
    $details['date'] = date('d.m.Y', filectime($filename));

    // add download details to downloads array
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
        $html .= '<table border=1 width="100%">';
        $html .= '<th rowspan="4" width="85%">';
        $html .= '<a class="btn btn-success btn-large" href="' . $download['download_url'] .'">' . $download['file'] . '</a></th>';
        $html .= '<tr><td width="20%">Size</td><td><span class="bold">' . $download['size'] . '</span></td></tr>';
        $html .= '<tr><td>MD5</td><td>' . $download['md5'] . '</td></tr>';
        $html .= '<tr><td>SHA-1</td><td>' . $download['sha1'] . '</td></tr>';

        // Components
        if('webinstaller' === strtolower($download['installer'])) {
           $html .= '<tr><td colspan="3">Latest Components fetched from the Web</td></tr>';
        } else {
           $html .= '<tr><td colspan="3">Components</td></tr>';
        }

        $html .= '<tr><td colspan="3">';

        $platform = isset($download['platform']) ? '-' . $download['platform'] : '';

        // set PHP version starting from 0.8.0 on
        $phpversion = isset($download['phpversion']) ? '-' . $download['phpversion'] : '';

        // PHP version dot fix
        $phpversion = str_replace('php5', 'php5.', $phpversion);

        $registry_file = __DIR__ . '/registry/' . strtolower($download['installer']) .'-'. $version . $phpversion . $platform . '.json';

        if (is_file($registry_file) === true) {
            $installerRegistry = json_decode(file_get_contents($registry_file));

            $i_total = count($installerRegistry);
            foreach ($installerRegistry as $i => $component) {
                    $html .= '<span style="font-weight:bold;">' . ucfirst($component[0]) . '</span> ' . $component[3];
                    $html .= ($i+1 !== $i_total) ? ', ' : '';
            }
            unset($installerRegistry);
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