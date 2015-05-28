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
 * Header redirection script
 * -------------------------
 * The script provides a header response to a software and version request
 * in form of a header redirection to the download url.
 */
class Registry implements ArrayAccess
{
    public $registry;

    public function __construct()
    {
        $this->loadRegistry();
    }

    public function getUrl($software, $version, $bitsize = null, $phpVersion = null)
    {
        return $this->registry[$software][$version][$bitsize][$phpVersion];
    }

    public function loadRegistry()
    {
        // load software components registry
        $this->registry = include __DIR__ . '/registry/wpnxm-software-registry.php';

        // ensure registry array is available
        if (!is_array($this->registry)) {
            header('HTTP/1.0 404 Not Found');
        }

        return $this->registry;
    }

    /**
     * Does the requested software exists in our registry?
     */
    public function softwareExists($software)
    {
        return (!empty($software) && array_key_exists($software, $this->registry));
    }

    /**
     * Does the requested version of a software exist in our registry?
     */
    public function versionExists($software, $version)
    {
        return (!empty($version) && array_key_exists($version, $this->registry[$software]));
    }

    public function extensionHasPhpVersion($software, $version, $bitsize, $phpVersion)
    {
        return array_key_exists($phpVersion, $this->registry[$software][$version][$bitsize]);
    }

    public function extensionLatestVersionHasPHPVersion($software, $bitsize, $phpVersion)
    {
        return array_key_exists($phpVersion, $this->registry[$software]['latest']['url'][$bitsize]);
    }

    /**
     * Is the software a PHP extension?
     */
    public function softwareIsPHPExtension($software)
    {
        return (strpos($software, 'phpext_') !== false);
    }

    /**
     * Return the latest version of an PHP extensions
     * given "name", "major.minor" "phpVersion" and "bitsize".
     *
     * $registry['phpext_xdebug']['1.2.1']['x86']['5.5.*']
     */
    public function getLatestVersion($software)
    {
        $versions = $this->getVersions($software);

        end($versions);

        $latest_version = key($versions);

        return $latest_version;
    }

    /**
     * Returns the version array for a component from the registry.
     */
    public function getVersions($component)
    {
        $component = $this->registry[$component];

        // drop all non-version keys
        unset($component['name'], $component['latest'], $component['website']);

        return $component;
    }

    public function getPhpVersionInRange($software, $version, $bitsize, $phpVersion)
    {
        $array      = $this->registry[$software][$version][$bitsize];
        $phpVersion = $this->getLatestVersionOfRange($array, $phpVersion . '.0', $phpVersion . '.99');

        return $phpVersion;
    }

    /**
     * Returns the latest version of a component inside a min/max version range.
     *
     * Example: fetch the "latest patch version" of a given "major.minor" version (5.4.*).
     * getLatestVersion('php', '5.4.1', '5.4.99') = "5.4.30".
     *
     * @param array Only the versions array for this component from the registry.
     * @param string A version number, setting the minimum (>=).
     * @param string A version number, setting the maximum (<).
     *
     * @return string Returns the latest version of a component given a min max version constraint.
     */
    public function getLatestVersionOfRange($versions, $minConstraint = null, $maxConstraint = null)
    {
        // get rid of (version => url) and use (idx => version)
        $versions = array_keys($versions);

        // reverse array, in order to have the highest version number on top
        $versions = array_reverse($versions);

        // reduce array to values in constraint range
        foreach ($versions as $idx => $version) {

            // fix "5.y" to "5.y.1"
            if (strlen($version) === 3) {
                $version = $version . '.1';
            }

            if (version_compare($version, $minConstraint, '>=') === true && version_compare($version, $maxConstraint, '<') === true) {
                #echo 'Version v' . $version . ' is greater v' . $minConstraint . '(MinConstraint) and smaller v' . $maxConstraint . '(MaxConstraint).<br>';
            } else {
                unset($versions[$idx]);
            }
        }

        // pop off the first element
        $latestVersion = array_shift($versions);

        return $latestVersion;
    }

    /**
     * ArrayAccess for Registry.
     */
    public function offsetSet($offset, $value)
    {
        return true;
    }

    public function offsetExists($offset)
    {
        return isset($this->registry[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->registry[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->registry[$offset]) ? $this->registry[$offset] : null;
    }
}

class Request
{
    public $software;
    public $version;
    public $phpVersion;
    public $bitsize;

    public function __construct()
    {
        if (defined('PHPUNIT_TESTSUITE') === 1) {
            $this->processGetDuringTesting();
        } else {
            $this->processGet();
        }
    }

    public function processGet()
    {
        // $_GET['s'] = software component
        $this->software = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);

        // $_GET['v'] = version
        $version = filter_input(INPUT_GET, 'v', FILTER_SANITIZE_STRING);
        // unset any latest version requests, because we return "latest version" by default
        $this->version = ($version === 'latest') ? null : $version;

        // $_GET['p'] = php version, default version is php 5.5
        $this->phpVersion = ($phpVersion = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_STRING)) ? $phpVersion : '5.5';

        // $_GET['bitsize'] = php bitsize for extensions, default version is x86
        $this->bitsize = ($bitsize = filter_input(INPUT_GET, 'bitsize', FILTER_SANITIZE_STRING)) ? $bitsize : 'x86';
    }

