<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

class InstallerRegistryArrayHelper
{
    /**
     * Sort Wizard registries from low to high version number,
     * with -next- registries at the bottom.
     *
     * @param $wizardRegistries
     * @return array
     */
    function sortWizardRegistries($wizardRegistries)
    {
        uasort($wizardRegistries, 'self::versionCompare');

        $cnt = $this->countNextRegistries($wizardRegistries);

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
    public static function versionCompare($a, $b)
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
}