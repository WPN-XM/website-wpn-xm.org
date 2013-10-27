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
function filesize_formatted($file) {
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
    if(is_dir($dir) === false) { mkdir($dir); }
    $md5ChecksumFile = $dir . $path['filename'] . '.md5';

    if(is_file($md5ChecksumFile) === true) {
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
    if(is_dir($dir) === false) { mkdir($dir); }
    $sha1ChecksumFile = $dir . $path['filename'] . '.sha1';

    if(is_file($sha1ChecksumFile) === true) {
         $sha1 = file_get_contents($sha1ChecksumFile);
    } else {
         $sha1 = sha1_file($filename);
         file_put_contents($sha1ChecksumFile, $sha1);
    }
    
    return $sha1;
}

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
    $details['download_url'] = $website . '/downloads/' . $file;

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
}

// send html page
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8" />
  <title>WPN-XM - is a free and open-source web server solution stack for professional PHP development on the Windows&reg; platform.</title>
  <meta http-equiv="x-ua-compatible" content="IE=EmulateIE7" />
  <!-- Google Site Verification -->
  <meta name="google-site-verification" content="OxwcTMUNiYu78EIEA2kq-vg_CoTyhGL-YVKXieCObDw" />
  <meta name="Googlebot" content="index,follow">
  <meta name="Author" content="Jens-Andre Koch" />
  <meta name="Copyright" content="(c) 2011-onwards Jens-Andre Koch." />
  <meta name="Publisher" content="Koch Softwaresystemtechnik" />
  <meta name="Rating" content="general" />
  <meta name="page-type" content="Homepage, Website" />
  <meta name="robots" content="index, follow, all, noodp" />
  <meta name="Description" content="WPN-XM - is a free and open-source web server solution stack for professional PHP development on the Windows platform." />
  <meta name="keywords" content="WPN-XM, free, open-source, server, NGINX, PHP, Windows, WAMP, WIMP, WAMPP, APC, memcached, xhprof, XDebug" />
  <!-- DC -->
  <meta name="DC.Title" content="WPN-XM" />
  <meta name="DC.Creator" content="Jens-Andre Koch" />
  <meta name="DC.Publisher" content="Koch Softwaresystemtechnik" />
  <meta name="DC.Type" content="Service" />
  <meta name="DC.Format" content="text/html" />
  <meta name="DC.Language" content="en" />
  <!-- Geo -->
  <meta name="geo.region" content="DE-MV" />
  <meta name="geo.placename" content="Neubrandenburg" />
  <meta name="geo.position" content="53.560348;13.249941" />
  <meta name="ICBM" content="53.560348, 13.249941" />

  <link href="favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />

  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
      <script src="http://wpn-xm.org/js/html5shiv.js"></script>
  <![endif]-->

  <!-- Blueprint CSS Framework -->
  <link rel="stylesheet" href="http://wpn-xm.org/css/blueprint/screen.css" type="text/css" media="screen, projection" />
  <link rel="stylesheet" href="http://wpn-xm.org/css/blueprint/print.css" type="text/css" media="print" />
  <!--[if IE]><link rel="stylesheet" href="css/blueprint/blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->

  <link rel="stylesheet" href="http://wpn-xm.org/css/style.css" type="text/css" media="screen, projection" />

  <script src="http://wpn-xm.org/js/jquery-1.9.1.min.js"></script>
  <script src="http://wpn-xm.org/js/bootstrap.min.js"></script>
  
  <style type="text/css">
            h1 { color:red; font-size:24px; }
            /* Buttons */
            .btn {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    background-color: #F5F5F5;
    background-image: linear-gradient(to bottom, #FFFFFF, #E6E6E6);
    background-repeat: repeat-x;
    border-color: #BBBBBB #BBBBBB #A2A2A2;
    border-image: none;
    border-radius: 4px 4px 4px 4px;
    border-style: solid;
    border-width: 1px;
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
    color: #333333;
    cursor: pointer;
    display: inline-block;
    font-size: 13px;
    line-height: 18px;
    margin-bottom: 0;
    padding: 4px 12px;
    text-align: center;
    text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
    vertical-align: middle;
}
.btn-info {
    background-color: #49AFCD;
    background-image: linear-gradient(to bottom, #5BC0DE, #2F96B4);
    background-repeat: repeat-x;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    color: #FFFFFF;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}
.btn-success {
    background-color: #5BB75B;
    background-image: linear-gradient(to bottom, #62C462, #51A351);
    background-repeat: repeat-x;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    color: #FFFFFF;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}
.btn-large {
    border-radius: 6px 6px 6px 6px;
    font-size: 16.25px;
    padding: 9px 14px;
}
.btn-mini {
    border-radius: 3px 3px 3px 3px;
    font-size: 9.75px;
    padding: 1px 6px;
}
th, td, caption {
    padding: 4px 10px 4px 15px;
}
        </style>
    </head>
    <body>
        <a href="https://github.com/WPN-XM/WPN-XM">
            <img width="149" height="149" class="github-ribbon" src="images/fork-me-on-github.png" alt="Fork WPN-XM on GitHub">
        </a>
        <div class="container showgrids" itemtype="http://schema.org/SoftwareApplication" itemscope="">
            <nav class="span-21 toolbar black" id="main-nav" role="navigation">
                <ul>
                    <li class="vcard">
                        <a class="fn org url uid" href="index.html" rel="home" itemprop="url">Home</a>
                    </li>
                    <li>
                        <a href="#about" rel="about">About</a>
                    </li>
                    <li>
                        <a href="https://groups.google.com/forum/?fromgroups#!forum/wpn-xm" rel="help">Mailing List</a>
                    </li>
                    <li>
                        <a href="#getinvolved" rel="get-involved">Get Involved</a>
                    </li>
                    <li>
                        <a href="https://github.com/WPN-XM/WPN-XM/wiki/" rel="install">Wiki</a>
                    </li>
                    <li>
                        <a href="https://github.com/WPN-XM/WPN-XM/issues/" rel="install">Issues</a>
                    </li>
                    <li>
                        <a href="#donate" rel="donate">Donate</a>
                    </li>
                    <li>
                        <a href="#imprint" rel="imprint">Imprint</a>
                    </li>
                </ul>
            </nav>
            <div class="span-21 header">
                <h1 id="logo">WPИ-XM</h1>
                <h2><strong itemprop="name">WPИ-XM</strong> is a free and open-source <em>web server solution stack for professional PHP development on the Windows<small><sup>&reg;</sup></small> platform</em>.</h2>
            </div>

            <div class="span-21">
                <div class="slider-wrapper">
                    <div class="slider-background rounded inset-panel mc-is" style="height: auto;">
                        <h3 id="download">Downloads</h3>   
                   
                       <!----//
                       Latest Version: <b><?= $downloads['latest_version']; ?></b>
                       Released: <b><?= $downloads[0]['date']; ?></b>
                       ---->
                <?php
                    unset($downloads['versions'], $downloads['latest_version']);
                    $version = '0.0.0';
                    $onlyOneW32 = $onlyOneW64 = true;

                    echo '<table border="1" style="width:98%;">';

                    foreach ($downloads as $download) {

                        // print version only once for all files of that version
                        if ($version != $download['version']) {
                            $version = $download['version'];

                            echo '<tr><td width="50%" style="vertical-align: bottom;"><h2>';
                            echo 'WPN-XM v' . $version . '&nbsp;&nbsp;&nbsp;';
                            echo '<small>' . $new_date = date('d M Y', strtotime($download['date'])) . '</small>';
                            echo '</h2></td>';
                            
                            // print release notes, changelog, github tag once per version
                            echo '<td>';
                            echo $download['release_notes'] . '&nbsp;';
                            echo $download['changelog']. '&nbsp;';
                            echo $download['github_tag'];
                            echo '</td></tr>';

                            // activate platform rendering after version number change
                            $onlyOneW32 = $onlyOneW64 = true;
                        }

                        // platform w32/w64
                        if(isset($download['platform']) === true) { // old releases don't have a platform set
                            if ($download['platform'] === 'w32' && $onlyOneW32 === true) {
                                echo '<tr><td colspan=3>w32</td></tr>';
                                $onlyOneW32 = false;
                            }
                            if ($download['platform'] === 'w64' && $onlyOneW64 === true) {
                                echo '<tr><td colspan=3>w64</td></tr>';
                                $onlyOneW64 = false;
                            }
                        }

                        // download details
                        echo '<td colspan="2">';
                        
                        echo '<table border=1>';
                        echo '<th rowspan="4" width="100%">';
                        
                        echo '<a class="btn btn-success btn-large"' .
                                $download['download_url'] .'>' .
                                $download['file'] . '</a></th>';
                        
                        echo '<tr><td width="15%">Size</td><td><span class="bold">' . $download['size'] . '</span></td></tr>';
                        echo '<tr><td>MD5</td><td>' . $download['md5'] . '</td></tr>';
                        echo '<tr><td>SHA-1</td><td>' . $download['sha1'] . '</td></tr>';
                        
                        // Components
                        echo '<tr><td colspan="2">Components</td></tr>';
                         
                        //echo '<tr><td>' . $download['link'] . '</td></tr>';  
                        //echo '<tr><td>Released: ' . $download['date'] . '</td></tr>';
                        //echo '<tr><td>' . $download['file'] . '</td></tr>';
                        //echo '<tr><td>v' . $download['version'] . '</td></tr>';
                        //echo '<tr><td>' . $download['platform'] . '</td></tr>';
                        //echo '<tr><td>' . $download['download_url'] . '</td></tr>';
                        //echo '<tr><td>' . $download['release_notes'] . '</td></tr>';
                        //echo '<tr><td>' . $download['changelog'] . '</td></tr>';
                        //echo '<tr><td>' . $download['github_tag'] . '</td></tr>';
                        echo '</table>';
                        echo '</td></tr>';
                    }
                    echo '</table><br/>';
                    ?>
                   </div>
               </div>
      </body>
</html>