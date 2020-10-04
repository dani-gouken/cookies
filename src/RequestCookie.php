<?php


namespace Atom\Cookies;

class RequestCookie implements CookieContract
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string|null
     */
    private $value;
    public function __construct(string $name, ?string $value = null)
    {

        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }
}
