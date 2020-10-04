<?php

namespace Atom\Cookies\Tests;

use Atom\Cookies\Cookie;
use Atom\Cookies\Cookies;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CookiesTest extends TestCase
{
    public function testOf()
    {
        /**
         * @var $request ServerRequestInterface
         */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method("getCookieParams")->willReturn($data = [
            "foo" => "bar",
            "jhon" => "doe"
        ]);
        $cookies = Cookies::of($request);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $cookies->get($key), "baz");
            $this->assertTrue($cookies->has($key));
        }


        /**
         * @var $response ResponseInterface |MockObject
         */
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->method("getHeader")->willReturn($data = [
            "sessionId=e8bb43229de9; Path=/; Domain=foo.example.com",
            "id=38afes7a8"
        ]);
        $cookies = Cookies::of($response);
        $this->assertEquals("e8bb43229de9", $cookies->get("sessionId"));
        $this->assertEquals("38afes7a8", $cookies->get("id"));
        $this->assertTrue($cookies->has("sessionId"));
        $this->assertTrue($cookies->has("id"));
        $this->assertInstanceOf(Cookie::class, $cookies->getCookie("id"));
        $this->assertEquals("foo.example.com", $cookies->getCookie("sessionId")->getDomain());


        $this->assertNull($cookies->getCookie("baz"));
        $this->assertEquals("baz", $cookies->get("baz", "baz"));
        $this->assertFalse($cookies->has("baz"));
    }
}
