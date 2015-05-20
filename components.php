<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2015 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * The script renders a "component list" with "version dropdowns".
 * This allows a user to quickly select and download a certain software of the registry.
 * The display is splitted between "Software Components" and "PHP Extensions".
 */

// WPNXM Software Registry
$registry = include __DIR__ . '/registry/wpnxm-software-registry.php';

$render_components = render_components($registry);

echo render_header();
echo render_component_tables($render_components);
echo '</div></body></html>';

function render_component_tables($render_components)
{
    $html = '<div class="span-20 inset-panel mc-is">'
        . '<h3 id="download">Software Components for Web Development on Windows</h2>'
        . '<p style="font-size: 16px;">'
        . 'All software components of the WPN-XM software registry are available for selective download.'
        . '<br>You will always download directly from the software vendor.'
        . '<br>You have to install these components manually.'
        . '<br>Please follow the installation instructions of the specific component.'
        . '<br>This collection of download links is provided to save you some time.'
        . '</p>'
        . '</div></div>'
        . '<div style="width: 1111px; margin: 0 auto; display: flex;">'
        . '<div class="download-components span-14">'
        . '<h2>Software Components</h2>'
        . '<table border=1 style="width: auto">'
        . '<thead><th>Software Component</th><th>Versions</th><th>Latest Version</th></thead>'
        . $render_components['components']
        . '</table></div>';

    $html .= '<div class="download-components download-extensions span-13">'
        . '<h2>PHP Extensions</h2>'
        . '<table border=1>'
        . '<thead><th>PHP Extension</th><th>Versions</th><th>Latest Version</th></thead>'
        . $render_components['extensions']
        . '</table></div>'
        . '</div>';

    return $html;
}

function render_components($registry)
{
    $html            = '';
    $html_extensions = '';

    foreach ($registry as $software => $component) {
        if (strpos($software, 'phpext_') !== false) {
            $html_extensions .= render_tr_for_php_extension($component);
            continue;
        }

        $html .= render_tr_for_normal_component($component);
    }

    return ['components' => $html, 'extensions' => $html_extensions];
}

function render_tr_for_php_extension($component)
{
    $name = str_replace('PHP Extension ', '', $component['name']);

    $html = '<tr>'
          . '<td><a href="' . $component['website'] . '">' . $name . '</a>' . '</td>'
          . '<td>' . render_version_dropdown_for_extension($component) . '</td>'
          . '<td>' . $component['latest']['version'] . '</td>'
          . '</tr>';

    return $html;
}

function render_version_dropdown_for_extension($component)
{
    unset($component['name'], $component['website'], $component['latest']);

    krsort($component);

    // restructure the array
    foreach ($component as $version => $bitsizes) {
        foreach ($bitsizes as $bitsize => $php_versions) {
            foreach ($php_versions as $php_version => $url) {
                $v[$bitsize][$php_version][$version] = $url;
            }
        }
    }

    // render
    $html = '';
    foreach ($v as $bitsize => $php_version) {
        $html .= '<span class="left">' . $bitsize . '</span>';
        foreach ($php_version as $php_v => $urls) {
            $html .= ' ' . $php_v;
            $html .= ' <select onchange="if (this.value) window.location.href=this.value">';
            $html .= '<option value="" selected disabled>Select..</option>';
            foreach ($urls as $ver => $url) {
                $html .= '<option value="' . $url . '">' . $ver . '</option>';
            }
            $html .= '</select>';
        }
        $html .= '<br>';
    }

    return $html;
}

function render_tr_for_normal_component($component)
{
    $html = '<tr>'
          . '<td><a href="' . $component['website'] . '">' . $component['name'] . '</a>' . '</td>'
          . '<td>' . render_version_dropdown($component) . '</td>'
          . '<td><a href="' . $component['latest']['url'] . '">' . $component['latest']['version'] . '</a></td>'
          . '</tr>';

    return $html;
}

function render_version_dropdown($component)
{
    // if a component only provides a latest version, we need no dropdown
    if ($component['latest']['version'] === 'latest') {
        return '&nbsp;';
    }

    unset($component['name'], $component['website'], $component['latest']);

    asort($component);

    $html = '<select onchange="if (this.value) window.location.href=this.value">';
    $html .= '<option value="" selected disabled>Please select a version...</option>';
    foreach ($component as $version => $url) {
        $html .= '<option value="' . $url . '">' . $version . '</option>';
    }
    $html .= '</select>';

    return $html;
}

