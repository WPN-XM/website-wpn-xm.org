<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens A. Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * GetLocalInstallers
 * The second part of the installer listing are our early releases .
 * From the time before we used Github Releases.
 * The installer were manually uploaded to the /downloads folder on the server.
 */
class DownloadsGetLocalInstallers
{
    /**
     * Get details for all available files
     */
    public function get()
    {
        // ----- Gather details for all available files

        $downloads_folder = dirname(__DIR__) . '/downloads/';

        if (!is_dir($downloads_folder)) {
            throw new Exception('The downloads directory is missing.');
        }

        //chdir($downloads_folder);

        $downloads = [];
        $details   = [];

        # get all installer executables
        $installerExecutables = glob($downloads_folder . '*.exe');

        foreach($installerExecutables as $filename)
        {
            // file
            $file            = basename($filename);
            $details['file'] = $file;

            // size
            $bytes           = filesize($filename);
            $details['size'] = LocalInstallersHelper::formatFilesize($bytes);

            // version, installer, phpversion, platform
            $details = array_merge($details, LocalInstallersHelper::getDetailsFromInstallerFilename($file));

            // md5 & sha1 checksums
            $details['md5']  = LocalInstallersHelper::md5_checksum($filename);
            $details['sha1'] = LocalInstallersHelper::sha1_checksum($filename);

            // download URL and link
            $details['download_url'] = 'http://wpn-xm.org/downloads/' . $file;
            $details['link']         = '<a href="' . $details['download_url'] . '">' . $file . '</a>';

            // release notes, e.g. https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.5.3
            $details['release_notes'] = '<a class="btn btn-large btn-info" '
                . 'href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v' . $details['version'] . '">Release Notes</a>';

            // put "v" in front to get a properly versionized tag, starting from version "0.8.0"
            $version = (version_compare($details['version'], '0.8.0')) ? $details['version'] : 'v' . $details['version'];

            // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/0.5.2/changelog.txt
            $details['changelog'] = '<a class="btn btn-large btn-info" '
                . 'href="https://github.com/WPN-XM/WPN-XM/blob/' . $version . '/changelog.txt">Changelog</a>';

            // component list with version numbers
            // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
            $details['github_tag'] = '<a class="btn btn-large btn-info" '
                . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $version . '">Github Tag</a>';

            // date
            $details['date'] = date('d.m.Y', filemtime($filename));

            // downloads
            $details['downloads'] = 0;

            // add download details to downloads array
            $downloads[] = $details;

            // reset array for next loop
            $details = [];
        }

        // order downloads - latest version first
        arsort($downloads);
        // reindex
        array_splice($downloads, 0, 0);

        return $downloads;
    }

    // ----- Gather some general data for the downloads list
    function getReleasesAndVersions($downloads)
    {
        $releases = [];

        // collect "release" details for a version
        $versions = [];

        foreach ($downloads as $download)
        {
            $v = $download['version'];
            if (!isset($versions[$v])) {
                $versions[$v]['version']       = $v;
                $versions[$v]['downloads']     = $download['downloads'];
                $versions[$v]['date']          = $download['date'];
                $versions[$v]['release_notes'] = $download['release_notes'];
                $versions[$v]['changelog']     = $download['changelog'];
                $versions[$v]['github_tag']    = $download['github_tag'];
            }
            $versions[$v]['downloads'] += $download['downloads'];
        }
        krsort($versions); // sort by key: version numbers from highest to lowest

        $releases['releases'] = $versions;

        // add "versions", listing "all available version"
        $releases['versions'] = array_keys($versions);

        // reassign for easier access: "latest_version" and "release date" of the latest version of WPN-XM
        $releases['latest_version']              = $downloads[0]['version'];
        $releases['latest_version_release_date'] = $downloads[0]['date'];

        return $releases;
    }
}

class LocalInstallersHelper
{
    /**
     * Formats filesize in human readable way.
     *
     * @param file $file
     *
     * @return string Formatted Filesize.
     */
    static function formatFilesize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes === 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }

    /**
     * Builds a md5 checksum for a file and writes it to a file for later reuse.
     *
     * @param string $filename
     *
     * @return string md5 file checksum
     */
    static function md5_checksum($filename)
    {
        $md5 = '';

        $path = pathinfo($filename);
        $dir  = dirname(__DIR__) . '/downloads/checksums/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $md5ChecksumFile = $dir . $path['filename'] . '.md5';

        if (is_file($md5ChecksumFile)) {
            return file_get_contents($md5ChecksumFile);
        } else {
            $md5 = md5_file($filename);
            file_put_contents($md5ChecksumFile, $md5);
        }

        return $md5;
    }

    /**
     * Builds a sha1 checksum for a file and writes it to a file for later reuse.
     *
     * @param string $filename
     *
     * @return string sha1 file checksum
     */
    static function sha1_checksum($filename)
    {
        $sha1 = '';

        $path = pathinfo($filename);
        $dir  = dirname(__DIR__) . '/downloads/checksums/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $sha1ChecksumFile = $dir . $path['filename'] . '.sha1';

        if (is_file($sha1ChecksumFile)) {
            $sha1 = file_get_contents($sha1ChecksumFile);
        } else {
            $sha1 = sha1_file($filename);
            file_put_contents($sha1ChecksumFile, $sha1);
        }

        return $sha1;
    }

    static function getDetailsFromInstallerFilename($installer_filename)
    {
        $details = [];

        // WPNXM-0.5.4-BigPack-Setup - without PHP version constraint
        if (substr_count($installer_filename, '-') === 3) {
            if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup.exe/', $installer_filename, $matches)) {
                $details['version']   = $matches['version'];
                $details['installer'] = $matches['installer'];
                $details['platform']  = 'w32';
            }
        }

        // WPNXM-0.5.4-BigPack-Setup-w32
        if (substr_count($installer_filename, '-') === 4) {
            if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup-(?<bitsize>.*).exe/', $installer_filename, $matches)) {
                $details['version']   = $matches['version'];
                $details['installer'] = $matches['installer'];
                $details['platform']  = $matches['bitsize']; //w32|w64
            }
        }

        // WPNXM-0.8.0-Full-Setup-php54-w32
        if (substr_count($installer_filename, '-') === 5) {
            if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup-(?<phpversion>.*)-(?<bitsize>.*).exe/', $installer_filename, $matches)) {
                $details['version']    = $matches['version'];
                $details['installer']  = $matches['installer'];
                $details['phpversion'] = $matches['phpversion'];
                $details['platform']   = $matches['bitsize']; //w32|w64
            }
        }

        $details['name'] = $installer_filename;

        return $details;
    }
}
