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

// Installer Registries
$installerRegistries = getInstallerRegistries($registry);

include __DIR__ . '/view/header.php';
?>
    <body style="padding-top: 0px;">
        <div class="page-header">
          <h1>Version Comparison Matrix</h1>
        </div>
        <p>
            The table is a "version comparison matrix" for all software components shipped by all our installation wizards.
            This allows a user to quickly notice, if a certain software is packaged and which version.
        </p>
        <table id="version-matrix" class="table table-condensed table-bordered table-version-matrix"
               style="width: auto !important; padding: 0px; vertical-align: middle; background-color: #fefefe;">
            <thead>
                <tr>
                    <th>Software Components (<?php echo count($registry); ?>)</th>
                    <?php echo renderTableHeader($installerRegistries); ?>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($registry as $software => $data) {
                echo '<tr><td>' . $software . '</td>' . renderTableCells($installerRegistries, $software) . '</tr>';
            }
            ?>
            </tbody>
        </table>
    </body>
</html>

<?php

/*
 * Installation Wizard Registries
 * - fetch the registry files
 * - split filenames to get version constraints (e.g. version, lite, php5.4, w32, w64)
 * - restructure the arrays for sorting and better iteration
 */
function getInstallerRegistries()
{
    $wizardFiles = InstallerRegistries::getAll();

    if (empty($wizardFiles) === true) {
        exit('No JSON registries found.');
    }

    $wizardRegistries = [];
    foreach ($wizardFiles as $file) {
        $name = basename($file, '.json');

        $parts                                  = getPartsOfInstallerFilename($name);
        $parts                                  = dropNumericKeys($parts);
        $wizardRegistries[$name]['constraints'] = $parts;
        unset($parts);

        // load registry
        $registryContent                     = issetOrDefault(json_decode(file_get_contents($file), true), []);
        $wizardRegistries[$name]['registry'] = fixArraySoftwareAsKey($registryContent);
    }

    return sortWizardRegistries($wizardRegistries);
}

class InstallerRegistries
{
    public static function getAll()
    {
        $files = self::recursiveFind(__DIR__ . '/registry/installer', '#^.+\.json#i');

        if (empty($files)) {
            throw new \Exception('No JSON registries found.');
        }

        return $files;
    }

    private static function recursiveFind($folder, $regexp)
    {
        $dir = new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir);
        $files = new \RegexIterator($iterator, $regexp, \RegexIterator::GET_MATCH);

        $fileList = array();
        foreach($files as $file) {
            $fileList[] = $file[0];
        }

        return $fileList;
    }
}

function getPartsOfInstallerFilename($name)
{
    if (substr_count($name, '-') === 3) {
        preg_match('/(?<installer>.*)-(?<version>.*)-(?<phpversion>.*)-(?<bitsize>.*)/i', $name, $parts);
        return $parts;
    }

    if (substr_count($name, '-') === 2) {
        preg_match('/(?<installer>.*)-(?<version>.*)-(?<bitsize>.*)/i', $name, $parts);
        return $parts;
    }
}

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
    return array_merge($wizardRegistries, $nextRegistries);
}

/**
 * @param $registries
 * @return int
 */
function countNextRegistries($registries)
{
    $i = 0;
    foreach ($registries as $registry) {
        if ($registry['constraints']['version'] === 'next') {
            $i += 1;
        }
    }
    return $i;
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
    $html = '';
    foreach ($wizardRegistries as $wizardName => $wizardRegistry) {
        $html .= '<th>' . $wizardName . '</th>';
    }
    return $html;
}

/**
 * @param array $wizardRegistries
 * @param $software
 * @return string
 */
function renderTableCells(array $wizardRegistries, $software)
{
    $html = '';
    foreach ($wizardRegistries as $wizardName => $wizardRegistry) {
        // normal versions
        if (isset($wizardRegistry['registry'][$software]) === true) {
            $html .= '<td class="version-number">' . $wizardRegistry['registry'][$software] . '</td>';
        } else {
            $html .= '<td>&nbsp;</td>';
        }
    }

    return $html;
}
