<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

class GithubReleaseStats implements ArrayAccess
{
	public $releases;

	public $stats;

	function __construct()
	{
		$this->load_github_releases_data();
		$this->get_total_number_of_releases();
		$this->drop_unwanted_keys();
		krsort($this->releases);
		$this->get_release_stats();
		$this->get_downloads_by_php_version();
		$this->get_release_dates_and_development_time();
		$this->calculate_project_age();
	}

	function load_github_releases_data()
	{
	    $this->releases = json_decode(file_get_contents(__DIR__ . '/downloads/github-releases-cache.json'), true);
	}

	function drop_unwanted_keys()
	{
		$unwanted_keys = ['body', 'author', 'tarball_url', 'zipball_url', 'upload_url', 'draft', 'prerelease', 'target_commitish', 'id'];

		for($i = 0; $i < $this->stats['total_releases']; $i++) {
			foreach($unwanted_keys as $key) {
				unset($this->releases[$i][$key]);
			}
		}
	}

	function get_total_number_of_releases()
	{
		$this->stats['total_releases'] = count($this->releases);
	}

	function get_release_stats()
	{
		$this->stats['total_downloads'] = 0;
		$this->stats['installer_downloads'] = [];

		foreach($this->releases as $release) {
			$version = GithubReleaseStatsHelpers::fix_version_name($release['tag_name']);

	    	if(!isset($this->stats['installer_downloads'][$version])) $this->stats['installer_downloads'][$version] = 0;
	    	if(!isset($this->stats['installers_released_per_version'][$version])) $this->stats['installers_released_per_version'][$version] = 0;

	    	$this->stats['total_downloads'] += array_sum(array_column($release['assets'], 'download_count'));
	    	$this->stats['installer_downloads'][$version] = array_column($release['assets'], 'download_count', 'name');
			$this->stats['installers_released_per_version'][$version] = count($release['assets']);
		}

		$this->stats['total_installers_released'] = array_sum($this->stats['installers_released_per_version']);
	}

	function get_downloads_by_php_version()
	{
		// map "phpversion of filename" to "pretty php version"
		$php_versions = ['php54' => 'PHP 5.4', 'php55' => 'PHP 5.5', 'php56' => 'PHP 5.6', 'php70' => 'PHP 7.0', 'php71' => 'PHP 7.1'];
		foreach($php_versions as $phpversion) {
	        $downloads[$phpversion] = 0;
		}
	    $downloads = [];
	    $downloads_installer_php = [];

	    $php_versions_lc = array_keys($php_versions);

		foreach($this->stats['installer_downloads'] as $installer_version => $installer_and_downloads)
		{
			if($installer_version === 'v0.2.0') continue;

			$this->stats['downloads_by_installer_version'][$installer_version] = array_sum($installer_and_downloads);

			if(!isset($downloads_installer_php[$installer_version])) {
	    		$downloads_installer_php[$installer_version] = [];
	    	}

	    	foreach ($installer_and_downloads as $installer => $dls) {
	    		foreach($php_versions_lc as $phpversion) {
	    			$PHP = $php_versions[$phpversion];

        	    	// downloads by php version
        	    	if(!isset($downloads[$PHP])) {
        	    		$downloads[$PHP] = 0;
        	    	}

					// downloda by installer and php version
					if(!isset($downloads_installer_php[$installer_version][$PHP])) {
        	    		$downloads_installer_php[$installer_version][$PHP] = 0;
        	    	}

	    			if(false !== strpos($installer, $phpversion)) {
	    				$downloads[$PHP] += $dls;
	        		    $downloads_installer_php[$installer_version][$PHP] += $dls;
	        	    } elseif($installer_version === 'v0.7.0' and $PHP === 'PHP 5.4') {
	        	    	// 0.7.0 was PHP 5.4 only, without correct naming of the installer, so we can't strpos for a PHP version
	        	    	$downloads[$PHP] += $dls;
	        	    	$downloads_installer_php[$installer_version][$PHP] += $dls;
	        	    }
    			}
    		}
    	}

		$this->stats['downloads_per_installer_version'] = $downloads_installer_php;
	    $this->stats['downloads_by_php_version'] = $downloads;
	}

