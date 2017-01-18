<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

class InstallerCompareViewRenderer
{
    public $versionA;
    public $versionB;
    public $installerNameA;
    public $installerNameB;
    public $comparison;
       
    public function render()
    {  
        define('RENDER_WPNXM_HEADER_LOGO', true);        
        include __DIR__ . '/../view/header.php';
        include __DIR__ . '/../view/topnav.php';
        include __DIR__ . '/../view/footer_scripts.php';
        ?>
        <div class="col-md-10 center">
            <div class="panel panel-default">
                <div class="panel-heading centered">
                  <h3>Compare Installers</h3> 
                  <p>Installer Software &amp; Version Changelog</p>
                </div>                
            </div>
        </div>
        <?php 
        $html = $this->renderSelectionPanel();
        $html .= $this->renderComparisonPanel();
        $html .= $this->renderScriptSection();    
        $html .= '</div></div></div></div>';   
        $html .= '</body></html>';        
        return $html;
    }
    
        public function renderSelectionPanel()
    {
        $dropdownsA = InstallerSelectionRenderer::getDropdowns("A", $this->versionA, $this->installerNameA);
        $dropdownsB = InstallerSelectionRenderer::getDropdowns("B", $this->versionB, $this->installerNameB);
        
        $html = '<div class="col-md-10 center">';
        $html .= '<div class="panel panel-default">';
        $html .= '<div class="panel-heading bold">Please select the two installers you want to compare with each other!</div>';
        $html .= '<div class="panel-body">';
        $html .= '<table class="table table-condensed">';        
        $html .= '<thead>';
        $html .= '<tr><th>Installer A</th><th>Installer B</th></tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><td>'.$dropdownsA.'</td><td>'.$dropdownsB.'</td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<p>
                    The table below shows you the comparison between these two installers.<br>
                    For each installer, you see the software components shipped and the versions.<br>
                    The change column indicates, when a software was added, updated or removed.<br>                    
                    This allows you to quickly notice changes between installers.                    
                  </p>';
        $html .= '</div>'; // close panel-body
        $html .= '<div class="panel-footer">                 
                  <p>
                    To see differences between all installers you can also use the
                    <a class="btn btn-sm" href="version-matrix.php">Version Matrix</a>.
                </p>
              </div>';
        $html .= '</div></div>'; // close panel-default, md-10 center  
        return $html;
    }
    
    public function renderComparisonPanel()
    {
        $html = '<div class="col-md-10 center">';
        $html .= '<div class="panel panel-default">';
        $html .= '<div class="panel-heading bold">Installer Comparison</div>'; 
        $html .= '<div class="panel-body">'; 
        $html .= '<table id="compare-installers-table" class="table table-bordered table-condensed table-hover">';
        $html .= '<thead>';
        $html .= '<tr><th>&nbsp;</th><th>Installer A</th><th>Installer B</th><th>&nbsp;</th></tr>';
        $html .= '<tr><th>&nbsp;</th><th>'.$this->installerNameA.'</th><th>'.$this->installerNameB.'</th><th>&nbsp;</th></tr>';
        $html .= '<tr><th>Software</th><th>Version</th><th>Version</th><th>Changed?</th></tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= $this->renderTableBody();                
        $html .= '</tbody></table>';
        $html .= '</div></div></div>'; // close panel-body, panel-default, md-10 center       
        return $html;
    }
            
    public function renderTableBody()
    {   
        $html = '';
        foreach ($this->comparison as $software => $data)
        {
            $html .= sprintf('<tr%s><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $this->renderTableRowColor($data['diffState']),
                $software,
                $data['versionA'],
                $data['versionB'],                
                $this->renderChangeLabel($data['diffState'])
            );   
        }
        return $html;
    }
    
    public function renderChangeLabel($diffState)
    {
        $label = '<span class="label label-%s">%s</span>';
        
        if($diffState === 'higher') {
            return sprintf($label, 'warning', '▲ updated');
        }
        if($diffState === 'equal') {
            return sprintf($label, 'default', ''); // ▬
        }
        if($diffState === 'lower') {
            return sprintf($label, 'danger', '▼ downgraded');
        } 
        if($diffState === 'removed') {
            return sprintf($label, 'danger', 'x removed');
        }
        if($diffState === 'added') {
            return sprintf($label, 'success', '+ added');
        }
    }
    
