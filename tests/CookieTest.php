<?php

namespace Atom\Cookies\Tests;

use Atom\Cookies\Cookie;
use DateTime;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;

class CookieTest extends BaseTestCase
{
    public function testItCanBeInstantiated()
    {
        $cookie = new Cookie($name = "foo", $value = "nar");
        $this->assertEquals($name, $cookie->getName());
        $this->assertEquals($value, $cookie->getValue());
        $cookie = Cookie::create($name, $value);
        $this->assertEquals($name, $cookie->getName());
        $this->assertEquals($value, $cookie->getValue());
    }

    public function testThatDeletes()
    {
        $cookie = Cookie::thatDelete($name = "foo");
        $this->assertEquals($name, $cookie->getName());
        $this->assertLessThan(time(), $cookie->getExpires());
    }

    public function testName()
    {
        $cookie = new Cookie($name = "foo");
        $this->assertEquals($name, $cookie->getName());
        $cookie2 = $cookie->withName($name2 = "bar");
        $this->assertNotEquals($cookie, $cookie2);
        $this->assertEquals($name2, $cookie2->getName());
        $this->assertEquals($name, $cookie->getName());
    }

    public function testValue()
    {
        $cookie = new Cookie("foo", $value = "bar");
        $this->assertEquals($value, $cookie->getValue());
        $cookie2 = $cookie->withValue($value2 = "baz");
        $this->assertEquals($value, $cookie->getValue());
        $this->assertEquals($value2, $cookie2->getValue());
    }

    public function testExpires()
    {
        $cookie = new Cookie("foo", $value = "bar");
        $this->assertEmpty($cookie->getExpires());
        $this->assertEquals(time(), $cookie->thatExpiresOn("now")->getExpires());
        $this->assertEquals(time(), $cookie->thatExpiresOn(new DateTime())->getExpires());

        $this->assertEquals(42, $cookie->thatExpiresOn("42")->getExpires());
        $this->assertEquals(time(), $cookie->thatRemainsUntil("now")->getExpires());

        $this->expectException(InvalidArgumentException::class);
        $cookie->thatExpiresOn("baz")->getExpires();
    }

    public function testPath()
    {
        $cookie = new Cookie("foo", "bar");
        $cookie = $cookie->withPath($path1 = "/bar");
        $this->assertEquals($path1, $cookie->getPath());
        $cookie2 = $cookie->withPath($path2 = "/baz");
        $this->assertEquals($path1, $cookie->getPath());
        $this->assertEquals($path2, $cookie2->getPath());
    }

    public function testMaxAge()
    {
        $cookie = (new Cookie("foo"))->withMaxAge("2 days");
        $this->assertEquals(24 * 3600 * 2, $cookie->getMaxAge());
        $this->assertEquals(3600 * 24, $cookie->thatExpiresIn("1 day")->getMaxAge());
        $this->assertEquals(2 * 3600 * 24, $cookie->thatStayFor("2 day")->getMaxAge());
        $this->assertEquals(5 * 365 * 3600 * 24, $cookie->thatStayForever()->getMaxAge());
    }

    public function testDomain()
    {
        $cookie = (new Cookie("foo"))->withDomain($domain = "gougle");
        $this->assertEquals($domain, $cookie->getDomain());
        $cookie2 = $cookie->withDomain($domain2 = "example.com");
        $this->assertEquals($domain2, $cookie2->getDomain());
        $this->assertEquals($domain, $cookie->getDomain());
    }

    public function testSecure()
    {
        $cookie = new Cookie("foo");
        $this->assertFalse($cookie->isSecure());
        $securedCookie = $cookie->secured();
        $this->assertTrue($securedCookie->isSecure());
        $unsecuredCookie = $cookie->unsecured();
        $this->assertFalse($unsecuredCookie->isSecure());
    }

    public function testHttpOnly()
    {
        $cookie = new Cookie("foo");
        $this->assertFalse($cookie->isHttpOnly());
        $jsCookie = $cookie->availableInJs();
        $this->assertFalse($jsCookie->isHttpOnly());
        $httpCookie = $cookie->httpOnly();
        $this->assertTrue($httpCookie->isHttpOnly());
    }