	/**
	 * Release Date and Development Time Taken (days_cost)
	 */
	static function get_release_dates($releases)
	{
		$releaseDates = [];
	    foreach ($releases as $release) {
	    	$versions[] = GithubReleaseStatsHelpers::fix_version_name($release['tag_name']);
	    	$releaseDates[] = DateTimeHelper::formatGithubDate($release['created_at']);
	    }

	    // add today with version "in dev" to calc the days from the last release
	    $versions[] = 'next_version_in_development_since';
	    $releaseDates[] = date('Y-m-d');

		usort($releaseDates, 'DateTimeHelper::usort_helper_sort_by_utc_time');

	    return array_combine($versions, $releaseDates);
	}

	static function calculate_days_between_releases($dates)
	{
		$dates = array_values($dates);
		$days = [];
		for($i = 0; $i < count($dates); $i++) {
			if(isset($dates[$i+1])) {
			     $days[] = DateTimeHelper::getDaysBetweenDates($dates[$i], $dates[$i+1]);
		    }
		}
		return $days;
	}

	function get_release_dates_and_development_time()
	{
		$releaseDates = self::get_release_dates($this->releases);
		$versions = array_keys($releaseDates);

	    $dateDiff = self::calculate_days_between_releases($releaseDates);
	    array_unshift($dateDiff, 'Started using Github Releases'); // push the rest of the stack down

	    $r = []; $i = 0;
	    foreach($versions as $version) {
	    	$r[$version] = array(
	    		'release_date' => $releaseDates[$version],
	    		'days_since_last_release' => $dateDiff[$i]  // "development_time_taken / days cost"
	    	);
	    	$i++;
	    }

	    // remove the date from "In Development Sice", because its not a release date and just "forward calculation".
		$r['next_version_in_development_since']['release_date'] = '';

		$this->stats['release_dates_and_development_time'] = $r;
	}

	function calculate_project_age()
	{
		$startDate = '15.11.2011';	
		$this->stats['project_started_date'] 	= DateTimeHelper::formatDate($startDate, 'd.m.Y', 'F d, Y');
		$this->stats['project_age'] 		= DateTimeHelper::formatDateDiff($startDate);
		$this->stats['project_birthday'] 		= DateTimeHelper::getNextBirthday($startDate, 'F d, Y');
		$this->stats['project_daysto_birthday'] = ltrim(DateTimeHelper::getDaysBetweenDates(date('r'), $this->stats['project_birthday']),'+');
		$this->stats['project_age_at_birthday'] = DateTimeHelper::getYearsBetweenDates($startDate, $this->stats['project_birthday']);
	}

	/**
	 * ArrayAccess
	 */
	function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->stats[] = $value;
        } else {
            $this->stats[$offset] = $value;
        }
    }
    function offsetExists($offset) {
        return isset($this->stats[$offset]);
    }
    function offsetUnset($offset) {
        unset($this->stats[$offset]);
    }
	function offsetGet($offset) {
        return isset($this->stats[$offset]) ? $this->stats[$offset] : null;
    }
}

class DateTimeHelper
{
	public static function formatDate($dateString, $fromFormat = 'Y-m-d', $toFormat = 'd-m-Y')
	{
		return date_format(date_create_from_format($fromFormat, $dateString), $toFormat);
	}

