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
            . 'href="https://github.com/WPN-XM/WPN-XM/blob/' . $version . '/changelog.txt">Changelog</a>';

    // component list with version numbers

    // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
    $details['github_tag'] = '<a class="btn btn-large btn-info"'
            . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $version . '">Github Tag</a>';

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

// reindex
array_splice($downloads, 0, 0);

// add "versions", listing "all available version"
$versions = array();
foreach ($downloads as $download) {
    if (isset($download['version']) === true) {
        $versions[] = $download['version'];
    }
}
$downloads['versions'] = array_unique($versions);

// add "latest" as array key, referring to the latest version of WPN-XM
$downloads['latest_version'] = $downloads[0]['version'];
$downloads['latest_version_release_date'] = $downloads[0]['date'];

// debug
// echo '<pre>' . htmlentities(var_export($downloads, true)) . '</pre>';

/*
    Example Downloads Array

    link, release_notes, changelog, github_tag are HTML anchor tags.

    array (
      39 =>
      array (
        'file' => 'WPNXM-0.8.0-Webinstaller-Setup-php56-w64.exe',
        'size' => '1.59 MB',
        'version' => '0.8.0',
        'installer' => 'Webinstaller',
        'phpversion' => 'php56',
        'platform' => 'w64',
        'md5' => '6ae27511a06bfbc98472283b30565913',
        'sha1' => '7258ed16afe86611572e1b5ea9f879b41adf4be1',
        'download_url' => 'http://wpn-xm.org/downloads/WPNXM-0.8.0-Webinstaller-Setup-php56-w64.exe',
        'link' => '<a href="http://wpn-xm.org/downloads/WPNXM-0.8.0-Webinstaller-Setup-php56-w64.exe">WPNXM-0.8.0-Webinstaller-Setup-php56-w64.exe</a>',
        'release_notes' => '<a class="btn btn-large btn-info"href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.8.0">Release Notes</a>',
        'changelog' => '<a class="btn btn-large btn-info"href="https://github.com/WPN-XM/WPN-XM/blob/v0.8.0/changelog.txt">Changelog</a>',
        'github_tag' => '<a class="btn btn-large btn-info"href="https://github.com/WPN-XM/WPN-XM/tree/v0.8.0">Github Tag</a>',
        'date' => '20.09.2014',
      ),

*/

// ----- GET
// accept "type" as a get parameter, e.g. index.php?type=json
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

// send download list as json
if (!empty($type) && ($type === 'json')) {
    header('Content-Type: application/json');
    echo json_encode($downloads);
} else {
    // send html page

    //echo 'Latest Version: <b>'. $downloads[0]['version'].'</b>';
    //echo 'Released: <b>'. $downloads[0]['date'] . '</b>';

    unset($downloads['versions'], $downloads['latest_version'], $downloads['latest_version_release_date']);
    $version = '0.0.0';

    $html = '<table border="1">';

    foreach ($downloads as $download) {

        // print version only once for all files of that version
        if ($version != $download['version']) {
            $version = $download['version'];

            $html .= '<tr>';
            $html .= '<td width="50%" style="vertical-align: bottom;">';
            $html .= '<h2>WPN-XM v' . $version . '&nbsp;<small>' . date('d M Y', strtotime($download['date'])) . '</small></h2>';
            $html .= '</td>';

            // print release notes, changelog, github tag once per version
            $html .= '<td>';
            $html .= $download['release_notes'] . '&nbsp;';
            $html .= $download['changelog']. '&nbsp;';
            $html .= $download['github_tag'];
            $html .= '</td>';
            $html .= '</tr>';
        }

        // download details
        $html .= '<td colspan="2">';
        $html .= '<table border=1 width="100%">';
        $html .= '<th rowspan="2"><a class="btn btn-success btn-large" href="' . $download['download_url'] .'">' . $download['file'] . '</a></th>';
        $html .= '<tr><td><span class="bold">' . $download['size'] . '</span></td><td>';
        $html .= '<button id="copy-to-clipboard" class="btn btn-mini zclip" data-zclip-text="' . $download['md5'] . '">MD5</button>&nbsp;';
        $html .= '<button id="copy-to-clipboard" class="btn btn-mini zclip" data-zclip-text="' . $download['sha1'] . '">SHA-1</button>';
        $html .= '</td></tr>';

        // Components
        if('webinstaller' === strtolower($download['installer'])) {
           $html .= '<tr><td colspan="3">Latest Components fetched from the Web</td></tr>';
        } else {
            $html .= '<tr><td colspan="3">Components<p>';

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

            $html .= '</p></td></tr>';
        }

        $html .= '</table>';
        $html .= '</td></tr>';
    }
    $html .= '</table><br/>';

    header('Content-Type: text/html; charset=utf-8');
    echo $html;
}
?>