    /**
     * http://getbootstrap.com/css/#tables-contextual-classes
     */
    public function renderTableRowColor($diffState)
    {
        if($diffState === 'higher') {
            return ' class="warning"';
        }
        if($diffState === 'equal') {
            return '';
        }
        if($diffState === 'lower') {
            return ' class="warning"';
        }
        if($diffState === 'removed') {
            return ' class="danger"';
        }
        if($diffState === 'added') {
            return ' class="success"';
        }
        return '';
    }
       
    public function renderScriptSection()
    {
        $html = '<script> 
                    /**
                     * jQuery Extension: replaceOptions()
                     *
                     * var options = [
                     *   {text: "one", value: 1},
                     *   {text: "two", value: 2}
                     * ];
                     *
                     * $("#select").replaceOptions(options);
                     */
                    (function($, window) {
                    $.fn.replaceOptions = function(options) {
                      var self, $option; this.empty(); self = this;
                      $.each(options, function(index, option) {
                        $option = $("<option></option>").attr("value", option.value).text(option.text);
                        self.append($option);
                      });
                    };              
                  })(jQuery, window);
                                   
                $("#installerVersionA").change(function() {
                  var version = $(this).val();
                  getVersionDropdown(version, "A");
                });
                $("#installerVersionB").change(function() {
                  var version = $(this).val();
                  getVersionDropdown(version, "B");                 
                });                
                function getVersionDropdown(version, installerSide)
                {
                    var pagename = "'.basename($_SERVER['SCRIPT_NAME']).'";
                    var url = pagename + "?action=get-installers&version="+version;                    
                    $.getJSON(url, function(data) {
                        var dropdown = $("#installersWithVersion"+installerSide);
                        dropdown.replaceOptions(data);
                        //dropdown.attr("size", data.length);
                        dropdown.find("option:first").attr("selected","selected");
                    });
                }
                
                $("#installersWithVersionA").change(function() {
                  var installerA = $(this).val();
                  var installerB = $("#installersWithVersionB").val();
                  getInstallerDropdown(installerA, installerB);
                });
                $("#installersWithVersionB").change(function() {
                  var installerA = $("#installersWithVersionA").val();
                  var installerB = $(this).val();                  
                  getInstallerDropdown(installerA, installerB);
                });                
                function getInstallerDropdown(installerA, installerB)
                { 
                    var pagename = "'.basename($_SERVER['SCRIPT_NAME']).'";
                    var url = pagename +"?installerA="+installerA
                                       +"&installerB="+installerB;                                                                               
                    window.location.href = url;
                }                
                </script>';
                
        return $html;
    }
}

class InstallerSelectionRenderer
{
    /**
     * Returns the HTML fpr the two dropdowns Version and Installer at once.
     * 
     * @param string $side
     * @param string $version
     * @param string $installerName
     * @return string Two HTML select elements: Version and Installer.
     */
    public static function getDropdowns($side, $version, $installerName)
    {            
        $versions   = InstallerRegistries::getVersions(); 
        $installerName = basename($installerName, '.json');
        $installers = InstallerRegistries::getInstallerNamesForVersion($version);
        
        $versionDropdownName = 'installerVersion'.$side;
        $installerDropdownName = 'installersWithVersion'.$side;
        
        $html = self::renderDropdown($versionDropdownName, $versions, $version);
        $html .= self::renderDropdown($installerDropdownName, $installers, $installerName);
        
        return $html;
    }
    
    /**
     * Renders a HTML dropdown/select element.
     * 
     * @param string $name
     * @param array $data
     * @param string $selected
     * @return string HTML select element.
     */
    public static function renderDropdown($name, array $data, $selected = null)
    {       
        $html = '<select id="'.$name.'" name="'.$name.'" style="vertical-align:top">'; // size="'.count($data).'"
        
        foreach($data as $id => $value) {
            if($selected == $value) {
                $html .= '<option selected>'.$value.'</option>';
            } else {
                $html .= '<option>'.$value.'</option>';
            }
        }
        $html .= '</select>';
        
        return $html;
    }
}
