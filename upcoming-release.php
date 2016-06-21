<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016, Jens A. Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

class UpcomingReleaseDataCollector
{     
    private function doRequest($url)
    {
        $options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return $response;
    }
    
    public function getNextVersion()
    {
        // get all milestones from github        
        /*$milestonesJson = $this->doRequest('https://api.github.com/repos/wpn-xm/wpn-xm/milestones');
        $milestones = json_decode($milestonesJson, true);  
        
        // get sorted titles (re-index array by title and return only the sorted keys)
        $milsteonesIndexedByTitle = array_column($milestones, null, 'title');
        $milestonesTitles = array_keys($milsteonesIndexedByTitle);        
        sort($milestonesTitles);
       
        // return lowest milestone 
        return $milestonesTitles[0];*/
        return 'v0.8.7';
    }
    
    public function getCurrentVersion()
    {       
        $installers = $this->getInstallers() ;
        
        return $this->getVersionFromInstallerFilename($installers[0]);
    }
    
    private function getInstallers()
    {
        $installers = glob(__DIR__.'/registry/installer/v*.*', GLOB_ONLYDIR);
        
        return array_reverse($installers); 
    }
    
    private function getVersionFromInstallerFilename($installer)
    {
        // return array_reverse(explode('/', $installer));
        return substr($installer, strrpos($installer, '/') + 1);
    }
    
    /**
     * Fetch last modification date of the installer registries of the next version.
     * 
     * @return string Last Modification Date of Installer Registries of the next version.
     */
    public function getLastModificationDateOfNextInstallerRegistries()
    {
        $installers = glob(__DIR__.'/registry/installer/next/*.*');        
        $installers = array_map('filemtime', $installers);
        $installers = array_unique($installers, SORT_NUMERIC);        
        return date('Y-m-d', $installers[0]);
    }
}

class LastUpdatedBadgeRenderer
{    
    public $nextInstallersLastUpdatedDate;
        
    public function __construct($nextInstallersLastUpdatedDate)
    {
        $this->nextInstallersLastUpdatedDate = $nextInstallersLastUpdatedDate;
    }
    
    public function calculateDaysDifference($date1, $date2)
    {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);

        return $interval->days;
    }
    
    public function render()
    {
        $today = date('Y-m-d');
        
        $daysDiff = $this->calculateDaysDifference($this->nextInstallersLastUpdatedDate, $today);
        
        if ($daysDiff <= 0) {
            $cssClass = 'label-success';
            $msg = 'up to date';
        } elseif($daysDiff <= 14) {
            $cssClass = 'label-warning';
            $msg = $daysDiff . ' days ago';
        } else {
            $cssClass = 'label-danger';
            $msg = $daysDiff . ' days ago';
        }
        
        return sprintf('<span class="label %s">%s</span>', $cssClass, $msg);
    }
}

class UpcomingReleaseBox
{       
    public $currentVersion;
    public $nextVersion;    
    public $nextInstallersLastUpdatedDate;
    
    public $compareInstallersUrl;
    public $openIssuesUrl;
    public $lastUpdatedBadge;
    
    public function __construct($lastUpdatedDate, $currentVersion, $nextVersion)
    {
        $this->nextInstallersLastUpdatedDate = $lastUpdatedDate;
        $this->currentVersion = $currentVersion;
        $this->nextVersion  = $nextVersion;
        
        $this->compareInstallersUrl = sprintf('compare-installers.php?from=%s&to=%s', $this->currentVersion, $this->nextVersion);        
        $this->openIssueUrl = sprintf('https://github.com/WPN-XM/WPN-XM/issues?q=is:open+is:issue+milestone:%s', $this->nextVersion);
        
        $lastUpdatedBadgeRenderer = new LastUpdatedBadgeRenderer($this->nextInstallersLastUpdatedDate);
        $this->lastUpdatedBadge = $lastUpdatedBadgeRenderer->render();
    }
       
    public function render()
    {
        $html = '<div class="panel panel-default" id="upcoming-release-box">
            <div class="panel-heading">
              <div class="centered">The next release will be
                <div id="upcoming-release"><div class="bold" id="version">WPИ-XM '.$this->nextVersion.'</div></div>
              </div>
            </div>
            <!-- List group -->
            <ul class="list-group">
              <li class="list-group-item centered">It\'s work in progress.</li>
              <li class="list-group-item"><strong>Todo</strong>
                <a class="btn bold pull-right" href="'.$this->openIssueUrl.'">Open Issues</a>
              </li>
              <li class="list-group-item"><strong>Done</strong>
                <a class="btn bold pull-right" href="https://github.com/WPN-XM/WPN-XM/blob/master/CHANGELOG.md#unreleased">Changelog</a>
              </li>
              <li class="list-group-item"><strong>Installer Registries</strong>
                <a class="btn bold pull-right" href="'.$this->compareInstallersUrl.'">Compare Installers</a>
              </li>
              <li class="list-group-item">The software components for the upcoming release were last updated on:</br>
                <div class="centered">'.$this->nextInstallersLastUpdatedDate.' '.$this->lastUpdatedBadge.'</div>                
              </li>
            </ul>
            <div class="panel-footer centered">When will you release? Sorry. No ETA.</div>
          </div>';
            
        return $html;    
    }
}

$data = new UpcomingReleaseDataCollector;

$upcomingReleaseBox = new UpcomingReleaseBox(
    $data->getLastModificationDateOfNextInstallerRegistries(),
    $data->getCurrentVersion(),
    $data->getNextVersion()
);

echo $upcomingReleaseBox->render();