<?php

class GetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $url
     */
    public function setGetRequest($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $array);
        $_GET = $array;
        
        include_once dirname(__DIR__) . '/get.php';        
    }

    public function testRequestLatestVersion()
    {
        $url = 'http://wpn-xm.org/get.php?s=nginx';

        $this->setGetRequest($url);

        $this->assertEquals(
            'http://nginx.org/download/nginx-1.9.0.zip',
            $handler->response->url
        );
    }

    public function testRequestSpecificVersion()
    {
        $url = 'http://wpn-xm.org/get.php?s=nginx&v=1.0.0';

        $this->setGetRequest($url);

        $this->assertEquals(
            'http://www.nginx.org/download/nginx-1.0.0.zip',
            $handler->response->url
        );
    }

    public function testRequest_PHPExtension_Phalcon_WithShortPHPVersion54()
    {
        $url = 'http://wpn-xm.org/get.php?s=phpext_phalcon&p=5.4';

        $this->setGetRequest($url);

        $this->assertEquals(
            'http://static.phalconphp.com/files/phalcon_x86_VC9_php5.4.0_2.0.1_nts.zip',
            $handler->response->url
        );
    }

    public function testRequest_PHPExtension_Phalcon_WithShortPHPVersion540()
    {
        $url = 'http://wpn-xm.org/get.php?s=phpext_phalcon&p=5.4.0';

        $this->setGetRequest($url);

        $this->assertEquals(
            'http://static.phalconphp.com/files/phalcon_x86_VC9_php5.4.0_2.0.1_nts.zip',
            $handler->response->url
        );
    }

    public function testRequest_PHPExtension_Phalcon_WithShortPHPVersion55()
    {
        $url = 'http://wpn-xm.org/get.php?s=phpext_phalcon&p=5.5';

        $this->setGetRequest($url);

        include dirname(__DIR__) . '/get.php';

        $this->assertEquals(
            'http://static.phalconphp.com/files/phalcon_x86_VC11_php5.5.0_2.0.1_nts.zip',
            $handler->response->url
        );
    }

    public function testRequest_PHPExtension_Trader_LatestVersion54()
    {
        $url = 'http://wpn-xm.org/get.php?s=phpext_trader&p=5.4';

        $this->setGetRequest($url);

        $this->assertEquals(
            'http://windows.php.net/downloads/pecl/releases/trader/0.4.0/php_trader-0.4.0-5.4-nts-VC9-x86.zip',
            $handler->response->url
        );
    }

    public function testRequest_PHP_LatestVersion54()
    {
        $url = 'http://wpn-xm.org/get.php?s=php&p=5.4';

        $this->setGetRequest($url);

        $this->assertContains("http://windows.php.net/downloads/releases/", $handler->response->url);
        $this->assertContains("5.4", $handler->response->url);
    }

    public function testRequest_PHP_LatestVersion56()
    {
        $url = 'http://wpn-xm.org/get.php?s=php&p=5.6';

        $this->setGetRequest($url);

        $this->assertContains("http://windows.php.net/downloads/releases/", $handler->response->url);
        $this->assertContains("5.6", $handler->response->url);
    }
}
