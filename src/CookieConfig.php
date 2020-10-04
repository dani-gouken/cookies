<?php


namespace Atom\Cookies;

class CookieConfig
{
    /**
     * @var string
     */
    protected static $domain = "";
    /**
     * @var bool
     */
    protected static $secure = false;
    /**
     * @var bool
     */
    protected static $httpOnly = false;
    /**
     * @var string
     */
    protected static $sameSite = Cookie::SAME_SITE_EMPTY;

    /**
     * @return string
     */
    public static function getDomain(): string
    {
        return self::$domain;
    }

    /**
     * @param string $domain
     * @return CookieConfig
     */
    public function withDomain(string $domain): self
    {
        self::$domain = $domain;
        return $this;
    }

    /**
     * @return bool
     */
    public static function isSecure(): bool
    {
        return self::$secure;
    }

    /**
     * @param bool $secure
     * @return CookieConfig
     */
    public function withSecure(bool $secure): self
    {
        self::$secure = $secure;
        return $this;
    }

    /**
     * @return bool
     */
    public static function isHttpOnly(): bool
    {
        return self::$httpOnly;
    }

    /**
     * @param bool $httpOnly
     * @return CookieConfig
     */
    public function withHttpOnly(bool $httpOnly): self
    {
        self::$httpOnly = $httpOnly;
        return $this;
    }

    /**
     * @return string
     */
    public static function getSameSite(): string
    {
        return self::$sameSite;
    }

    /**
     * @param string $sameSite
     * @return CookieConfig
     */
    public function withSameSite(string $sameSite): self
    {
        self::$sameSite = $sameSite;
        return $this;
    }

    public static function configure(): self
    {
        return new self();
    }

    public static function reset(): self
    {
        return self::configure()
            ->withSecure(false)
            ->withHttpOnly(false)
            ->withDomain("")
            ->withSameSite(Cookie::SAME_SITE_EMPTY);
    }
}
