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
$registry = include __DIR__ . '/registry/wpnxm-software-registry.php';

/*
 * Installation Wizard Registries
 * - fetch the registry files
 * - split filenames to get version constraints (e.g. version, lite, php5.4, w32, w64)
 * - restructure the arrays for sorting and better iteration
 */
$wizardFiles = glob(__DIR__ . '/registry/*.json');

if (empty($wizardFiles) === true) {
    exit('No JSON registries found.');
}

$wizardRegistries = [];
foreach ($wizardFiles as $file) {
    $name = basename($file, '.json');

    if (substr_count($name, '-') === 2) {
        preg_match('/(?<installer>.*)-(?<version>.*)-(?<bitsize>.*)/i', $name, $parts);
    }

    if (substr_count($name, '-') === 3) {
        preg_match('/(?<installer>.*)-(?<version>.*)-(?<phpversion>.*)-(?<bitsize>.*)/i', $name, $parts);
    }

    $parts                                  = dropNumericKeys($parts);
    $wizardRegistries[$name]['constraints'] = $parts;
    unset($parts);

    // load registry
    $registryContent                     = issetOrDefault(json_decode(file_get_contents($file), true), []);
    $wizardRegistries[$name]['registry'] = fixArraySoftwareAsKey($registryContent);
}

$wizardRegistries = sortWizardRegistries($wizardRegistries);

/**
 * Sort Wizard registries from low to high version number,
 * with -next- registries at the bottom.
 *
 * @param $wizardRegistries
 * @return array
 */
function sortWizardRegistries($wizardRegistries)
{
    uasort($wizardRegistries, 'versionCompare');

    $cnt = countNextRegistries($wizardRegistries);

    // copy
    $nextRegistries = array_slice($wizardRegistries, 0, $cnt, true);

    // reduce
    for ($i = 1; $i <= $cnt; ++$i) {
        array_shift($wizardRegistries);
    }

    // append (to bottom)
    $wizardRegistries = array_merge($wizardRegistries, $nextRegistries);

    return $wizardRegistries;
}

/**
 * @param $registries
 * @return int
 */
function countNextRegistries($registries)
{
    $cnt = 0;

    foreach ($registries as $registry) {
        if ($registry['constraints']['version'] === 'next') {
            $cnt = $cnt + 1;
        }
    }

    return $cnt;
}

/**
 * @param $a
 * @param $b
 * @return mixed
 */
function versionCompare($a, $b)
{
    return version_compare($a['constraints']['version'], $b['constraints']['version'], '>=');
}

/**
 * @param $array
 * @return array
 */
function fixArraySoftwareAsKey($array)
{
    $out = [];
    foreach ($array as $key => $values) {
        $software = $values[0];
        unset($values[0]);
        $out[$software] = $values[3];
    }
    return $out;
}

/**
 * @param array $array
 * @return array
 */
function dropNumericKeys(array $array)
{
    foreach ($array as $key => $value) {
        if (is_int($key) === true) {
            unset($array[$key]);
        }
    }
    return $array;
}

/**
 * @param $var
 * @param null $defaultValue
 * @return null
 */
function issetOrDefault($var, $defaultValue = null)
{
    return (isset($var) === true) ? $var : $defaultValue;
}

/**
 * @param array $array
 * @param $key
 * @param null $defaultValue
 * @return null
 */
function issetArrayKeyOrDefault(array $array, $key, $defaultValue = null)
{
    return (isset($array[$key]) === true) ? $array[$key] : $defaultValue;
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
 * @param array $wizardRegistries
 * @return string
 */
function renderTableHeader(array $wizardRegistries)
{
    $header = '';
    foreach ($wizardRegistries as $wizardName => $wizardRegistry) {
        $header .= '<th>' . $wizardName . '</th>';
    }
    return $header;
}

/**
 * @param array $wizardRegistries
 * @param $software
 * @return string
 */
function renderTableCells(array $wizardRegistries, $software)
{
    $cells = '';
    foreach ($wizardRegistries as $wizardName => $wizardRegistry) {
        // normal versions
        if (isset($wizardRegistry['registry'][$software]) === true) {
            $cells .= '<td class="version-number">' . $wizardRegistry['registry'][$software] . '</td>';
        } else {
            $cells .= '<td>&nbsp;</td>';
        }
    }

    return $cells;
}

?>

<table id="version-matrix" class="table table-condensed table-bordered table-version-matrix" style="width: auto !important; padding: 0px; vertical-align: middle;">
<thead>
    <tr>
        <th>Software Components (<?php echo count($registry); ?>)</th>
        <?php echo renderTableHeader($wizardRegistries); ?>
    </tr>
</thead>
<?php
foreach ($registry as $software => $data) {
    echo '<tr><td>' . $software . '</td>' . renderTableCells($wizardRegistries, $software) . '</tr>';
}
?>
</table>