function render_header()
{
    return <<<EOD
<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head prefix="og: https://ogp.me/ns# fb: https://ogp.me/ns/fb# website: https://ogp.me/ns/website#">
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
  <meta name="keywords" content="WPN-XM, free, open-source, server, NGINX, PHP, Windows, MariaDb, MongoDb, Adminer, XDebug, WAMP, WIMP, WAMPP, LAMP" />
  <!-- avgthreatlabs.com Site Verification -->
  <meta name="avgthreatlabs-verification" content="247b6d3c405a91491b1fea8e89fb3b779f164a5f" />
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
  <!-- Facebook OpenGraph -->
  <meta property="og:url" content="http://wpn-xm.org/" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="WPN-XM - is a free and open-source web server solution stack for professional PHP development on the Windows platform." />
  <meta property="og:description" content="WPN-XM is a free and open-source web server solution stack for professional PHP development on the Windows platform." />

  <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" href="images/touch/apple-touch-icon.png" />
  <link rel="apple-touch-icon" sizes="57x57" href="images/touch/apple-touch-icon-57x57.png" />
  <link rel="apple-touch-icon" sizes="60x60" href="images/touch/apple-touch-icon-60x60.png" />
  <link rel="apple-touch-icon" sizes="72x72" href="images/touch/apple-touch-icon-72x72.png" />
  <link rel="apple-touch-icon" sizes="76x76" href="images/touch/apple-touch-icon-76x76.png" />
  <link rel="apple-touch-icon" sizes="114x114" href="images/touch/apple-touch-icon-114x114.png" />
  <link rel="apple-touch-icon" sizes="120x120" href="images/touch/apple-touch-icon-120x120.png" />
  <link rel="apple-touch-icon" sizes="144x144" href="images/touch/apple-touch-icon-144x144.png" />
  <link rel="apple-touch-icon" sizes="152x152" href="images/touch/apple-touch-icon-152x152.png" />

  <!-- Blueprint CSS Framework -->
  <link rel="stylesheet" href="css/blueprint/screen.css" type="text/css" media="screen, projection" />
  <link rel="stylesheet" href="css/blueprint/print.css" type="text/css" media="print" />
  <!--[if IE]><link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
  <link rel="stylesheet" href="css/style.css" type="text/css" media="screen, projection" />
  <style>
  div.download-components h2 {
   font-size:18px;
   margin:10px 0;
  }
  div.download-components table {
    border: 1px solid #DDDEDE;
    box-shadow: 0 1px 0 white;
    background: #F7F7F8;
    width: 100%;
  }
  div.download-components td,
  div.download-components th {
    text-align:center;
    padding: 3px 8px 3px 8px;
    border-right: 1px solid #DDDEDE;
    border-bottom: 1px solid #DDDEDE;
    box-shadow: 0 2px 0 white;
    margin:0;
  }
  div.download-components th {
    background-color:transparent!important;
    color:inherit!important;
  }
  div.download-components td:first-child,
  div.download-components th:first-child {
    text-align:left;
    width:50%;
  }
  div.download-components td:nth-child(2) {
    text-align: right;
  }
  div.download-components tr:last-child td {
    border-bottom: none;
    box-shadow: none;
  }
  div.download-components td:last-child,
  div.download-components th:last-child  {
    border-right:none;
  }

  div.download-components td:first-child,
  div.download-components th:first-child {
    text-align:left;
    width:15%;
  }
  div.download-extensions td:nth-child(2) {
    width: 70%;
    text-align: right;
  }

  div.download-components td a {
    color: rgb(80, 80, 80);
    font-family: Arial, sans-serif;
    font-size: 13px;
    font-weight: normal;
  }
  div.download-components select {
    margin: 0;
  }
  </style>
</head>
<body>
<div class="container showgrids">

  <nav role="navigation" id="main-nav" class="span-21 toolbar black">
    <ul>
      <li class="vcard"><a itemprop="url" rel="home" href="index.html" class="fn org url uid">Home</a></li>
      <li><a rel="about" href="index.html#about">About</a></li>
      <li><a rel="help" href="https://groups.google.com/forum/?fromgroups#!forum/wpn-xm">Mailing List</a></li>
      <li><a rel="get-involved" href="index.html#getinvolved">Get Involved</a></li>
      <li><a rel="install" href="https://github.com/WPN-XM/WPN-XM/wiki/">Wiki</a></li>
      <li><a rel="install" href="https://github.com/WPN-XM/WPN-XM/issues/">Issues</a></li>
      <li><a rel="donate" href="index.html#donate">Donate</a></li>
      <li><a rel="imprint" href="index.html#imprint">Imprint</a></li>
    </ul>
  </nav>

  <div class="span-21 header">
    <h1 id="logo">WPИ-XM</h1>
    <h2><strong itemprop="name">WPИ-XM</strong> is a free and open-source web server solution stack for professional PHP development on the Windows<small><sup>&reg;</sup></small> platform.</h2>
  </div>


EOD;
}
