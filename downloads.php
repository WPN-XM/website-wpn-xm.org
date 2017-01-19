<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens-André Koch <jakoch@web.de>
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
 *
 * @return string Formatted Filesize.
 */
function filesize_formatted($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes === 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

/**
 * Builds a md5 checksum for a file and writes it to a file for later reuse.
 *
 * @param string $filename
 *
 * @return string md5 file checksum
 */
function md5_checksum($filename)
{
    $md5 = '';

    $path = pathinfo($filename);
    $dir  = __DIR__ . '/' . $path['dirname'] . '/checksums/';
    if (is_dir($dir) === false) {
        mkdir($dir);
    }
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
 *
 * @return string sha1 file checksum
 */
function sha1_checksum($filename)
{
    $sha1 = '';

    $path = pathinfo($filename);
    $dir  = __DIR__ . '/' . $path['dirname'] . '/checksums/';
    if (is_dir($dir) === false) {
        mkdir($dir);
    }
    $sha1ChecksumFile = $dir . $path['filename'] . '.sha1';

    if (is_file($sha1ChecksumFile) === true) {
        $sha1 = file_get_contents($sha1ChecksumFile);
    } else {
        $sha1 = sha1_file($filename);
        file_put_contents($sha1ChecksumFile, $sha1);
    }

    return $sha1;
}

function get_github_releases()
{
    $cache_file = __DIR__ . '/downloads/github-releases-cache.json';

    if (file_exists($cache_file) && (filemtime($cache_file) > (time() - (1 * 24 * 60 * 60)))) {
        // Use cache file, when not older than 7 days.
        $data = file_get_contents($cache_file);
    } else {
        // The cache is out-of-date. Load the JSON data from Github.
        $data = curl_request();
        file_put_contents($cache_file, $data, LOCK_EX);
    }

    return json_decode($data, true);
}

function get_github_releases_tag($release_tag)
{
    $releases = get_github_releases();

    foreach ($releases as $release) {
        if ($release['tag_name'] === $release_tag) {
            return $release;
        }
    }
}

function get_total_downloads($release)
{
    $downloadsTotal = 0;

    foreach ($release['assets'] as $idx => $asset) {
        $downloadsTotal += $asset['download_count'];
    }

    return $downloadsTotal;
}

function render_github_releases()
{
    $releases = get_github_releases();

    $html = '';

    foreach ($releases as $release) {
        // skip our first release - only commits, no downloads
        if ($release['tag_name'] === '0.2.0') {
            continue;
        }

        unset($release['author']);

        if ($release['prerelease'] === false) {
            
            $html .= '<tr>'; // row for new release

            $html .= '<td class="release-cell">'
                . '<h2 style="text-align: left;">' . $release['name'] . '&nbsp;'
                . '<small class="btn btn-sm" title="Release Date">Release Date<br>'
                . '<b>' . date('d M Y', strtotime($release['created_at'])) . '</b></small>'
                . '&nbsp;'
                . '<small class="btn btn-sm" title="Total Downloads">Downloads<br>'
                . '<span class="bold installer-downloads">' . get_total_downloads($release) . '</span></small>'
                . '</h2>'
                . '</td>';

            // release notes, e.g. https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.5.3
            $release_notes = '<a class="btn btn-large btn-info" '
                . 'href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-' . $release['tag_name'] . '">Release Notes</a>';

            // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/master/CHANGELOG.md#v085---2015-07-12
            $hash = '#' . str_replace('.', '', $release['tag_name']) . '---' . date('Y-m-d', strtotime($release['created_at']));
            $changelog = '<a class="btn btn-large btn-info" '
                . 'href="https://github.com/WPN-XM/WPN-XM/blob/master/CHANGELOG.md' . $hash . '">Changelog</a>';

            // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
            $github_tag = '<a class="btn btn-large btn-info" '
                . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $release['tag_name'] . '">Github Tag</a>';

            // print release notes, changelog, github tag once per version
            $html .= '<td style="vertical-align: middle;">' . $release_notes . '&nbsp;' . $changelog . '&nbsp;' . $github_tag . '</td>';
            $html .= '</tr>'; // row for new release

            foreach ($release['assets'] as $idx => $asset) 
            {
                unset($asset['uploader'], $asset['url'], $asset['label'], $asset['content_type'], $asset['updated_at']);

                // download button for installer, filesize, download counter
                $html .= '<tr><td colspan="2">';
                $html .= '  <a class="btn btn-large btn-success" href="' . $asset['browser_download_url'] . '">';
                $html .= '  <i class="glyphicon glyphicon-cloud-download"></i> ' . $asset['name'] . '</a>';
                $html .= '  &nbsp;';
                $html .= '  <div class="btn btn-small bold" title="Filesize">' . filesize_formatted($asset['size']) . '</div>';
                $html .= '  &nbsp;';
                $html .= '  <div class="btn btn-small bold" title="Downloads">' . $asset['download_count'] . '</div>';
                $html .= '</td></tr>';

                // component list with version numbers for the installer
                $html .= render_component_list_for_installer($asset['name']);

            }

            //$html .= '</td></tr>';
        }
    }
    return $html;
}

function curl_request()
{
    $headers[] = 'Accept: application/vnd.github.manifold-preview+json';

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://api.github.com/repos/wpn-xm/wpn-xm/releases',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_USERAGENT      => 'wpn-xm.org - downloads page',
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

// ----- Gather details for all available files

if (!is_dir(__DIR__ . '/downloads')) {
    echo 'The downloads directory is missing.';
    exit();
}

$downloads = [];
$details   = [];

# scan folder for installers 
$installerExecutables = glob('./downloads/*.exe');
foreach ($installerExecutables as $filename) {

    // file
    $file            = str_replace('./downloads/', '', $filename);
    $details['file'] = $file;

    // size
    $bytes           = filesize($filename);
    $details['size'] = filesize_formatted($bytes);

    $details = array_merge($details, get_installer_details($file));

    // md5 & sha1 hashes / checksums
    $details['md5']  = md5_checksum(substr($filename, 2));
    $details['sha1'] = sha1_checksum(substr($filename, 2));

    // download URL
    $details['download_url'] = 'http://wpn-xm.org/downloads/' . $file;

    // download link
    $details['link'] = '<a href="' . $details['download_url'] . '">' . $file . '</a>';

    // release notes, e.g. https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.5.3
    $details['release_notes'] = '<a class="btn btn-large btn-info" '
        . 'href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v' . $details['version'] . '">Release Notes</a>';

    // put "v" in front to get a properly versionized tag, starting from version "0.8.0"
    $version = (version_compare($details['version'], '0.8.0')) ? $details['version'] : 'v' . $details['version'];

    // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/0.5.2/changelog.txt
    $details['changelog'] = '<a class="btn btn-large btn-info" '
        . 'href="https://github.com/WPN-XM/WPN-XM/blob/' . $version . '/changelog.txt">Changelog</a>';

    // component list with version numbers
    // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
    $details['github_tag'] = '<a class="btn btn-large btn-info" '
        . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $version . '">Github Tag</a>';

    // date
    $details['date'] = date('d.m.Y', filectime($filename));

    // add download details to downloads array
    $downloads[] = $details;

    // reset array for next loop
    $details = [];
}

// ----- Gather some general data for the downloads list
// order downloads - latest version first
arsort($downloads);

// reindex
array_splice($downloads, 0, 0);

// add "versions", listing "all available version"
$versions = [];
foreach ($downloads as $download) {
    if (isset($download['version']) === true) {
        $versions[] = $download['version'];
    }
}
$downloads['versions'] = array_unique($versions);

// add "latest" as array key, referring to the latest version of WPN-XM
$downloads['latest_version']              = $downloads[0]['version'];
$downloads['latest_version_release_date'] = $downloads[0]['date'];

// ----- GET
// accept "type" as a get parameter, e.g. index.php?type=json
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

// send download list as json
if (!empty($type) && ($type === 'json')) {
    header('Content-Type: application/json');
    echo json_encode($downloads);
} else {
    // send html page
    header('Content-Type: text/html; charset=utf-8');

    // load software components registry
    $registry = include __DIR__ . '/registry/wpnxm-software-registry.php';

    // ensure registry array is available
    if (!is_array($registry)) {
        header('HTTP/1.0 404 Not Found');
    }

    require __DIR__ . '/view/header.php';
    define('RENDER_WPNXM_HEADER_LOGO', true);
    require __DIR__ . '/view/topnav.php';

    unset($downloads['versions'], $downloads['latest_version'], $downloads['latest_version_release_date']);
    $version = '0.0.0';

?>

    <div class="row">

            <div class="panel panel-default" id="section-download-installation-wizards">
              <div class="panel-heading" style="overflow: hidden; min-height: 90px;">
                <!-- Total Downloads -->
                <h3 id="download" class="pull-left centered">Downloads<br>
                    <small class="label label-default bold total-amount-downloads" title="Total downloads"></small>
                </h3>
                <!-- Google Ads -->
                <div class="pull-right" style="height: 90px; width: 728px;">
                  <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                  <!-- WPИ-XM Leaderboard, 728x90, Erstellt 8.11.11 -->
                  <ins class="adsbygoogle"
                       style="display:inline-block;width:728px;height:90px"
                       data-ad-client="ca-pub-8272564713803494"
                       data-ad-slot="1380654938"></ins>
                  <script>
                  (adsbygoogle = window.adsbygoogle || []).push({});
                  </script>
                </div>
              </div>
              <div class="panel-body" id="downloads-list">

              <!-- Downloads Table -->
              <table style="width:auto; min-width:900px">

<?php
    // the first part of the installer listing are the releases from github

    $html = render_github_releases();

    // the second part of the installer listing are our early releases 
    // from before we used github releases
    // the installer were manually uploaded to /downloads on the server

    foreach ($downloads as $download) {

        // print version only once for all files of that version
        if ($version !== $download['version']) {
            $version = $download['version'];

             $html .= '<tr><td class="release-cell">'
                . '<h2 style="text-align: left;">WPИ-XM v' . $version . '&nbsp;'
                . '<small class="btn btn-sm" title="Release Date">Release Date<br>'
                . '<b>' . date('d M Y', strtotime($download['date'])) . '</b></small>'
                . '&nbsp;'
                //. '<small class="btn btn-sm" title="Total Downloads">Downloads<br>'
                //. '<span class="bold installer-downloads">' . get_total_downloads($release) . '</span></small>'
                . '</h2>'
                . '</td>';

            // print release notes, changelog, github tag once per version
            $html .= '<td style="vertical-align: middle;">';
            $html .= $download['release_notes'] . '&nbsp;';
            $html .= $download['changelog'] . '&nbsp;';
            $html .= $download['github_tag'];
            $html .= '</td>';
            $html .= '</tr>'. PHP_EOL;
        }

        // download button for installer, filesize, download counter
        $html .= '<tr><td colspan="2">';
        $html .= '  <a class="btn btn-large btn-success" href="' . $download['download_url'] . '">';
        $html .= '  <i class="glyphicon glyphicon-cloud-download"></i> ' . $download['file'] . '</a>';
        $html .= '  &nbsp;';
        $html .= '  <div class="btn btn-small bold" title="Filesize">' . $download['size'] . '</div>';
        //$html .= '  &nbsp;';
        //$html .= '  <div class="btn btn-small bold" title="Downloads">' . $asset['download_count'] . '</div>';
        $html .= '</td></tr>';

        // component list with version numbers for the installer
        $html .= render_component_list_for_installer($download['file']);

        $html .= '</table>'. PHP_EOL;
        $html .= '</td></tr>'. PHP_EOL;
    }

    $html .= '</table><br/>'. PHP_EOL;

    $html .= '<script>
                function calculateTotalDownloads() {
                  var total = 0;
                  $(\'span.installer-downloads\').each(function () {
                      total += parseInt($(this).text());
                  });
                  $(\'small.total-amount-downloads\').html(total);
                }
                calculateTotalDownloads();
              </script>';

    require __DIR__ . '/view/footer_scripts.php';

    $html .= '</div></div></div></body></html>';

    echo $html;
}

function get_installer_details($installer_filename)
{
    $details = [];

    // WPNXM-0.5.4-BigPack-Setup - without PHP version constraint
    if (substr_count($installer_filename, '-') === 3) {
        if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup.exe/', $installer_filename, $matches)) {
            $details['version']   = $matches['version'];
            $details['installer'] = $matches['installer'];
            $details['platform']  = 'w32';
        }
    }

    // WPNXM-0.5.4-BigPack-Setup-w32
    if (substr_count($installer_filename, '-') === 4) {
        if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup-(?<bitsize>.*).exe/', $installer_filename, $matches)) {
            $details['version']   = $matches['version'];
            $details['installer'] = $matches['installer'];
            $details['platform']  = $matches['bitsize']; //w32|w64
        }
    }

    // WPNXM-0.8.0-Full-Setup-php54-w32
    if (substr_count($installer_filename, '-') === 5) {
        if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup-(?<phpversion>.*)-(?<bitsize>.*).exe/', $installer_filename, $matches)) {
            $details['version']    = $matches['version'];
            $details['installer']  = $matches['installer'];
            $details['phpversion'] = $matches['phpversion'];
            $details['platform']   = $matches['bitsize']; //w32|w64
        }
    }

    $details['name'] = $installer_filename;

    return $details;
}

