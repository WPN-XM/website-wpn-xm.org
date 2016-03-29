<?php

class GetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
       //
    }

    /**
     * @param $url
     */
    public function setGetRequest($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $array);
        $_GET = $array;
        require_once dirname(__DIR__) . '/get.php';

        $request  = new Request();
        $this->response = new Response();
        $registry = new Registry();
        require_once dirname(__DIR__) . '/stats/Database.php';
        $database = new Database();

        $component = new Component($request, $this->response, $registry, $database);
        $component->redirectTo();
    }

    public function testRequestLatestVersion()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=nginx');

        // the latest version number will change with the next update of the registry
        //$this->assertEquals('http://nginx.org/download/nginx-1.9.11.zip', $this->response->url);
        
        // lets test the pattern latest version number
        $this->assertEquals(1, preg_match('#nginx-(\d+.\d+.\d+).zip#i', $this->response->url));
    }

    public function testRequestSpecificVersion()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=nginx&v=1.0.0');

        $this->assertEquals(
            'http://www.nginx.org/download/nginx-1.0.0.zip',
            $this->response->url
        );
    }

    public function testRequest_PHPExtension_Phalcon_PHPVersion_MajorMinor_54()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_phalcon&p=5.4');

        $this->assertRegExp(
            '#https://static.phalconphp.com/www/files/phalcon_x86_VC9_php(\d+\.\d+\.\d+)_(\d+\.\d+\.\d+(.RC\d+)?)_nts.zip#i',
            $this->response->url
        );
    }

    public function testRequest_PHPExtension_Phalcon_PHPVersion_MajorMinorPatch_540()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_phalcon&p=5.4.0');

        $this->assertRegExp(
            '#https://static.phalconphp.com/www/files/phalcon_x86_VC9_php(\d+\.\d+\.\d+)_(\d+\.\d+\.\d+(.RC\d+)?)_nts.zip#i',
            $this->response->url
        );
    }

    public function testRequest_PHPExtension_Phalcon_PHPVersion_MajorMinor_55()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_phalcon&p=5.5');

        $this->assertRegExp(
            '#https://static.phalconphp.com/www/files/phalcon_x86_vc11_php(\d+\.\d+\.\d+)_(\d+\.\d+\.\d+(.RC\d+)?)_nts.zip#i',
            $this->response->url
        );
    }
    
    public function testRequest_PHPExtension_Wincache_MajorMinorPatchWhatever()
    {
        // this is a request for the latest version for "PHP 5.5" + "x86" => 1.3.7.9
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_wincache');

        $this->assertEquals(
            'http://windows.php.net/downloads/pecl/releases/wincache/1.3.7.9/php_wincache-1.3.7.9-5.5-nts-VC11-x86.zip',
            $this->response->url
        );
    }
    
    public function testRequest_PHPExtension_Trader_LatestVersion_54()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_trader&p=5.4');

        $this->assertEquals(
            'http://windows.php.net/downloads/pecl/releases/trader/0.4.0/php_trader-0.4.0-5.4-nts-VC9-x86.zip',
            $this->response->url
        );
    }    
    

    public function testRequest_PHP_LatestVersion_54()
    {
        $url = 'http://wpn-xm.org/get.php?s=php&p=5.4';
        $this->setGetRequest($url);

        $this->assertContains("http://windows.php.net/downloads/releases/", $this->response->url);
        $this->assertContains("5.4", $this->response->url);
    }

    public function testRequest_PHP_LatestVersion_56()
    {
        $url = 'http://wpn-xm.org/get.php?s=php&p=5.6';

        $this->setGetRequest($url);

        $this->assertContains("http://windows.php.net/downloads/releases/", $this->response->url);
        $this->assertContains("5.6", $this->response->url);
    }
}
