<?php


namespace Atom\Cookies\Tests;

use Atom\Cookies\CookieConfig;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    public function setUp(): void
    {
        CookieConfig::reset();
    }

}