function render_component_list_for_installer($installer_name)
{
    global $registry;

    $download = get_installer_details($installer_name);

    // Components
    if ('webinstaller' === strtolower($download['installer'])) {
       return '<tr><td colspan="3">Latest Components fetched from the Web</td></tr>';
    } 

    $html = '';

    $platform = isset($download['platform']) ? '-' . $download['platform'] : '';

    // set PHP version starting from 0.8.0 on
    $phpversion = isset($download['phpversion']) ? '-' . $download['phpversion'] : '';

    // PHP version dot fix
    $phpversion = str_replace(['php5', 'php7'], ['php5.', 'php7.'], $phpversion);

    $registry_file = __DIR__ . '/registry/installer/v'.$download['version'].'/'. strtolower($download['installer']) . '-' . $download['version'] . $phpversion . $platform . '.json';

    if (!is_file($registry_file)) {
        return '</p></td></tr>';
    }

    $installerRegistry = json_decode(file_get_contents($registry_file));

    $number_of_components = count($installerRegistry);

    $html .= '<tr><td colspan="3">The following ' . $number_of_components . ' Components are included:<br>';

    //if($number_of_components >= 10) {
    $html .= render_component_list_multi_column($registry, $installerRegistry);
    //} else {
    //  $html .= render_component_list_comma_separated($registry, $installerRegistry, $number_of_components);
    //}

    $html .= '</td></tr>';    

    return $html;
}