    public function testSameSite()
    {
        $cookie = new Cookie("foo");
        $this->assertEquals("", $cookie->getSameSite());
        $cookie = $cookie->secured();
        foreach (Cookie::SAME_SITE_ATTRIBUTES as $attr) {
            $newCookie = $cookie->withSameSite($attr);
            $this->assertEquals($newCookie->getSameSite(), $attr);
        }
        $this->expectException(InvalidArgumentException::class);
        $cookie->withSameSite("foo");
    }

    public function testItThrowsIfSameSiteIsNoneAndTheCookieIsNotSecured()
    {
        $cookie = (new Cookie("foo"))->unsecured();
        $this->expectException(InvalidArgumentException::class);
        $cookie->withSameSite(Cookie::SAME_SITE_NONE);
    }

    public function testToHeaderValue()
    {
        $this->assertEquals(
            "sessionId=38afes7a8",
            (new Cookie("sessionId", "38afes7a8"))->toHeaderValue()
        );
        $this->assertEquals(
            "id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT",
            (new Cookie("id", "a3fWa"))
                ->thatExpiresOn(1445412480)
                ->toHeaderValue()
        );
        $this->assertEquals(
            "id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly",
            (new Cookie("id", "a3fWa"))
                ->thatExpiresOn(1445412480)
                ->secured()
                ->httpOnly()
                ->toHeaderValue()
        );

        $this->assertEquals(
            "flavor=choco; Secure; SameSite=None",
            (new Cookie("flavor", "choco"))
                ->secured()
                ->withSameSite(Cookie::SAME_SITE_NONE)
                ->toHeaderValue()
        );
        $this->assertEquals(
            "sessionId=e8bb43229de9; Domain=foo.example.com",
            (new Cookie("sessionId", "e8bb43229de9"))
                ->withDomain("foo.example.com")
                ->toHeaderValue()
        );

        $this->assertEquals(
            "sessionId=e8bb43229de9; Path=/; Domain=foo.example.com",
            (new Cookie("sessionId", "e8bb43229de9"))
                ->withPath("/")
                ->withDomain("foo.example.com")
                ->toHeaderValue()
        );
    }

    /**
     * @throws Exception
     */
    public function testFromHeader()
    {
        $cookie = Cookie::fromHeader("sessionId=e8bb43229de9; Path=/; Domain=foo.example.com");
        $this->assertEquals("sessionId", $cookie->getName());
        $this->assertEquals("e8bb43229de9", $cookie->getValue());
        $this->assertEquals("/", $cookie->getPath());
        $this->assertEquals("foo.example.com", $cookie->getDomain());

        $cookie = Cookie::fromHeader("sessionId=38afes7a8");
        $this->assertEquals("sessionId", $cookie->getName());
        $this->assertEquals("38afes7a8", $cookie->getValue());

        $cookie = Cookie::fromHeader(
            "id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly; SameSite=None"
        );
        $this->assertEquals("id", $cookie->getName());
        $this->assertEquals("a3fWa", $cookie->getValue());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertEquals(strtotime("Wed, 21 Oct 2015 07:28:00 GMT"), $cookie->getExpires());
        $this->assertEquals(strtotime("Wed, 21 Oct 2015 07:28:00 GMT"), $cookie->getExpires());
        $this->assertEquals(Cookie::SAME_SITE_NONE, $cookie->getSameSite());
    }

    public function testToString()
    {
        $cookie = (new Cookie("sessionId", "e8bb43229de9"))
            ->withPath("/")
            ->withDomain("foo.example.com");
        $this->assertEquals("sessionId=e8bb43229de9; Path=/; Domain=foo.example.com", (string)$cookie);
    }

    public function testApplyTo()
    {
        /**
         * @var $response ResponseInterface | MockObject
         */
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $cookie = (new Cookie("sessionId", "e8bb43229de9"))
            ->withPath("/")
            ->withDomain("foo.example.com");
        $response->expects($this->once())->method("withAddedHeader")->with(
            "Set-Cookie",
            (string)$cookie
        )->willReturn($response);
        $cookie->applyTo($response);
    }

}
