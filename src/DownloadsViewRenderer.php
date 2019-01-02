<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

include_once __DIR__ . '/Registry.php';

class DownloadsViewRenderer
{
    private $downloads = [];
    private $releases  = [];

    public function __construct($downloads, $releases)
    {
        $this->downloads = $downloads;
        $this->releases = $releases;
    }

    public function renderJson()
    {
        $json = json_encode($this->downloads);
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }

    public function renderHtml()
    {
        header('Content-Type: text/html; charset=utf-8');

        define('RENDER_WPNXM_PAGE_TITLE', 'WPN-XM - Downloads');
        define('RENDER_WPNXM_HEADER_LOGO', true);
        include __DIR__ . '/../view/header.php';
        include __DIR__ . '/../view/topnav.php';
        echo $this->renderHeader();
        echo $this->renderDownloadsTable();
        include __DIR__ . '/../view/footer_scripts.php';
        echo $this->renderFooter();
    }

    public function renderHeader()
    {
        $html = '
        <!-- Header -->
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
                    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
                </div>
            </div>
        ';

        return $html;
    }

    public function renderDownloadsTable()
    {
        $html  = '  <!-- Downloads Table -->' . "\n";
        $html .= '  <div class="panel-body" id="downloads-list">' . "\n";
        $html .= '  <table style="width:auto; min-width:900px">'  . "\n";

        foreach($this->releases as $release) {
            $html .= $this->renderRelease($release);
        }

        $html .= '  </table>' . "\n";
        $html .= '  </div>' . "\n";

        return $html;
    }

    public function renderRelease($release)
    {
        // row for a new release version
        $html = '  <tr>' . "\n";

        // print Name, Date, Total Downloads
        $html .= '    <td class="release-cell">' . "\n";
        $html .= '      <h4>WPИ-XM v' . $release['version'] . '</h4>' . "\n";
        $html .= '      <small class="btn btn-sm" title="Release Date">Release Date<br>'
            . '<b>' . $release['date'] . '</b></small>' . "\n";
        $html .= '      <small class="btn btn-sm" title="Total Downloads">Downloads<br>'
            . '<span class="bold installer-downloads">' . $release['downloads'] . '</span></small>' . "\n";
        $html .= '    </td>' . "\n";

        // print release notes, changelog, github tag
        $html .= '    <td style="vertical-align: middle;">' . "\n";
        $html .= '      ' . $release['release_notes'] . '&nbsp;' . "\n";
        $html .= '      ' . $release['changelog'] . '&nbsp;' . "\n";
        $html .= '      ' . $release['github_tag'] . '&nbsp;' . "\n";
        $html .= '    </td>' . "\n";
        $html .= '  </tr>' . "\n";

        foreach($this->downloads as $installer) {
            if(isset($release['version'])) { 
                if($installer['version'] == $release['version']) {
                    $html .= $this->renderInstaller($installer);
                }
            }
        }

        return $html;
    }

    public function renderInstaller($installer)
    {
        // download button for installer, filesize, download counter
        $html = '  <tr>' . "\n";
        $html .= '    <td colspan="2" class="installer-file-data">' . "\n";
        $html .= '      <a class="btn btn-large btn-success" href="' . $installer['download_url'] . '">'
            . '<i class="glyphicon glyphicon-cloud-download"></i> ' . $installer['name'] . '</a>' . "\n";
        $html .= '      <div class="btn btn-small bold" title="Filesize">' . $installer['size'] . '</div>' . "\n";
        $html .= '      <div class="btn btn-small bold" title="Downloads">' . $installer['downloads'] . '</div>' . "\n";

        $html .= $this->renderComponentListForInstaller($installer);

        return $html;
    }

    public function renderFooter()
    {
$html = <<<'HTML'
    <!-- RenderFooter -->
    <script>
    function calculateTotalDownloads() {
        var total = 0;
        $('span.installer-downloads').each(function () {
            total += parseInt($(this).text());
        });
        $('small.total-amount-downloads').html(total);
      }
      calculateTotalDownloads();
    </script>
    </div> <!-- /panel -->
    </div> <!-- /row -->
    </div> <!-- /col-md10 -->
    </div> <!-- /row -->
    </div> <!-- /col-md12 -->
    </div> <!-- /container -->
    </body>
</html>
HTML;

        return $html;
    }

