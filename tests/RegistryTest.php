<?php

require_once dirname(__DIR__) . '/src/Registry.php';

class RegistryTest extends \PHPUnit\Framework\TestCase
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
        $this->assertTrue(is_string($version));
        $this->assertRegExp('#(\d+\.\d+\.\d+(.RC\d+)?)#i',$version);
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

    public function testGetLatestVersion_using_PhpVersion_MajorMinorPatch_and_Bitsize()
    {
        $software   = 'phpext_phalcon';
        $bitsize    = 'x86';
        $phpVersion = '5.4.0';

        $version = $this->registry->findLatestVersionForBitsize($software, $bitsize, $phpVersion);

        $this->assertEquals('3.0.2', $version);
    }

    public function testGetLatestVersion_using_PhpVersion_MajorMinor_and_Bitsize()
    {
        $software   = 'phpext_phalcon';
        $bitsize    = 'x86';
        $phpVersion = '5.4';

        $version = $this->registry->findLatestVersionForBitsize($software, $bitsize, $phpVersion);

        $this->assertEquals('3.0.2', $version);
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
        $version  = '2.0.10';
        $bitsize  = 'x86';
        $array    = $this->registry->registry[$software][$version][$bitsize];
        $phpVersion = '5.5';

        $latestVersion = $this->registry->getLatestVersionOfRange($array, $phpVersion . '.0', $phpVersion . '.99');
        $this->assertEquals('5.5.0', $latestVersion);
    }
}