    /**
     * Same as above, but using filter_var() in order to set and modify the $_GET superglobal during testing.
     */
    public function processGetDuringTesting()
    {
        $this->software = filter_var($_GET['s'], FILTER_SANITIZE_STRING);

        if (isset($_GET['v'])) {
            $version       = filter_var($_GET['v'], FILTER_SANITIZE_STRING);
            $this->version = ($version === 'latest') ? null : $version;
        }

        $this->phpVersion = '5.5';
        if (isset($_GET['p'])) {
            $this->phpVersion = ($phpVersion = filter_var($_GET['p'], FILTER_SANITIZE_STRING)) ? $phpVersion : '5.5';
        }

        $this->bitsize = 'x86';
        if (isset($_GET['bitsize'])) {
            $this->bitsize = ($bitsize = filter_var($_GET['bitsize'], FILTER_SANITIZE_STRING)) ? $bitsize : 'x86';
        }
    }
}

class Response
{
    public $url    = '';
    public $header = '';

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Redirect to the target url.
     *
     * @param string $url
     */
    public function redirect($url = null)
    {
        if (isset($url)) {
            $this->url = $url;
        }

        $this->header = 'Location: ' . $this->url;

        if (defined('PHPUNIT_TESTSUITE') === 1) {
            #echo $this->header;
            return;
        } else {
            header($this->header);
            exit;
        }
    }

    public function send()
    {
        if (defined('PHPUNIT_TESTSUITE') === 1) {
            #echo $this->header;
            return;
        } else {
            header($this->header);
            exit;
        }
    }
}

### Script ####

$request  = new Request();
$response = new Response();
$registry = new Registry();

$component = new Component($request, $response, $registry);
$component->redirectTo();

/**
 * Find component in registry.
 * Redirect to download url.
 */
class Component
{
    public $registry;
    public $request;
    public $response;

    public function __construct($request, $response, $registry)
    {
        $this->response = $response;
        $this->request  = $request;
        $this->registry = $registry;
    }

    public function redirectTo()
    {
        // re-assign vars to shorter ones
        $software   = $this->request->software;
        $version    = $this->request->version;
        $phpVersion = $this->request->phpVersion;
        $bitsize    = $this->request->bitsize;

        if (!defined('PHPUNIT_TESTSUITE')) {
            if (!$this->registry->softwareExists($software)) {
                $this->response->setHeader('HTTP/1.0 404 Not Found');
                $this->response->send();
            }
        }

        /*
         * If the software component is a PHP extension, then
         * we have to take the "phpVersion" and "bitsize" into account, when fetching the url.
         * The "version" to "url" relationship has two levels more: "version" to "bizsize" to phpVersion" to "url".
         */
        if ($this->registry->softwareIsPHPExtension($software)) {
            if ($this->registry->versionExists($software, $version) &&
                $this->registry->extensionsHasPhpVersion($software, $version, $bitsize, $phpVersion)) {

                // return download url for specific version, e.g. $registry['phpext_xdebug']['1.2.1']['x86']['5.5']
                $url = $this->registry[$software][$version][$bitsize][$phpVersion];
                $this->response->redirect($url);
            } elseif ($software === 'phpext_phalcon') {

                // special handling for phpext_phalcon, because it has a PHP "patch level" version constraint.
                // (while all other PHP extensions have only a "major.minor" version constraint.)
                $version    = $this->registry->getLatestVersion($software);
                $phpVersion = $this->registry->getPhpVersionInRange($software, $version, $bitsize, $phpVersion);
                $url        = $this->registry[$software][$version][$bitsize][$phpVersion];
                $this->response->redirect($url);
            } elseif ($this->registry->extensionLatestVersionHasPhpVersion($software, $bitsize, $phpVersion)) {

                // the specific version does not exist. return latest version for php default version instead,
                // e.g. $registry['phpext_xdebug']['latest']['url']['x86']['5.5']
                $this->response->redirect($this->registry[$software]['latest']['url'][$bitsize][$phpVersion]);
            } else {

                // software does not exist, download will fail.
                $this->response->setHeader('HTTP/1.0 404 Not Found');
                $this->response->send();
            }
        } else {

            /*
             * These are simple "version" to "url" relationships.
             * There are no other constrains, like "phpVersion" or "bitsize" to consider.
             */

            if ($this->registry->versionExists($software, $version)) {

                // return download url for specific version, e.g. $registry['nginx']['1.2.1']
                $this->response->redirect($this->registry[$software][$version]);
            } elseif ($software === 'php' or $software === 'php-x64') {

                // special handling for PHP, because we have to
                // return the latest patch version (x.y.*) of a "major.minor" PHP version (x.y)
                $version = getLatestVersion($this->registry, $software, $version);
                $this->response->redirect($this->registry[$software][$version]);
            } else {

                // return latest version url, e.g. $registry['nginx']['latest']['url']
                $url = $this->registry[$software]['latest']['url'];
                $this->response->redirect($url);
            }
        }
    }
}
