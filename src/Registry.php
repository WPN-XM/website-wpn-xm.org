<?php

/**
 * The class is a helper to work with the software registry array.
 * It loads the registry and provides basic array access.
 * There are also helpers and specialized getters for software components and their versions.
 * This allows to access the version data and URLs of software components easily.
 */
class Registry implements ArrayAccess
{
    public $registry;

    public function __construct()
    {
        $this->loadRegistry();
    }

    /**
     * @param $software
     * @param $version
     * @param $bitsize
     * @param $phpVersion
     * @return mixed
     */
    public function getUrl($software, $version, $bitsize = null, $phpVersion = null)
    {
        return $this->registry[$software][$version][$bitsize][$phpVersion];
    }

    /**
     * @return array|mixed
     */
    public function loadRegistry()
    {
        // load software components registry
        $this->registry = include dirname(__DIR__) . '/registry/wpnxm-software-registry.php';

        // ensure registry array is available
        if (!is_array($this->registry)) {
            header('HTTP/1.0 404 Not Found');
        }

        return $this->registry;
    }

    /**
     * Does the requested software exists in our registry?
     *
     * @param $software
     * @return bool
     */
    public function softwareExists($software)
    {
        return (!empty($software) && array_key_exists($software, $this->registry));
    }

    /**
     * Does the requested version of a software exist in our registry?
     *
     * @param $software
     * @param $version
     * @return bool
     */
    public function versionExists($software, $version)
    {
        return (!empty($version) && array_key_exists($version, $this->registry[$software]));
    }

    public function bitsizeExists($software, $version, $bitsize)
    {
        return (!empty($bitsize) && array_key_exists($bitsize, $this->registry[$software][$version]));
    }

    /**
     * Check, that a specific version of a "PHP Extension" (software + version + bitsize)
     * is available for a certain "PHP version".
     *
     * @param $software
     * @param $version
     * @param $bitsize
     * @param $phpVersion
     * @return bool
     */
    public function extensionHasPhpVersion($software, $version, $bitsize, $phpVersion)
    {
        return array_key_exists($phpVersion, $this->registry[$software][$version][$bitsize]);
    }

    /**
     * Check, if the "latest version" of a PHP extensions is available for a certain "PHP version".
     *
     * @param $software
     * @param $bitsize
     * @param $phpVersion
     * @return bool
     */
    public function extensionLatestVersionHasPHPVersion($software, $bitsize, $phpVersion)
    {
        return array_key_exists($phpVersion, $this->registry[$software]['latest']['url'][$bitsize]);
    }

    /**
     * Check, if the "latest version" of a PHP extensions is available for a certain "bitsize".
     * Some PHP extensions (for instance ICE) do not release for x86 and x64.
     *
     * @param $software
     * @param $bitsize
     * @param $phpVersion
     * @return bool
     */
    public function extensionLatestVersionHasBitsize($software, $bitsize)
    {
        return array_key_exists($bitsize, $this->registry[$software]['latest']['url']);
    }

    /**
     * Find the latest version of a software for a given bitsize and php version constraint.
     *
     * @param $software
     * @param $bitsize
     * @param $phpversion PHP versions: accepts PHP versions as Major.Minor and Major.Minor.Patch.
     */
    public function findLatestVersionForBitsize($software, $bitsize, $phpVersion)
    {
        // @todo
        // add a zero as patch level to a major.minor PHP version
        // not good, but i simply don't know how to do the lookup atm, sorry. (probably part of string search)
        if(substr_count($phpVersion, '.') == 1) {
            $phpVersion = $phpVersion . '.0';
        }

        $versions = $this->getVersions($software);

        $versions = array_reverse($versions); // latest version first

        foreach($versions as $_version => $data) {
            if(isset($data[$bitsize]) && $this->extensionHasPhpVersion($software, $_version, $bitsize, $phpVersion)) {
                return $_version;
            }
        }
    }

