<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

include_once  __DIR__ . '/Registry.php';

class InstallerRegistryComparator
{
    public $installerRegistryA;
    public $installerRegistryB;
    public $result;

    public function compare()
    {
        $result = [];

        $this->installerRegistryA = Registry::reindexArrayWithSoftwareNameAsKey($this->installerRegistryA);
        $this->installerRegistryB = Registry::reindexArrayWithSoftwareNameAsKey($this->installerRegistryB);
        $this->installerRegistryA = $this->modifyArraySoftwareNameBitsizeAsValue($this->installerRegistryA);
        $this->installerRegistryB = $this->modifyArraySoftwareNameBitsizeAsValue($this->installerRegistryB);

        foreach($this->installerRegistryA as $nameA => $data)
        {
            $nameA = Registry::updateDeprecatedSoftwareRegistryKeyNames($nameA);
            if(array_key_exists($nameA, $result)) {
                continue;
            }
            $versionA = $data['version'];
            if(!array_key_exists($nameA, $this->installerRegistryB) && !array_key_exists($nameA, $result)) {
                $result[$nameA] = array('versionA' => $versionA, 'versionB' => '', 'diffState' => 'removed');
                continue;
            }
            $versionB = $this->installerRegistryB[$nameA]['version'];
            $result[$nameA] = $this->versionCompare($versionA, $versionB);
        }
        unset($nameA, $nameB, $versionA, $versionB);

        foreach($this->installerRegistryB as $nameB => $data)
        {
            $nameB = Registry::updateDeprecatedSoftwareRegistryKeyNames($nameB);
            if(array_key_exists($nameB, $result)) {
                continue;
            }
            $versionB = $data['version'];
            if(!array_key_exists($nameB, $this->installerRegistryA)) {
                $result[$nameB] = array('versionA' => '', 'versionB' => $versionB, 'diffState' => 'added');
                continue;
            }
            $versionA = $this->installerRegistryA[$nameB]['version'];
            $result[$nameB] = $this->versionCompare($versionA, $versionB);
        }
        unset($nameA, $nameB, $versionA, $versionB);

        ksort($result);

        $this->result = $result;
    }

    function versionCompare($versionA, $versionB)
    {
        $result = ['versionA' => $versionA, 'versionB' => $versionB];
        $versionCompareResult = version_compare($versionA, $versionB);
        if($versionCompareResult === -1) { // a lower b
            $result += ['diffState' => 'higher'];
        }
        elseif($versionCompareResult === 0) { // equal
            $result += ['diffState' => 'equal'];
        }
        elseif($versionCompareResult === 1) { // b lower a
            $result += ['diffState' => 'lower'];
        }
        return $result;
    }

    function modifyArraySoftwareNameBitsizeAsValue($array)
    {
        $out = [];
        foreach ($array as $name => $version)
        {
            if(strpos($name, '-x86') !== false ) {
                $key = str_replace('-x86', '', $name);
                $out[$key] = ['realname' => $name, 'bitsize' => 'x86', 'version' => $version];
                continue;
            }

            if(strpos($name, '-x64') !== false ) {
                $key = str_replace('-x64', '', $name);
                $out[$key] = ['realname' => $name, 'bitsize' => 'x64', 'version' => $version];
                continue;
            }

            $out[$name] = ['version' => $version];
        }
        return $out;
    }


}
