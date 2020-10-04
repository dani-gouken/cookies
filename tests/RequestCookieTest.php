<?php


namespace Atom\Cookies\Tests;

use Atom\Cookies\RequestCookie;
use PHPUnit\Framework\TestCase;

class RequestCookieTest extends TestCase
{
    public function testRequestCookie()
    {
        $cookie = new RequestCookie($name = "foo", $value = "bar");
        $this->assertEquals($name, $cookie->getName());
        $this->assertEquals($value, $cookie->getValue());
    }
}