    /**
     * Is the software a PHP extension?
     *
     * @param $software
     * @return bool
     */
    public function softwareIsPHPExtension($software)
    {
        return (stristr($software, 'phpext_') !== false);
    }

    /**
     * Return the latest version.
     *
     * @param $software
     * @return string Value of $registry[$software]['latest']['version']
     */
    public function getLatestVersion($software)
    {
        return $this->registry[$software]['latest']['version'];
    }

    /**
     * Returns the version array for a software from the registry.
     *
     * @param $software
     * @return mixed
     */
    public function getVersions($software)
    {
        $software = $this->registry[$software];

        // drop all non-version keys
        unset($software['name'], $software['latest'], $software['website']);

        return $software;
    }

    /**
     * @param $software
     * @param $version
     * @param $bitsize
     * @param $phpVersion
     * @return string
     */
    public function getPhpVersionInRange($software, $version, $bitsize, $phpVersion)
    {
        $array = $this->registry[$software][$version][$bitsize];

        // reduce "major.minor.patch" to "major.minor"
        if(substr_count($phpVersion, '.') == 2) {
            $phpVersion = $this->getMajorMinorVersion($phpVersion);
        }

        return $this->getLatestVersionOfRange($array, $phpVersion . '.0', $phpVersion . '.99');
    }

    /**
     * Returns the "major.minor" part of a "major.minor.patch" version.
     *
     * @param string $version "major.minor.patch" Version
     * @return string "major.minor" Version
     */
    public function getMajorMinorVersion($version)
    {
        return substr($version, 0, strrpos($version, '.'));
    }

    /**
     * Returns the latest version of a component inside a min/max version range.
     *
     * Example: fetch the "latest patch version" of a given "major.minor" version (5.4.*).
     * getLatestVersion('php', '5.4.1', '5.4.99') = "5.4.30".
     *
     * @param array Only the versions array for this component from the registry.
     * @param string A version number, setting the minimum (>=).
     * @param string A version number, setting the maximum (<).     *
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

            // The majority of PHP extensions uses just "major.minor" PHP versions.
            // Let's fix these: "5.y" to "5.y.0".
            if (strlen($version) === 3) {
                $version = $version . '.0';
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

    function getBitsize($software) {
        return $this->is64BitSoftware($software) ? 'x64' : 'x86';
    }

    function is64BitSoftware($software) {
        return (stristr($software, 'x64') !== false) ? true : false;
    }

    /**
     * This function helps to keep backwards compatibility for webinstallers,
     * which still use download requests with old software key names.
     * @param string $software
     */
    public static function updateDeprecatedSoftwareRegistryKeyNames($software)
    {
        // renamed
        if ($software == 'wpnxmscp')     { return 'wpnxm-scp';     }
        if ($software == 'wpnxmscp-x64') { return 'wpnxm-scp-x64'; }
        if ($software == 'robomongo')    { return 'robo3t'; }
        if ($software == 'redis')        { return 'redis-x64'; }

        return $software;
    }

    /**
     * These components were removed from the registry.
     *
     * @param string $software
     */
    public static function isDeprecatedSoftwareRegistryKeyName($software)
    {
        // removed
        if($software == 'phpext_xcache') { return true; }
        if($software == 'junction')      { return true; }

        return false;
    }

    /**
     * Re-index the array with a Software Name to Version key-value relationship.
     *
     * @param $array
     * @return array
     */
    public static function reindexArrayWithSoftwareNameAsKey($array)
    {
        $out = [];
        foreach ($array as $key => $values) {
           $software = self::updateDeprecatedSoftwareRegistryKeyNames($values[0]);
           $out[$software] = $values[3];
        }
        return $out;
    }

    /**
     * ArrayAccess for Registry.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return bool
     */
    public function offsetSet($offset, $value)
    {
        return true;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->registry[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->registry[$offset]);
    }

    /**
     * @param mixed $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->registry[$offset]) ? $this->registry[$offset] : null;
    }
}