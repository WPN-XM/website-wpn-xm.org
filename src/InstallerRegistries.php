<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

class InstallerRegistries
{
    public static function getFiles()
    {
        return self::recursiveFind(__DIR__ . '/../registry/installer', '#^.+\.json#i');
    }

    public static function recursiveFind($folder, $regexp)
    {
        $dir = new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir);
        $matches = new \RegexIterator($iterator, $regexp, \RegexIterator::GET_MATCH);

        $files = array();
        foreach($matches as $file) {
            $files[] = $file[0];
        }
        
        if (empty($files)) {
            throw new \Exception('No matches found.');
        }
        
        return $files;
    }
    
    public static function getPartsOfInstallerFilename($name)
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
     * Installation Wizard Registries
     * - fetch the registry files
     * - split filenames to get version constraints (e.g. version, lite, php5.4, w32, w64)
     * - restructure the arrays for sorting and better iteration
     */
    public static function getInstallerRegistries()
    {        
        $arrayHelper = new InstallerRegistryArrayHelper;        
        $wizardRegistries = [];         
        $wizardFiles = self::getFiles();
                
        foreach ($wizardFiles as $file) {
            $name = basename($file, '.json');
            
            $parts = self::getPartsOfInstallerFilename($name);
            $parts = $arrayHelper->dropNumericKeys($parts);
            $wizardRegistries[$name]['constraints'] = $parts;
            unset($parts);
            
            // load registry
            $registryContent = $arrayHelper->issetOrDefault(json_decode(file_get_contents($file), true), []);
            $wizardRegistries[$name]['registry'] = $arrayHelper->fixArraySoftwareAsKey($registryContent);     
        }
        return $arrayHelper->sortWizardRegistries($wizardRegistries);
    }  
    
    public static function getInstallerNames()
    { 
        $registries = [];        
        $files = self::getFiles();                
        foreach ($files as $file) {
            $name = basename($file, '.json');
            $parts = self::getPartsOfInstallerFilename($name);
            $registries[$name]['constraints'] = $parts;
            unset($parts);
        }        
        $arrayHelper = new InstallerRegistryArrayHelper;
        return $arrayHelper->sortWizardRegistries($registries);
    }
    
    public static function getInstallerNamesForVersion($version)
    {   
        $registries = [];        
        $files = self::getFiles();                
        foreach ($files as $file) {
            $name = basename($file, '.json');
            $v = self::getPartsOfInstallerFilename($name)['version'];  
            if($v === $version) {
                $registries[] = $name;
            }            
        }        
        asort($registries, SORT_STRING);            
        return $registries;
    }
    
    public static function getVersions()
    {
        $installerNames = InstallerRegistries::getInstallerNames();                
        $installerNamesWithVersion = array_map(function ($value) {
            return $value['constraints']['version'];
        }, $installerNames);         
        return array_unique(array_values($installerNamesWithVersion));
    }
    
    public static function getVersionFromInstallerName($installerFilename)
    {
        $parts = self::getPartsOfInstallerFilename($installerFilename);
        $version = $parts['version'];    
        return ($version !== 'next') ? 'v'.$version : $version;
    }

    public static function buildInstallerRegistryFilePath($installerFilename)
    {
        $version = self::getVersionFromInstallerName($installerFilename);
        $file = __DIR__ . '/../registry/installer/'.$version.'/'.$installerFilename.'.json';
        return realpath($file);   
    }

    public static function loadRegistry($installerFilename) 
    {
        $registryFilename = self::buildInstallerRegistryFilePath($installerFilename);
        if(!file_exists($registryFilename)) {
            throw new \RuntimeException('Installer Registry File not found.');
        } 
        $json = file_get_contents($registryFilename);
        return json_decode($json, true);
    }
}