function updateDeprecatedSoftwareRegistryKeyNames($software)
{
    if ($software === 'wpnxmscp')     { return 'wpnxm-scp';     }
    if ($software === 'wpnxmscp-x64') { return 'wpnxm-scp-x64'; }

    return $software;
}

function render_component_list_multi_column($registry, $installerRegistry)
{
    $html = '' . PHP_EOL;
    $html .= '<ul class="multi-column-list">';

    $extensions_html = '<li>PHP Extension(s):</li>';

    foreach ($installerRegistry as $i => $component) {
        $shortName = $component[0];

        $shortName = updateDeprecatedSoftwareRegistryKeyNames($shortName);

        // skip - components removed from registry, still in 0.7.0 and breaking it
        if (in_array($shortName, ['phpext_xcache', 'junction'])) {
            continue;
        }

        $version = $component[3];

        // php extension - they are appended to the extension html fragment
        if (false !== strpos($shortName, 'phpext_')) {
            $name = str_replace('PHP Extension ', '', $registry[$shortName]['name']);
            $extensions_html .= '<li><b>' . $name . '</b> ' . $version . '</li>';
            continue;
        }

        // normal component
        $name = $registry[$shortName]['name'];
        $html .= '<li><b>' . $name . '</b> ' . $version . '</li>';
    }
    unset($installerRegistry);

    $html .= $extensions_html;
    $html .= '</ul>'. PHP_EOL;

    return $html;
}

function render_component_list_comma_separated($registry, $installerRegistry, $number_of_components)
{
    $html            = '';
    $extensions_html = ', PHP Extension(s): ';

    foreach ($installerRegistry as $i => $component) {
        $shortName = $component[0];
        $version   = $component[3];

        // skip - removed from registry, still in 0.7.0 and breaking it
        if ($shortName === 'phpext_xcache') {
            continue;
        }

        if (false !== strpos($component[0], 'phpext_')) {
            $name = str_replace('PHP Extension ', '', $registry[$component[0]]['name']);
            $extensions_html .= '<b>' . $name . '</b> ' . $version;
            continue;
        }

        $name = $registry[$shortName]['name'];

        $html .= '<b>' . $name . '</b> ' . $version;
        $html .= ($i + 1 !== $number_of_components) ? ', ' : '';
    }
    unset($installerRegistry);

    $html .= $extensions_html;

    return $html;
}
