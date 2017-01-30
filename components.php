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
 * The script renders a "component list" with "version dropdowns".
 * This allows a user to quickly select and download a certain software of the registry.
 * The display is splitted between "Software Components" and "PHP Extensions".
 */

echo render_header();
?>

  <div class="col-md-13">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 id="download">Software Components for Web Development on Windows</h2>
      </div>
      <div class="panel-body">
        <p style="font-size: 16px;">
          All software components of the WPN-XM software registry are available for selective download.
          <br>You will always download directly from the software vendor.
          <br>You have to install these components manually.
          <br>After downloading, go to the appropriate manual and follow the installation instructions.
          <br>This collection of download links is provided with the intention to save you some time.
        </p>
      </div>
      <div class="panel-footer" style="min-height: 90px;">
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
        </div>
      </div><!-- End: Panel Footer -->
    </div><!-- End: Panel-->
  </div><!-- End: Col -->

</div><!-- End: header col-md-10 -->

<?php
// WPNXM Software Registry
$registry = include __DIR__ . '/registry/wpnxm-software-registry.php';
echo render_tables(split_registry_into_components_and_extensions($registry));
render_footer_scripts();
echo '</div></div></div></div></div></body></html>';

function render_tables($splitRegistry)
{
    $html = '';

    $html .= '<div class="row-components">'
        . '<div class="download-components col-md-6">'
        . '<div class="panel panel-default">'
        . '<div class="panel-heading"><h4>Software Components <small id="software-components-counter">(0)</small></h4></div>'
        . '<div class="panel-body">'
        . '<table class="table table-condensed table-hover" id="software-components">'
        . '<thead><tr><th>Software Component</th><th>Versions</th><th>Latest Version</th></tr></thead>'
        . '<tbody>'
        . $splitRegistry['components']
        . '</tbody></table></div></div></div>';

    $html .= '<div class="download-components download-extensions col-md-6">'
        . '<div class="panel panel-default">'
        . '<div class="panel-heading"><h4>PHP Extensions <small id="php-extensions-counter">(0)</small></h4></div>'
        . '<div class="panel-body">'
        . '<table class="table table-condensed table-hover" id="php-extensions">'
        . '<thead><tr><th>PHP Extension</th><th>Versions</th><th>Latest Version</th></tr></thead>'
        . '<tbody>'
        . $splitRegistry['extensions']
        . '</tbody></table>'
        . '</div></div>'; // end - body

    $html .= '<div class="alert alert-info" role="alert">
            <div>
              <strong>You may find more PHP Extensions for Windows here:</strong><br/>
              <div class="btn-group" role="group" aria-label="php-extension-downloads">
                <a class="btn btn-default" href="https://pecl.php.net/">pecl.php.net</a>
                <a class="btn btn-default" href="http://windows.php.net/downloads/pecl/">windows.php.net/downloads/pecl</a>
                <a class="btn btn-default" href="https://github.com/gophp7/gophp7-ext/wiki/extensions-catalog">goPHP7 Extension Catalog</a>
              </div>
            </div>
            <br>
            <div>
              <strong>PHP Version Lifetime</strong><br/>
              <div class="btn-group" role="group" aria-label="php-version-lifetime">
                <a class="btn btn-default" href="http://php.net/supported-versions.php">Supported Versions</a>
                <a class="btn btn-default" href="http://php.net/eol.php">Unsupported Versions (End Of Life)</a>
              </div>
            </div>
          </div>';

    return $html;
}

function split_registry_into_components_and_extensions($registry)
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

    $html = PHP_EOL . '<tr>'
        . '<td><a id="' . getAnchor($component['name']) . '" href="' . $component['website'] . '"><strong>' . $name . '</strong></a>' . '</td>'
        . '<td>' . render_version_dropdown_for_extension($component) . '</td>'
        . '<td><span class="label label-primary">' . $component['latest']['version'] . '</span></td>'
        . '</tr>' . PHP_EOL;

    return $html;
}

