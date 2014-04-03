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
 * The script renders a "version comparison matrix" for all available installers.
 * This allows a user to quickly notice, if a certain software is packaged and which version.
 */

// WPNXM Software Registry
$registry  = include __DIR__ . '\registry\wpnxm-software-registry.php';

// Installation Wizard Registries
$wizardFiles = glob(__DIR__ . '\registry\*.json');
$wizardRegistries = array();
foreach($wizardFiles as $file) {
	$name = str_replace('wpnxm-software-registry-', '', basename($file, '.json'));
	$wizardRegistries[$name] = fixArraySoftwareAsKey(json_decode(file_get_contents($file), true));
}

function fixArraySoftwareAsKey($array) {
	$out = array();
	foreach($array as $key => $values) {
		$software = $values[0];
		unset($values[0]);
		$out[$software] = $values[3];
	}
	return $out;
}

function getVersion($registry, $software)
{
	if(isset($registry[$software]) === true) {
		return '<span class="badge badge-info">' . $registry[$software] . '</span>';
	}
	return '&nbsp;';
}

function renderCell($registry, $software)
{
	return '<td>' . isVersion($registry, $software) . '</td>';
}

function renderTableHeader($wizardRegistries)
{
	$header = '';
	foreach($wizardRegistries as $wizardName => $wizardRegistry) {
		$header .= '<td>' . $wizardName. '</td>';
	}
	return $header;
}

function renderTableCells($wizardRegistries, $software)
{
	$cells = '';
	foreach($wizardRegistries as $wizardName => $wizardRegistry) {
		$cells .= '<td>' . getVersion($wizardRegistry, $software) . '</td>';
	}
	return $cells;
}
?>

<table class="table table-condensed table-bordered">
<thead>
	<th>Software</th> <?php echo renderTableHeader($wizardRegistries); ?>
</thead>
<?php
foreach($registry as $software => $data)
{
	echo '<tr><td>' . $software . '</td>' . renderTableCells($wizardRegistries, $software) . '</tr>';
}
?>
</table>