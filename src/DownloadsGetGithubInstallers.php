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
 * The first part of the installer listing are the releases from Github.
 */
class DownloadsGetGithubInstallers
{
    private $isGithubApiRequest = false;

    public function get()
    {
        $downloads = [];

        if($this->isGithubApiRequest) {
            $githubDownloadStatsDatabase = new GithubDownloadStatsDatabase;
        }

        $releases = $this->getGithubReleases();

        foreach ($releases as $release)
        {
            $details = [];

            // skip our first release tag - only commits, no downloads
            if ($release['tag_name'] == '0.2.0') {
                continue;
            }

            unset($release['author']);

            if ($release['prerelease'] === false) {

                $details['name']            = $release['name'];
                $details['date']            = date('d M Y', strtotime($release['created_at']));
                //$details['total_downloads'] = get_total_downloads($release);

                // release notes, e.g. https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.5.3
                $details['release_notes'] = '<a class="btn btn-large btn-info" '
                    . 'href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-' . $release['tag_name'] . '">Release Notes</a>';

                // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/master/CHANGELOG.md#v085---2015-07-12
                $hash = '#' . str_replace('.', '', $release['tag_name']) . '---' . date('Y-m-d', strtotime($release['created_at']));
                $details['changelog'] = '<a class="btn btn-large btn-info" '
                    . 'href="https://github.com/WPN-XM/WPN-XM/blob/master/CHANGELOG.md' . $hash . '">Changelog</a>';

                // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
                $details['github_tag'] = '<a class="btn btn-large btn-info" '
                    . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $release['tag_name'] . '">Github Tag</a>';

                foreach ($release['assets'] as $idx => $asset)
                {
                    unset($asset['uploader'], $asset['url'], $asset['label'], $asset['content_type'], $asset['updated_at']);

                    // version, installer, phpversion, platform
                    $details = array_merge($details, LocalInstallersHelper::getDetailsFromInstallerFilename($asset['name']));

                    $details['download_url'] = $asset['browser_download_url'];
                    $details['link']         = '<a href="' . $details['download_url'] . '">' . $asset['name'] . '</a>';
                    $details['size']         = LocalInstallersHelper::formatFilesize($asset['size']);
                    $details['downloads']    = $asset['download_count'];

                    if($this->isGithubApiRequest) {
                        $githubDownloadStatsDatabase->insertDownload($asset['name'], $asset['download_count']);
                    }

                    // add download details to downloads array
                    $downloads[] = $details;
                }
            }
        }

        return $downloads;
    }

    public function getGithubReleases()
    {
        $cache_file = dirname(__DIR__) . '/downloads/github-releases-cache.json';

        // Use cache file, when not older than 1 day.
        if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 86400))) { // 1 * 24 * 60 *60           
            $data = file_get_contents($cache_file);
        } else {
            // The cache is out-of-date. Load the JSON data from Github.
            $data = self::curlRequest();
            file_put_contents($cache_file, $data, LOCK_EX);

            $this->isGithubApiRequest = true;
        }

        return json_decode($data, true);
    }

    public static function getGithubReleasesTag($release_tag)
    {
        $releases = get_github_releases();

        foreach ($releases as $release) {
            if ($release['tag_name'] === $release_tag) {
                return $release;
            }
        }
    }

    public static function curlRequest()
    {
        $headers[] = 'Accept: application/vnd.github.manifold-preview+json';

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.github.com/repos/wpn-xm/wpn-xm/releases',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_USERAGENT      => 'wpn-xm.org - downloads page',
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
