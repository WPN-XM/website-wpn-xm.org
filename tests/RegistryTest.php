<?php

require_once dirname(__DIR__) . '/get.php';

class RegistryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->registry = new Registry();
    }

    public function testGetLatestVersion()
    {
        $version = $this->registry->getLatestVersion('nginx');
        //$this->assertEquals('1.9.11', $version);
        $this->assertTrue(is_string($version));
        $this->assertTrue(2 == substr_count($version, '.'));
    }

    public function testGetLatestVersion_for_PHP_Extension()
    {
        $version  = $this->registry->getLatestVersion('phpext_phalcon');
        //$this->assertEquals('2.1.0', $version);
        $this->assertTrue(is_string($version));
        $this->assertTrue(2 == substr_count($version, '.'));
    }

    public function testGetPhpVersionInRange_using_PhpVersion_MajorMinor()
    {
        $software   = 'phpext_phalcon';
        $version    = $this->registry->getLatestVersion($software);
        $bitsize    = 'x86';
        $phpVersion = '5.5';

        $phpVersion = $this->registry->getPhpVersionInRange($software, $version, $bitsize, $phpVersion);
        $this->assertEquals('5.5.0', $phpVersion);
    }

    public function testGetPhpVersionInRange_using_PhpVersion_MajorMinorPatch()
    {
        $software   = 'phpext_phalcon';
        $version    = $this->registry->getLatestVersion($software);
        $bitsize    = 'x86';
        $phpVersion = '5.5.0';

        $phpVersion = $this->registry->getPhpVersionInRange($software, $version, $bitsize, $phpVersion);
        $this->assertEquals('5.5.0', $phpVersion);
    }

    public function testGetLatestVersionOfRange()
    {
        $software = 'phpext_phalcon';
        $version  = '2.1.0';
        $bitsize  = 'x86';
        $array    = $this->registry->registry[$software][$version][$bitsize];
        $phpVersion = '5.5';

        $latestVersion = $this->registry->getLatestVersionOfRange($array, $phpVersion . '.0', $phpVersion . '.99');
        $this->assertEquals('5.5.0', $latestVersion);
    }
}
