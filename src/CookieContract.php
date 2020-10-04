<?php


namespace Atom\Cookies;

interface CookieContract
{
    public function getName(): string;

    public function getValue(): ?string;
}
