<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * Header redirection script
 * -------------------------
 * The script provides a header response to a software and version request
 * in form of a header redirection to the download url.
 */

include_once __DIR__ . '/src/Registry.php';

class Request
{
    public $software;
    public $version;
    public $phpVersion;
    public $bitsize;

    private $defaultPHPversion = '5.6';
    private $defaultBitsize    = 'x86';

    public function __construct()
    {
        if (defined('PHPUNIT_TESTSUITE')) {
            $this->processGetDuringTesting();
        } else {
            $this->processGet();
        }
    }

    /**
     *
     */
    public function processGet()
    {
        // $_GET['s'] = software component
        $this->software = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);

        // $_GET['v'] = version
        $version = filter_input(INPUT_GET, 'v', FILTER_SANITIZE_STRING);
        // unset any latest version requests, because we return "latest version" by default
        $this->version = ($version == 'latest') ? null : $version;

        // $_GET['p'] = php version, default version is php 5.6
        if(isset($_GET['p']) && (substr_count($_GET['p'], '.') >= 1)) {
        	$this->phpVersion = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_STRING);
        } else {
        	$this->phpVersion = $this->defaultPHPversion;
        }

        // $_GET['bitsize'] = php bitsize for extensions, default version is x86
        if (isset($_GET['bitsize']) && ($_GET['bitsize'] == 'x86' || $_GET['bitsize'] == 'x64')) {
			$this->bitsize = filter_input(INPUT_GET, 'bitsize', FILTER_SANITIZE_STRING);
        } else {
            $this->bitsize = $this->defaultBitsize;
        }
    }

    /**
     * Same as above, but using filter_var() instead of filter_input(),
     * in order to set and modify the $_GET superglobal during testing.
     */
    public function processGetDuringTesting()
    {
        // $_GET['s'] = software component
        $this->software = filter_var($_GET['s'], FILTER_SANITIZE_STRING);

        // $_GET['v'] = version
        if(isset($_GET['v'])) {
            $version = filter_var($_GET['v'], FILTER_SANITIZE_STRING);
            // unset any latest version requests, because we return "latest version" by default
            $this->version = ($version == 'latest') ? null : $version;
        }

        // $_GET['p'] = php version, default version is php 5.5
        if(isset($_GET['p']) && (substr_count($_GET['p'], '.') >= 1)) {
        	$this->phpVersion = filter_var($_GET['p'], FILTER_SANITIZE_STRING);
        } else {
        	$this->phpVersion = $this->defaultPHPversion;
        }

        // $_GET['bitsize'] = php bitsize for extensions, either "x86" or "x64", default version is "x86"
        if(isset($_GET['bitsize']) && ($_GET['bitsize'] == 'x86' || $_GET['bitsize'] == 'x64')) {
			$this->bitsize = filter_var($_GET['bitsize'], FILTER_SANITIZE_STRING);
        } else {
            $this->bitsize = $this->defaultBitsize;
        }
    }

    function getReferer()
    {
        if (defined('PHPUNIT_TESTSUITE')) {
            return;
        }

        /**
         * The referer is mixed: its either a browser or webinstallation wizard.
         * Our webinstallers identify themself with the following "User Agent" Header:
         * "WPN-XM Server Stack - Webinstaller - Version".
         * Only the version is stats relevant, let's ditch the rest.
         */
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if(false !== strpos($_SERVER['HTTP_USER_AGENT'], 'WPN-XM Server Stack - Webinstaller - ')) {
                return substr($_SERVER['HTTP_USER_AGENT'], 37);
            }
            return $_SERVER['HTTP_USER_AGENT'];
        }

        return 'HTTP_USER_AGENT not set';
    }

    public function getDefaultPHPVersion()
    {
        return $this->defaultPHPversion;
    }

    public function getDefaultBitsize()
    {
        return $this->defaultBitsize;
    }

}

class Response
{
    public $url    = '';
    public $header = '';

    /**
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     *
     * Redirect to the target url.
     *
     * @param null|string $url
     */
    public function redirect($url = null)
    {
        if (isset($url)) {
            $this->url = $url;
        }

        $this->header = 'Location: ' . $this->url;

        if (defined('PHPUNIT_TESTSUITE')) {
            #echo $this->header;
            return;
        } else {
            header($this->header);
            exit;
        }
    }

    public function send()
    {
        if (defined('PHPUNIT_TESTSUITE')) {
            #echo $this->header;
            return;
        } else {
            header($this->header);
            exit;
        }
    }
}

/**
 * Find component in registry.
 * Redirect to download URL.
 */
class Component
{
    /* @var obj Registry */
    public $registry;
    public $request;
    public $response;
    public $database;

    /**
     * @param $request
     * @param $response
     * @param $registry
     * @param $database
     */
    public function __construct($request, $response, $registry, $database)
    {
        $this->response = $response;
        $this->request  = $request;
        $this->registry = $registry;
        $this->database = $database;
    }

