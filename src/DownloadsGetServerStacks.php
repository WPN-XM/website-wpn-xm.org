<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2017 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * Fetch download stats for multiple windows server stacks.
 */
class DownloadsGetServerStacks
{
    public $sourceforge;

    private $sourceForgeProjects = ['quickeasyphp', 'wampserver', 'wtnmp', 'xampp'];

    public function __construct()
    {
        $this->sourceforge = new SourceForgeStatsApi;
    }

    public function fetch()
    {
        $seriesJson = [];

        foreach($this->sourceForgeProjects as $project)
        {
            $json  = $this->sourceforge->getJson($project);

            $array = json_decode($json, true);    
            $array = $this->buildHighChartArray($project, $array);
            $json  = json_encode($array);

            // aggregate stats data (series)
            if(empty($seriesJson)) {
                $seriesJson = $json;            
            } else {
                $seriesJson = $seriesJson . ",\n" . $json;
            }        
        }

        file_put_contents(dirname(__DIR__).'/downloads/downloads_serverstacks.json', $seriesJson);
    }

    private function buildHighChartArray($project, array $data)
    {
        $name  = $this->getProjectName($project);
        $color = $this->getColor($project);

        return ['name' => $name, 'data' => $data, 'color' => $color];
    }

    private function getProjectName($project)
    {
        if($project == 'xampp') {      
            return 'XAMPP';
        }
        if($project == 'wtnmp') {      
            return 'WT-NMP';
        }
        if($project == 'quickeasyphp') { 
            return 'EasyPHP';
        }
        if($project == 'wampserver') {  
            return 'WAMPServer';
        }
        /*if($project == 'wpnxm') {  
            return 'WPN-XM';
        }*/
    }

    private function getColor($project)
    {
        if($project == 'xampp') {      
            return '#f7a35c';
        }
        if($project == 'wtnmp') {      
            return '#90ed7d';
        }
        if($project == 'quickeasyphp') { 
            return '#7cb5ec';
        }
        if($project == 'wampserver') {  
            return '#434348';
        }
        /*if($project == 'wpnxm') {  
            return '#BF0B23';
        }*/
    }
}

/**
 * Get statistics from Sourceforge.
 */
class SourceForgeStatsApi
{
    public function getJson($project)
    {
        $cache_file = dirname(__DIR__) . '/downloads/downloads_serverstack_'.$project.'.json';

        if (file_exists($cache_file) && (filemtime($cache_file) > (time() - (30 * 24 * 60 * 60)))) {
            // Use cache file, when not older than 30 days.
            $json = file_get_contents($cache_file);
        } else {
            // The cache is out-of-date. Load the JSON data from Github.
            $json = self::doRequest($project);

            // reduce the json data set to relevant stuff, before we cache it
            $array = json_decode($json, true);
            $array = $this->reduceDataSet($array);
            $array = $this->fixDataSet($array);
            $json = json_encode($array);

            file_put_contents($cache_file, $json, LOCK_EX);
        }

        return $json;
    }

    private function doRequest($project)
    {
        // start and end date (grab monthly stats only; use day 01 of a month)
        $start_date = '2010-11-01';
        $end_date   = date('Y-m-01', strtotime('-1 month'));

        $url = "http://sourceforge.net/projects/$project/files/stats/json?start_date=$start_date&end_date=$end_date";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_USERAGENT      => 'wpn-xm.org - stats page',
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    private function reduceDataSet($array)
    {
        unset(
            $array['end_date'], 
            $array['oses'], 
            $array['countries'], 
            $array['messages'], 
            $array['oses_with_downloads'], 
            $array['oses_by_country'], 
            $array['summaries'], 
            $array['geo']
        );

        return $array;
    }

    private function fixDataSet($array)
    {
       $data = [];
       foreach($array['downloads'] as $download)  {
           $data[] = [(strtotime($download[0])*1000), $download[1]];
       }
       return $data;
    }
}