	public static function formatGithubDate($githubDate)
    {
    	$date = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $githubDate);
    	return $date->format('Y-m-d');
    }

	public static function usort_helper_sort_by_utc_time($a, $b)
	{
	    $adate = date_create_from_format("Y-m-d", $a);
	    $bdate = date_create_from_format("Y-m-d", $b);
	    if ($adate === $bdate) { return 0; }
	    return ($adate < $bdate) ? -1 : 1;
	}

	public static function getInterval($date1, $date2, $intervalFormat = null)
	{
		$datetime1 = new DateTime($date1);
		$datetime2 = new DateTime($date2);
		$interval = $datetime1->diff($datetime2);
		return $interval->format($intervalFormat);
	}

	public static function getDaysBetweenDates($date1, $date2)
	{
		return self::getInterval($date1, $date2, '%R%a days');
	}

	public static function getYearsBetweenDates($date1, $date2)
	{
		return self::getInterval($date1, $date2, '%y years');		
	}

	/** 
	 * Format a date difference interval.
	 * Uses the two biggest interval parts automatically. 
	 * 
	 * @param DateTime $start 
	 * @param DateTime|null $end 
	 * @return string 
	 */ 
	public static function formatDateDiff($start, $end = null)
	{ 
	    if(!($start instanceof DateTime)) { 
	        $start = new DateTime($start); 
	    } 
	    
	    if($end === null) { 
	        $end = new DateTime(); 
	    } 
	    
	    if(!($end instanceof DateTime)) { 
	        $end = new DateTime($start); 
	    } 
	    
	    $interval = $end->diff($start); 
	    $usePlural = function($nb,$str) {
	    	return $nb > 1 ? $str . 's' : $str;
	    };
	    
	    $format = array(); 
	    if($interval->y !== 0) { 
	        $format[] = '%y ' . $usePlural($interval->y, 'year'); 
	    } 
	    if($interval->m !== 0) { 
	        $format[] = '%m ' . $usePlural($interval->m, 'month'); 
	    } 
	    if($interval->d !== 0) { 
	        $format[] = '%d ' . $usePlural($interval->d, 'day'); 
	    } 
	    if($interval->h !== 0) { 
	        $format[] = '%h ' . $usePlural($interval->h, 'hour'); 
	    } 
	    if($interval->i !== 0) { 
	        $format[] = '%i ' . $usePlural($interval->i, 'minute'); 
	    } 
	    if($interval->s !== 0) { 
	        if(!count($format)) { 
	            return 'less than a minute ago'; 
	        } else { 
	            $format[] = '%s ' . $usePlural($interval->s, 'second'); 
	        } 
	    } 
	    
	    // use the two biggest parts 
	    if(count($format) > 1) { 
	        $format = array_shift($format) . ' and ' . array_shift($format); 
	    } else { 
	        $format = array_pop($format); 
	    } 
	    
	    return $interval->format($format); 
	} 

	/**
	 * Get next birthday.
     *
     * @param birthday
     * @param DateTime format, e.g. 'd.m.Y'. Returns DateTime object, when false.
     * @return string|object Formatted date or DateTime object.
	 */
	public static function getNextBirthday($birthday, $format = 'Y-m-d')
	{
	    $date = new DateTime($birthday);
	    $date->modify('+' . date('Y') - $date->format('Y') . ' years');
	    if($date < new DateTime()) {
	        $date->modify('+1 year');
	    }
	    if($format === false) {
	    	return $date;
	    }	    
	    return $date->format($format);
	}
}

class GithubReleaseStatsHelpers
{
	static function fix_version_name($tag_name)
	{
	    return ($tag_name === '0.7.0' || $tag_name === '0.2.0') ? ('v'. $tag_name) : $tag_name;
	}
}

class StatsTable
{
	static function render($table_id, $array)
	{
		$html = '<table id="' . $table_id . '" class="table table-bordered table-condensed table-hover">';
		//$html .= '<caption>'.ucwords(str_replace('-', ' ', $table_id)).'</caption>';
		$html .= '<thead><tr>';
		foreach($array as $key => $value) {
			$html .= '<th scope="col">' . $key . '</th>';
		}
		$html .= '</tr></thead>';

	    $html .= '<tbody><tr>';
	    foreach($array as $key => $value) {
	        $html .= '<td>' . $value . '</td>';
	    }
	    $html .= '</tr></tbody>';

		$html .= '</table>';

		return $html;
	}

