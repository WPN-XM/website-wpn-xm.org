<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

class VersionMatrixRenderer
{
    public $registry;
    public $installerRegistries;
    
    public function __construct($registry, $installerRegistries)
    {
        $this->registry = $registry;
        $this->installerRegistries = $installerRegistries;
    }

    /**
     * @param $registry
     * @param $software
     * @return string
     */
    function getVersion($registry, $software)
    {
        if (isset($registry[$software]) === true) {
            return '<span class="badge badge-info">' . $registry[$software] . '</span>';
        }
        return '&nbsp;';
    }

    /**
     * @return string
     */
    function renderTableHeader()
    {
        $html = '';
        foreach ($this->installerRegistries as $wizardName => $wizardRegistry) {
            $html .= '<th>' . $wizardName . '</th>';
        }
        return $html;
    }

    /**
     * @param $software
     * @return string
     */
    function renderTableCells($software)
    {
        $html = '';
        foreach ($this->installerRegistries as $wizardName => $wizardRegistry) {
            // normal versions
            if (isset($wizardRegistry['registry'][$software]) === true) {
                $html .= '<td class="version-number">' . $wizardRegistry['registry'][$software] . '</td>';
            } else {
                $html .= '<td>&nbsp;</td>';
            }
        }

        return $html;
    }
    
    function render()
    {
        define('RENDER_WPNXM_HEADER_LOGO', true);
        include __DIR__ . '/../view/header.php';
        include __DIR__ . '/../view/topnav.php';
        ?>   

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                  <h2 class="centered">Version Comparison Matrix</h1>
                  <small>Overview of packaged software components and their versions for all packaged WPN-XM installers.</small>
                </div>
                <div class="panel-body">
                  <p>
                    The table shows you all software components shipped by all our installation wizards and their versions.<br/>
                    It allows you to quickly notice, if a certain software is packaged and which version.</p>
                  <p>
                    To see the differences between two installers you can also 
                    <a class="btn btn-sm" href="compare-installers.php">Compare Installers</a>.
                </p>
              </div>
            </div>
        </div>

        <table id="version-matrix" class="table table-condensed table-bordered table-version-matrix"
               style="width: auto !important; padding: 0px; vertical-align: middle; background-color: #fefefe;">
            <thead>
                <tr>
                    <th>Software Components (<?php echo count($this->registry); ?>)</th>
                    <?php echo $this->renderTableHeader(); ?>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($this->registry as $software => $data) {
                echo '<tr><td>' . $software . '</td>' . $this->renderTableCells($software) . '</tr>';
            }
            ?>
            </tbody>
        </table>
                
        <?php include __DIR__ . '/../view/footer_scripts.php'; ?>                    
            </body></html>
        <?php
    }
}