    public function redirectTo()
    {
        // re-assign vars to shorter ones
        $software   = $this->request->software;
        $version    = $this->request->version;
        $phpVersion = $this->request->phpVersion;
        $bitsize    = $this->request->bitsize;

        $software = Registry::updateDeprecatedSoftwareRegistryKeyNames($software);

        if (!defined('PHPUNIT_TESTSUITE')) {
            if (!$this->registry->softwareExists($software)) {
                $this->response->setHeader('HTTP/1.0 404 Not Found');
                $this->response->send();
            }
        }

        /*
         * If the software component is a PHP extension, then
         * we have to take the "phpVersion" and "bitsize" into account when fetching the URL.
         * The "version" to "url" relationship has two levels more:
         * "version" -> "bizsize" -> "phpVersion" -> "url".
         */

        if ($this->registry->softwareIsPHPExtension($software)) {

            // return download URL for specific version request,
            // e.g. $registry['phpext_xdebug']['1.2.1']['x86']['5.5']
            if ($this->registry->versionExists($software, $version) &&
                $this->registry->extensionHasPhpVersion($software, $version, $bitsize, $phpVersion))
            {
                $url = $this->registry[$software][$version][$bitsize][$phpVersion];

                $this->trackDownloadEvent($url, $software, $version, $bitsize, $phpVersion);
                $this->response->redirect($url);
            }

            // the specific version does not exist.
            // return latest version with default PHP version and default bitsize instead,
            // e.g. $registry['phpext_xdebug']['latest']['url']['x86']['5.5']
            elseif ($this->registry->extensionLatestVersionHasBitsize($software, $bitsize) &&
                    $this->registry->extensionLatestVersionHasPhpVersion($software, $bitsize, $phpVersion))
            {
                $url     = $this->registry[$software]['latest']['url'][$bitsize][$phpVersion];
                $version = $this->registry[$software]['latest']['version'];

                $this->trackDownloadEvent($url, $software, $version, $bitsize, $phpVersion);
                $this->response->redirect($url);
            }
            elseif ($software === 'phpext_phalcon')
            {
                // special handling for phpext_phalcon, because it has a PHP "patch level" version constraint.
                // (while all other PHP extensions have only a "major.minor" version constraint.)
                if($this->registry->extensionLatestVersionHasBitsize($software, $bitsize) &&
                    $this->registry->extensionLatestVersionHasPhpVersion($software, $bitsize, $phpVersion)) {
                    $version = $this->registry->getLatestVersion($software);
                } else {

                    $version = $this->registry->findLatestVersionForBitsize($software, $bitsize, $phpVersion);
                }

                $phpVersion = $this->registry->getPhpVersionInRange($software, $version, $bitsize, $phpVersion);
                $url        = $this->registry[$software][$version][$bitsize][$phpVersion];

                $this->trackDownloadEvent($url, $software, $version, $bitsize, $phpVersion);
                $this->response->redirect($url);
            }

            // if the latest version doesn't have an entry for this, e.g. "phpext_wincache"
            elseif(empty($version)) {
                    $versions = $this->registry->getVersions($software);
                    $versions = array_reverse($versions); // latest version first
                    foreach($versions as $_version => $data) {
                        if(isset($data[$bitsize])) {
                            if($this->registry->extensionHasPhpVersion($software, $_version, $bitsize, $phpVersion)) {
                                $version = $_version;
                                break;
                            }
                        }
                    }
                    if( $this->registry->versionExists($software, $version) &&
                        $this->registry->bitsizeExists($software, $version, $bitsize)) {
                        $url = $this->registry[$software][$version][$bitsize][$phpVersion];
                        $this->trackDownloadEvent($url, $software, $version, $bitsize, $phpVersion);
                        $this->response->redirect($url);
                    }
            }
            else {
                // fall-through to "not found"
            }
        }
        else
        {
            /*
             * These are simple "version" to "url" relationships.
             * There are no other constrains, like "phpVersion" or "bitsize" to consider.
             */

            if ($this->registry->versionExists($software, $version))
            {
                // return download url for specific version, e.g. $registry['nginx']['1.2.1']
                $url     = $this->registry[$software][$version];
                $bitsize = $this->registry->getBitsize($software);
                $this->trackDownloadEvent($url, $software, $version, $bitsize);
                $this->response->redirect($url);
            }
            elseif (in_array($software, ['php', 'php-x64', 'php-qa', 'php-qa-x64']))
            {
                // special handling for PHP, because we have to
                // return the latest patch version (x.y.*) of a "major.minor" PHP version (x.y)
                $versions = $this->registry[$software];
                $version  = $this->registry->getLatestVersionOfRange($versions, $phpVersion . '.0', $phpVersion . '.99');

                $url      = $this->registry[$software][$version];
                $this->trackDownloadEvent($url, $software, $version, $bitsize);
                $this->response->redirect($url);
            }
            else
            {
                // return latest version url, e.g. $registry['nginx']['latest']['url']
                $url     = $this->registry[$software]['latest']['url'];
                $version = $this->registry[$software]['latest']['version'];
                $bitsize = $this->registry->getBitsize($software);
                $this->trackDownloadEvent($url, $software, $version, $bitsize);
                $this->response->redirect($url);
            }
        }

        // software does not exist, download will fail.
        $this->response->setHeader('HTTP/1.0 404 Not Found');
        $this->response->send();
    }

    public function trackDownloadEvent($url, $component, $version, $bitsize = '', $phpVersion = '')
    {
        $this->database->insertDownload($url, $component, $version, $bitsize, $phpVersion, $this->request->getReferer());
    }
}

/** ------------------- */
if (!defined('PHPUNIT_TESTSUITE'))
{
    $request  = new Request();
    $response = new Response();
    $registry = new Registry();
    require_once __DIR__ . '/src/Database.php';
    $database = new Database();

    $component = new Component($request, $response, $registry, $database);
    $component->redirectTo();
}