	static function renderMatrix($table_id, $array)
	{
		$php_versions = array_keys($array['v0.8.6']);

		$html = '<table id="' . $table_id . '" class="table table-bordered table-hover table-condensed">';

		$html .= '<thead><tr><th>Installer Version</th>';#<th>Total Downloads</th>
		foreach($php_versions as $phpversion) {
			$html .= '<th>' . $phpversion . '</th>';
		}
		$html .= '</tr></thead>';
		$html .= '<tbody>';

		foreach($array as $key => $values)
		{
			// possible array_sum($values) to display total downloads per version
			$html .= '<tr>';
			$html .= '<th>' . $key . '</th>';
			foreach($values as $php => $dls) {
				$html .= '<td>' . $dls . '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';

		return $html;
	}
}

class HighchartHelper
{
	static function render_json_for_phpversions_piechart($dls_per_phpversion)
	{
		$html = '';
		foreach($dls_per_phpversion as $version => $downloads) {
			$html .= "{name: \"$version\", y: $downloads},";
		}
		return $html . PHP_EOL;
	}
}

/**
 * ---------- cut here ----------
 */
define('RENDER_WPNXM_PAGE_TITLE', 'WPN-XM - Project Statistics');
define('RENDER_WPNXM_HEADER_LOGO', true);
require __DIR__ . '/view/header.php';
require __DIR__ . '/view/topnav.php';
$s = new GithubReleaseStats();
?>

<div class="panel panel-default">
	<div class="panel-heading"><h4>Project Statistics</h4></div>
	<table class="table table-bordered table-condensed">
	    <tr>
	        <td>The project was started on <?=$s['project_started_date']?>. That was <?=$s['project_age']?> ago.</td>
	    </tr>
	    <tr>
	    	<td>
    		<?php if($s['project_daysto_birthday'] === '0 days') { ?>
				<h3 style="text-align:center">Hooray! It's my birthday. I just turned <?=$s['project_age_at_birthday']?>.<img src="images/birthday.jpg"/></h3>
    		<?php } else { ?>
        		The project will turn <?=$s['project_age_at_birthday']?> old on <?=$s['project_birthday']?>. That's in <?=$s['project_daysto_birthday']?>.
        	<?php } ?> 
	        </td>      
	    </tr>
	</table>
</div>

<div class="panel panel-default">
	<div class="panel-heading"><h4>Releases</h4></div>
	<table class="table table-bordered table-condensed">
		<tr>
			<td>Number of Releases</td>
			<td><?=$s['total_releases']?></td>
		</tr>
		<tr>
			<td>Number of Released Installers</td>
			<td><?=$s['total_installers_released']?></td>
		</tr>
		<tr>
			<td>Number of Installers Released by Installer Version</td>
			<td><?=StatsTable::render('installers-released-per-version-datatable', $s['installers_released_per_version'])?></td>
		</tr>
		<tr>
			<td>Total number of Downloads</td>
			<td><strong><?=$s['total_downloads']?></strong></td>
		</tr>
		<tr>
			<td>Total Downloads by PHP Version</td>
			<td><?=StatsTable::render('total-downloads-per-phpversion', $s['downloads_by_php_version'])?></td>
		</tr>
		<tr>
			<td>Total Downloads by Installer Version</td>
			<td><?=StatsTable::render('total-downloads-by-installer-version', $s['downloads_by_installer_version'])?></td>
		</tr>
		<tr>
			<td>Number of Installer Downloads by PHP Version</td>
			<td><?=StatsTable::renderMatrix('downloads-by-installer-and-php-version', $s['downloads_per_installer_version']);?></td>
		</tr>
		<tr>
			<td>Release Dates and Development times</td>
			<td><?=StatsTable::renderMatrix('release-dates-and-dev-times', $s['release_dates_and_development_time']);?></td>
		</tr>
	</table>
</div> <!-- close panel -->

<div class="panel panel-default">
  <div class="panel-body">
    <div id="highchart-container-downloads-by-installer-and-php-version" style="min-width: 300px; height: 500px; max-width: 800px; margin: 0 auto"></div>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-body">
    <div id="highchart-container-total-downloads" style="min-width: 300px; height: 500px; max-width: 800px; margin: 0 auto"></div>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-body">
    <div id="highchart-container-downloads-by-php-version" style="min-width: 300px; height: 500px; max-width: 800px; margin: 0 auto"></div>
  </div>
</div>

<?php
require __DIR__ . '/view/footer_scripts.php';
require __DIR__ . '/view/highchart_scripts.php';
?>

</div><!-- container -->
</div><!-- col-md-12 -->
</div><!-- row -->
</div><!-- col-md-10 -->
</body>
</html>