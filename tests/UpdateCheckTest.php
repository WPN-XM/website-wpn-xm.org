<?php

class GetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once dirname(__DIR__) . '/updatecheck.php';
    }

    function getVersionStrings()
    {
        return array(
          // array($expected, $versionString)
          // ok
          array("1.2.3",                        "1.2.3"),          
          array("1.2.3h",                       "1.2.3h"),
          array("1.2.3-alpha+001",              "1.2.3-alpha+001"),
          array("1.2.3+20130313144700",         "1.2.3+20130313144700"),
          array("1.2.3-alpha",                  "1.2.3-alpha"),
          array("1.2.3-alpha.1",                "1.2.3-alpha.1"),
          array("1.2.3-0.3.7",                  "1.2.3-0.3.7"),
          array("1.2.3-x.7.z.92",               "1.2.3-x.7.z.92"),
          array("1.2.3-beta+exp.sha.5114f85",   "1.2.3-beta+exp.sha.5114f85"),
          // ok - real versions
          array("7.0.0alpha1",                  "7.0.0alpha1"), // PHP
          array("0.9.8ze",                      "0.9.8ze"),     // OpenSSL
          array("5.22.2.1",                     "5.22.2.1"),    // Perl
          // not ok and cleaned
          array("1.2.3",                        "1.2.3§$&"),
          array("1.2.3h",                         "1.2.3h°!\"§$%&/()=?<>|;:"),
        );
    }

    /**
     * @dataProvider getVersionStrings
     */ 
    public function testCleanVersionString($expected, $versionString)
    {
        $cleanVersionString = cleanVersionString($versionString);
        $this->assertEquals($expected, $cleanVersionString);
    }

    function getSoftwareStrings()
    {
        return array(
          // array($expected, $versionString)
          // ok
          array("abc",      "abc"),
          array("abc-x86",  "abc-x86"),
          array("abc-x64",  "abc-x64"),
          array("abc_abc",  "abc_abc"),
        );
    }

    /**
     * @dataProvider getSoftwareStrings
     */ 
    public function testCleanSoftwareString($expected, $softwareString)
    {
        $cleanSoftwareString = cleanSoftwareString($softwareString);        
        $this->assertEquals($expected, $cleanSoftwareString);
    }
}