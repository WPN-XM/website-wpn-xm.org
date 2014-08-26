<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2014 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * The script renders a "version comparison matrix" for all available installers.
 * This allows a user to quickly notice, if a certain software is packaged and which version.
 */

// WPNXM Software Registry
$registry  = include __DIR__ . '/registry/wpnxm-software-registry.php';

// Installation Wizard Registries
$wizardFiles = glob(__DIR__ . '/registry/*.json');
if(empty($wizardFiles) === true) {
    exit('No JSON registries found.');
}
$wizardRegistries = array();
foreach($wizardFiles as $file) {
    $name = basename($file, '.json');
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