function render_version_dropdown_for_extension($component)
{
    // skip APC, it was only available up to PHP5.4 EOL
    if($component['name'] === 'PHP Extension APC') {
      return '<span class="label label-info">Was available up to PHP v5.4 (EOL). Replaced by APCu.</span>';
    }

    unset($component['name'], $component['website'], $component['latest']);

    $component = sortVersionsHighToLow($component);

    $eol_php_versions = ['5.4', '5.4.0', '5.5', '5.5.0'];

    // restructure the array
    foreach ($component as $version => $bitsizes) {
        foreach ($bitsizes as $bitsize => $php_versions) {
            foreach ($php_versions as $php_version => $url) {
                // skip data for EOL PHP versions
                if(in_array($php_version, $eol_php_versions)) {
                   continue;
                }
                $v[$bitsize][$php_version][$version] = $url;
            }
        }
    }

	if(empty($v)) {
		return '<span class="label label-danger">No Versions found for PHP 5.6+ !</span>';
	}

    // render
    $html = '';
    foreach ($v as $bitsize => $php_version) {
        $html .= '<span class="label label-default left">' . $bitsize . '</span>';
        foreach ($php_version as $php_v => $urls) {
            $html .= ' ' . $php_v;
            $html .= PHP_EOL .' <select>';
            $html .= '<option value="" selected disabled>Select..</option>';
            foreach ($urls as $ver => $url) {
                $html .= '<option value="' . $url . '">' . $ver . '</option>';
            }
            $html .= '</select>' . PHP_EOL;
        }
        $html .= '<br>';
    }

    return $html;
}

function getAnchor($componentName)
{
    return strtolower(str_replace([' '],['-'], $componentName));
}

function render_tr_for_normal_component($component)
{
    $html = PHP_EOL . '<tr>'
        . '<td><a id="' . getAnchor($component['name']) . '" href="' . $component['website'] . '"><strong>' . $component['name'] . '</strong></a>' . '</td>'
        . '<td>' . render_version_dropdown($component) . '</td>'
        . '<td><a href="' . $component['latest']['url'] . '">' . $component['latest']['version'] . '</a></td>'
        . '</tr>' . PHP_EOL;

    return $html;
}

function render_version_dropdown($component)
{
    // if a component only provides a latest version, we need no dropdown
    if ($component['latest']['version'] === 'latest') {
        return '&nbsp;';
    }

    unset($component['name'], $component['website'], $component['latest']);

    $component = sortVersionsHighToLow($component);

    $html = PHP_EOL . '<select>';
    $html .= '<option value="" selected disabled>Please select a version...</option>';
    foreach ($component as $version => $url) {
        $html .= '<option value="' . $url . '">' . $version . '</option>';
    }
    $html .= '</select>' . PHP_EOL;

    return $html;
}

function render_header()
{
    define('RENDER_WPNXM_PAGE_TITLE', 'WPN-XM - Software Components and PHP Extensions');
    define('RENDER_WPNXM_HEADER_LOGO', true);
    require __DIR__ . '/view/header.php';
    require __DIR__ . '/view/topnav.php';
}

function render_footer_scripts()
{
    require __DIR__ . '/view/footer_scripts.php';

    echo '<script>   
    // scroll into view   
    if(document.location.hash) {
        var id = document.location.hash.substring(1);
        var el = $("#"+id);
        window.scroll(0, el.offset().top-125);
        el.fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn();
    };
    // bind "change" event of all select boxes (dropdowns)
    $("select").change(function(){
       if (this.value) {
        window.location.href=this.value;
       }
    });
    // update Counter for PHP Extensions
    $("#php-extensions-counter").html( "("+ $("table#php-extensions > tbody > tr").length +")" );
    // update Counter for Software Components
    $("#software-components-counter").html( "("+ $("table#software-components > tbody > tr").length +")" );
    </script>';
}

function sortVersionsHighToLow($versions)
{
    uasort($versions, function($a, $b) {
        if(is_array($a)) {
          return;
        }
        return version_compare($a, $b);
    });

    return array_reverse($versions, true);
}