<?php

class GetTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
       if (!extension_loaded('pdo')) {
            $this->markTestSkipped('This test requires the PHP extensions "PDO"');
       }

       if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('This test requires the PHP extensions "PDO_SQLITE".');
       }
    }

    /**
     * @param $url
     */
    public function setGetRequest($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $_GET);

        require_once dirname(__DIR__) . '/get.php';

        $this->request  = new Request();
        $this->response = new Response();
        $registry = new Registry();
        require_once dirname(__DIR__) . '/stats/Database.php';
        $database = new Database();

        $component = new Component($this->request, $this->response, $registry, $database);
        $component->redirectTo();
    }

    public function testRequestLatestVersion()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=nginx');

        $this->assertContains('http://nginx.org/download/nginx-', $this->response->url);
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
            '#https://static.phalconphp.com/www/files/phalcon_x86_VC9_php5.4.0_(\d+\.\d+\.\d+(.RC\d+)?)_nts.zip#i',
            $this->response->url
        );
    }

    public function testRequest_PHPExtension_Phalcon_PHPVersion_MajorMinorPatch_540()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_phalcon&p=5.4.0');

        $this->assertRegExp(
            '#https://static.phalconphp.com/www/files/phalcon_x86_VC9_php5.4.0_(\d+\.\d+\.\d+(.RC\d+)?)_nts.zip#i',
            $this->response->url
        );
    }

    public function testRequest_PHPExtension_Phalcon_PHPVersion_MajorMinor_55()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_phalcon&p=5.5');

        $this->assertRegExp(
            '#https://static.phalconphp.com/www/files/phalcon_x86_vc11_php5.5.0_(\d+\.\d+\.\d+(.RC\d+)?)_nts.zip#i',
            $this->response->url
        );
    }

    public function testRequest_PHPExtension_Wincache_LatestVersion()
    {
        // we are testing because of the Major.Minor.Patch.Whatever version number
        // this is a request for the latest version with "default PHP version" and "default bitsize"
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_wincache');

        $this->assertRegExp(
            '#http://windows.php.net/downloads/pecl/releases/wincache/(\d+\.\d+\.\d+.\d+)/php_wincache-(\d+\.\d+\.\d+.\d+)-'.$this->request->getDefaultPHPVersion().'-nts-VC11-'.$this->request->getDefaultBitsize().'.zip#i',
            $this->response->url
        );
    }

    public function testRequest_PHPExtension_Wincache_LatestVersion_MajorMinor_56_x64()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_wincache&p=5.6&bitsize=x64');

        $this->assertRegExp(
            '#http://windows.php.net/downloads/pecl/releases/wincache/(\d+\.\d+\.\d+.\d+)/php_wincache-(\d+\.\d+\.\d+.\d+)-5.6-nts-VC11-x64.zip#i',
            $this->response->url
        );
    }

    public function testRequest_PHPQA_SpecificVersion()
    {
        // request for "PHP QA 7.0.1RC1" with default bitsize "x86"
        $this->setGetRequest('http://wpn-xm.org/get.php?s=php-qa&v=7.0.1RC1');

        $this->assertEquals(
            'http://windows.php.net/downloads/qa/archives/php-7.0.1RC1-nts-Win32-VC14-x86.zip',
            $this->response->url
        );
    }

    public function testRequest_PHPQA_LatestVersion_MajorMinorRange_56()
    {
        // request for "latest version" of "PHP 5.6.*" (range) with default bitsize "x86"
        $this->setGetRequest('http://wpn-xm.org/get.php?s=php-qa&p=5.6');

        $this->assertEquals(
            'http://windows.php.net/downloads/qa/archives/php-5.6.11RC1-nts-Win32-VC11-x86.zip',
            $this->response->url
        );
    }

    public function testRequest_PHPExtension_Trader_LatestVersion_MajorMinor_56()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_trader&p=5.6');

        $this->assertEquals(
            'http://windows.php.net/downloads/pecl/releases/trader/0.4.0/php_trader-0.4.0-5.6-nts-vc11-x86.zip',
            $this->response->url
        );
    }

    public function testRequest_PHP_LatestVersion_MajorMinor_54()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=php&p=5.4');

        $this->assertContains("http://windows.php.net/downloads/releases/", $this->response->url);
        $this->assertContains("5.4", $this->response->url);
    }

    public function testRequest_PHP_LatestVersion_MajorMinor_56()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=php&p=5.6');

        $this->assertContains("http://windows.php.net/downloads/releases/", $this->response->url);
        $this->assertContains("5.6", $this->response->url);
    }

    public function testRequest_PHP_LatestVersion_MajorMinor_70()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=php&p=7.0');

        $this->assertContains("http://windows.php.net/downloads/releases/", $this->response->url);
        $this->assertContains("7.0", $this->response->url);
    }

    public function testRequest_PHP_LatestVersion_MajorMinor_71()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=php&p=7.1');

        $this->assertContains("http://windows.php.net/downloads/releases/", $this->response->url);
        $this->assertContains("7.1", $this->response->url);
    }

    public function testRequest_PHP_DefaultVersion_Major_99()
    {
        // p=99 is invalid; the default PHP version is set instead
        $this->setGetRequest('http://wpn-xm.org/get.php?s=php&p=99');

        $this->assertContains("http://windows.php.net/downloads/", $this->response->url);
        $this->assertEquals(1, preg_match('#php-(\d+.\d+.\d+)-nts-#i', $this->response->url));
        $this->assertContains("5.", $this->response->url);
    }

    public function testRequest_PHPExtension_XDebug_DefaultVersion_Major_99()
    {
        // p=99 is invalid; the default PHP version is set instead
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_xdebug&p=99');

        $this->assertContains("http://windows.php.net/downloads/pecl/releases/xdebug/", $this->response->url);
        $this->assertEquals(1, preg_match('#php_xdebug-(.*)-5.6-nts-VC11-x86.zip#i', $this->response->url));
        $this->assertContains("5.6", $this->response->url);
    }

    public function testRequest_PHPExtension_Ice_LatestVersion_MajorMinor_56()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_ice&p=5.6');

        $this->assertContains("http://www.iceframework.org/dll/ice-1.1.2-php-5.6-nts-vc11-x86.zip", $this->response->url);
        $this->assertEquals(1, preg_match('#ice-(.*)-5.6-nts-VC11-x86.zip#i', $this->response->url));
        $this->assertContains("5.6", $this->response->url);
    }

    public function testRequest_PHPExtension_Ice_For_PHP_70_Bitsize_x64()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_ice&p=7.0&bitsize=x64');

        $this->assertContains("http://www.iceframework.org/dll/ice", $this->response->url);
        $this->assertEquals(1, preg_match('#ice-(.*)-php-7.0-nts-vc14-x64.zip#i', $this->response->url));
        $this->assertContains("7.0", $this->response->url);
        $this->assertContains("x64", $this->response->url);
    }

    public function testRequest_PHPExtension_Imagick_LatestVersion_MajorMinor_56()
    {
        $this->setGetRequest('http://wpn-xm.org/get.php?s=phpext_imagick&p=5.6');

        $this->assertContains("http://windows.php.net/downloads/pecl/releases/imagick", $this->response->url);
        $this->assertContains("5.6", $this->response->url);
    }
}