    public function renderComponentListForInstaller($installer)
    {
        // check installer type: if webinstaller, always latest for all components
        if ('webinstaller' === strtolower($installer['installer'])) {
            return '<tr><td colspan="2">Latest Components fetched from the Web</td></tr>';
        }

        /**
         * build filename from installer details
         */

        $platform = isset($installer['platform']) ? '-' . $installer['platform'] : '';
        // set PHP version starting from 0.8.0 on
        $phpversion = isset($installer['phpversion']) ? '-' . $installer['phpversion'] : '';
        // PHP version dot fix
        $phpversion = str_replace(['php5', 'php7'], ['php5.', 'php7.'], $phpversion);
        
        $filename = strtolower($installer['installer']) . '-' . $installer['version'] . $phpversion . $platform;
        $normalized_filename = str_replace('.', '-', $filename);

        $file = dirname(__DIR__) . '/registry/installer/v'.$installer['version'].'/' . $filename . '.json';

        $html = '';

        if (!is_file($file)) {
            $html .= '  <tr>' . "\n";
            $html .= '     <td colspan="2">Components included: No data.</td>'. "\n";
            $html .= '  </tr>'. "\n";
            return $html;
        } 

        // load installer registry data (json)
        $installerRegistry = json_decode(file_get_contents($file));  

        $number_of_components = count($installerRegistry);

        $html .= '      <div class="btn btn-small bold" title="collapse-toggle-'.$normalized_filename.'" data-toggle="collapse" data-target="#multiCollapse-'.$normalized_filename.'" aria-expanded="false" aria-controls="multiCollapse-'.$normalized_filename.'">Show Components ('.$number_of_components.')</div>';
        $html .= '    </td>' . "\n";
        $html .= '  </tr>' . "\n";     

        $html .= '  <tr>' . "\n";
        $html .= '     <td colspan="2">';

        //if($number_of_components >= 10) {
        $html .= $this->renderComponentListMultiColumn($installerRegistry, $normalized_filename, $number_of_components);
        //} else {
        //  $html .= $this->renderComponentListCommaSeparated($installerRegistry, $number_of_components);
        //}

        $html .= '     </td>'. "\n";
        $html .= '  </tr>'. "\n";

        return $html;
    }

    public function renderComponentListMultiColumn($installerRegistry, $normalized_filename,  $number_of_components)
    {
        $registryObject = new Registry;
        $registry = $registryObject->loadRegistry();

        $number_of_php_extensions = 0;

        $html = '<div class="collapse multi-collapse" id="multiCollapse-'.$normalized_filename.'">The following ' . $number_of_components . ' Components are included: '. "\n";
        $html .= '       <!-- Component List for "' . $normalized_filename . '" -->' . "\n";
        $html .= '       <ul class="multi-column-list">' . "\n";
  
        foreach ($installerRegistry as $i => $component)
        {
            $software = $registryObject->updateDeprecatedSoftwareRegistryKeyNames($component[0]);

            // skip - components removed from registry, still in 0.7.0 and breaking it
            if ($registryObject->isDeprecatedSoftwareRegistryKeyName($software)) {
                continue;
            }

            $version = $component[3];

            // php extension - they are appended to the extension html fragment
            if (false !== strpos($software, 'phpext_')) {
                $number_of_php_extensions++;
                if(!isset($extensions_html)) {
                    $extensions_html = '';
                }
                $name = str_replace('PHP Extension ', '', $registry[$software]['name']);
                $extensions_html .= '        <li><b>' . $name . '</b> ' . $version . '</li>' . "\n";
                continue;
            }
          
            // normal component
            $name = $registry[$software]['name'];
            $html .= '        <li><b>' . $name . '</b> ' . $version . '</li>'. "\n";
        }
        unset($installerRegistry);

        if($number_of_php_extensions == 1) {
           $html .= '<li>Included PHP Extension:</li>'. "\n" . $extensions_html;
        }
        elseif($number_of_php_extensions >= 1) {
           $html .= '<li>Included PHP Extensions ('.$number_of_php_extensions.'):</li>'. "\n" . $extensions_html;
        }
        
        $html .= '      </ul>'. "\n";
        
        return $html;
    }

    /*public function renderComponentListCommaSeparated($installerRegistry, $number_of_components)
    {
        $registry = Registry::loadRegistry();
     *
        $html            = '';
        $extensions_html = ', PHP Extension(s): ';

        foreach ($installerRegistry as $i => $component) {
            $software = $component[0];

            // skip - components removed from registry, still in 0.7.0 and breaking it
            if (Registry::isDeprecatedSoftwareRegistryKeyName($software)) {
                continue;
            }

            $version   = $component[3];

            if (false !== strpos($component[0], 'phpext_')) {
                $name = str_replace('PHP Extension ', '', $registry[$component[0]]['name']);
                $extensions_html .= '<b>' . $name . '</b> ' . $version;
                continue;
            }

            $name = $registry[$software]['name'];

            $html .= '<b>' . $name . '</b> ' . $version;
            $html .= ($i + 1 !== $number_of_components) ? ', ' : '';
        }
        unset($installerRegistry);

        $html .= $extensions_html;

        return $html;
    }*/
}