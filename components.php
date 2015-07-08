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
    $html = '<div class="panel panel-default">'
        . '<div class="panel-heading"><h2 id="download">Software Components for Web Development on Windows</h2></div>'
        . '<div class="panel-body"><p style="font-size: 16px;">'
        . 'All software components of the WPN-XM software registry are available for selective download.'
        . '<br>You will always download directly from the software vendor.'
        . '<br>You have to install these components manually.'
        . '<br>Please follow the installation instructions of the specific component.'
        . '<br>This collection of download links is provided with the intention to save you some time.'
        . '</p></div>'
        . '<div class="panel-footer" style="min-height: 90px;>
        <!-- Google Ads -->
            <div style="height: 90px; width: 728px;">
              <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
              <!-- WPN-XM Leaderboard, 728x90, Erstellt 8.11.11 -->
              <ins class="adsbygoogle"
                   style="display:inline-block;width:728px;height:90px"
                   data-ad-client="ca-pub-8272564713803494"
                   data-ad-slot="1380654938"></ins>
              <script>
              (adsbygoogle = window.adsbygoogle || []).push({});
              </script>
            </div>'
        . '</div><!-- End: Panel -->'
        . '</div><!-- End: Row-->';

    $html .= '<div class="row">'
        . '<div class="download-components col-md-6">'
        . '<h2>Software Components</h2>'
        . '<table border=1 class="table table-condensed table-hover">'
        . '<thead><th>Software Component</th><th>Versions</th><th>Latest Version</th></thead>'
        . $render_components['components']
        . '</table></div>';

    $html .= '<div class="download-components download-extensions col-md-6">'
        . '<h2>PHP Extensions</h2>'
        . '<table border=1 class="table table-condensed table-hover">'
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
        . '<td><a href="' . $component['website'] . '"><strong>' . $name . '</strong></a>' . '</td>'
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
        . '<td><a href="' . $component['website'] . '"><strong>' . $component['name'] . '</strong></a>' . '</td>'
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
    <meta property="og:description" content="WPN-XM - is a free and open-source web server solution stack for professional PHP development on the Windows platform." />
    <!-- Favicon & Touch-Icons -->
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="favicon.ico" />
    <link rel="apple-touch-icon" href="images/touch/apple-touch-icon.png" />
    <link rel="apple-touch-icon" sizes="57x57" href="images/touch/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="60x60" href="images/touch/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="images/touch/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="images/touch/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="images/touch/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="images/touch/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="images/touch/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="images/touch/apple-touch-icon-152x152.png" />
    <!-- Bootstrap CSS Framework -->
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    <!-- Javascripts -->
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/bootstrap.min.js"></script>    
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="/js/html5shiv.js"></script>
    <![endif]-->
    <!-- Google Analytics -->
    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-26811143-1']);
      _gaq.push(['_trackPageview']);

      (function () {
          var ga = document.createElement('script');
          ga.type = 'text/javascript';
          ga.async = true;
          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
          var s = document.getElementsByTagName('script')[0];
          s.parentNode.insertBefore(ga, s);
      })();
    </script>
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
 <!-- "Fork me on Github" Ribbon -->
    <a href="https://github.com/WPN-XM/WPN-XM">
      <img width="149" height="149" alt="Fork WPN-XM on GitHub" 
           src="images/fork-me-on-github.png" class="github-ribbon" />
    </a>

    <!-- Top Navigation Bar -->
    <header class="navbar navbar-inverse navbar-fixed-top" role="banner" id="section-home">
      <div class="container" id="top-nav">
        <!-- Logo -->
        <a class="navbar-brand" href="#">
          <img alt="WPN-XM" src="images/logo-transparent.png" width="74" heigth="59" />
        </a>
        <!-- Menu Items -->
        <nav class="collapse navbar-collapse" role="navigation" id="navigation-bar">
          <ul class="nav navbar-nav">
            <li class="active"><a href="index.html#section-home">Home</a></li>
            <li><a href="index.html#section-about">About</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Downloads <span class="caret"></span></a>
              <ul class="dropdown-menu">                
                <li><a href="index.html#section-installer-download-table">Installation Wizards</a></li>
                <li><a href="components.php">Web Components</a></li>
              </ul>
            </li>            
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Community <span class="caret"></span></a>
              <ul class="dropdown-menu">                                       
                <!--<li><a href="#">Forum</a></li>-->
                <li><a href="https://groups.google.com/forum/#!forum/wpn-xm">Mailinglist</a></li>                         
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Documentation <span class="caret"></span></a>
              <ul class="dropdown-menu">                                       
                <!--<li><a href="#section-docu-manual">Manual</a></li>-->
                <li><a href="https://github.com/WPN-XM/WPN-XM/wiki">Wiki</a></li>
              </ul>
            </li>
            <li><a href="index.html#section-donate">Donate</a></li> 
            <li><a href="index.html#section-getinvolved">Get Involved</a></li> 
            <li><a href="index.html#section-imprint">Imprint</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">   
            <li><a href="https://github.com/WPN-XM/WPN-XM/issues/new">Report Issue</a></li>
            <li class="dropdown">
              <a id="git" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                Github <span class="caret"></span>
              </a>
              <ul class="dropdown-menu">
                <li><a href="https://github.com/WPN-XM/WPN-XM/">WPN-XM Build Tools</a></li>                                 
                <li role="separator" class="divider"></li>
                <li><a href="https://github.com/WPN-XM/registry">Registry</a></li>
                <li><a href="https://github.com/WPN-XM/updater">Updater</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="https://github.com/WPN-XM/webinterface">Webinterface</a></li>
                <li><a href="https://github.com/WPN-XM/server-control-panel">Server Control Panel</a></li>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
    </header>

    <div class="container" id="content">

      <div class="col-md-12">
        <div class="row">
          <div class="col-md-1"></div>
          <div class="col-md-10">

            <!-- Logo -->
            <div class="header">
              <div id="logo"></div>
              <h1 style="visibility:hidden; line-height: 1px;" >WPN-XM</h1>
              <h2><strong itemprop="name">WPИ-XM</strong> is a free and open-source web server solution stack for professional PHP development on the Windows<small><sup>&reg;</sup></small> platform.</h2>
            </div>

            <hr/>


EOD;
}
