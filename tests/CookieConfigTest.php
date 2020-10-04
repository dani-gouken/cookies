<?php


namespace Atom\Cookies\Tests;

use Atom\Cookies\Cookie;
use Atom\Cookies\CookieConfig;

class CookieConfigTest extends BaseTestCase
{
    public function testDefaultConfigure()
    {
        $this->assertEquals("", CookieConfig::getDomain());
        $this->assertFalse(CookieConfig::isSecure());
        $this->assertFalse(CookieConfig::isHttpOnly());
        $this->assertEquals(Cookie::SAME_SITE_EMPTY, CookieConfig::getSameSite());
    }

    public function testConfigure()
    {
        CookieConfig::configure()
            ->withDomain($domain = "foo.com")
            ->withSameSite($samesite = Cookie::SAME_SITE_LAX)
            ->withHttpOnly($httpOnly = true)
            ->withSecure($secure = true);
        $this->assertEquals($domain, CookieConfig::getDomain());
        $this->assertTrue($secure);
        $this->assertTrue($httpOnly);
        $this->assertEquals($samesite, CookieConfig::getSameSite());
    }
}
