<?php


namespace Atom\Cookies;

use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use function count;
use function explode;
use function preg_match;
use function strcasecmp;
use function stripos;

class Cookie implements CookieContract
{
    public const SAME_SITE_LAX = "Lax";
    public const SAME_SITE_STRICT = "Strict";
    public const SAME_SITE_NONE = "None";
    public const SAME_SITE_EMPTY = "";

    public const SAME_SITE_ATTRIBUTES = [
        self::SAME_SITE_EMPTY,
        self::SAME_SITE_LAX,
        self::SAME_SITE_STRICT,
        self::SAME_SITE_NONE
    ];

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var int
     */
    private $expires;

    /**
     * @var int
     */
    private $maxAge;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var string|null
     */
    private $domain;

    /** @var bool */
    private $secure;

    /**
     * @var bool
     */
    private $httpOnly;

    /**
     * @var string|null
     */
    private $sameSite;

    public function __construct(
        string $name,
        ?string $value = null,
        int $expires = 0,
        string $path = "",
        ?string $domain = null,
        ?bool $secure = null,
        ?bool $httpOnly = null,
        ?string $sameSite = null,
        ?int $maxAge = null
    ) {
        $this->assertValidName($name);
        $this->name = $name;
        $this->value = $value;
        $this->expires = $expires;
        $this->maxAge = $maxAge;
        $this->path = $path;
        $this->domain = $domain ?? CookieConfig::getDomain();
        $this->secure = $secure ?? CookieConfig::isSecure();
        $this->httpOnly = $httpOnly ?? CookieConfig::isHttpOnly();
        $this->sameSite = $sameSite ?? CookieConfig::getSameSite();
    }

    /**
     * @param string $name
     * @param string|null $value
     * @return Cookie
     */
    public static function create(string $name, ?string $value = null)
    {
        return new self($name, $value);
    }

    public static function thatDelete(string $name)
    {
        return self::create($name)
            ->thatExpiresOn(time() - 3600);
    }


    /**
     * @param ResponseInterface $message
     * @return ResponseInterface
     */
    public function applyTo(ResponseInterface $message): ResponseInterface
    {
        return $message->withAddedHeader("Set-Cookie", (string)$this);
    }

    public function toHeaderValue(): string
    {
        $headerValue = sprintf('%s=%s', $this->name, urlencode($this->value));

        if ($this->expires !== 0) {
            $headerValue .= sprintf(
                '; Expires=%s',
                gmdate('D, d M Y H:i:s T', $this->expires)
            );
        }
        if (empty($this->path) === false) {
            $headerValue .= sprintf('; Path=%s', $this->path);
        }
        if (empty($this->domain) === false) {
            $headerValue .= sprintf('; Domain=%s', $this->domain);
        }
        if ($this->secure) {
            $headerValue .= '; Secure';
        }
        if ($this->httpOnly) {
            $headerValue .= '; HttpOnly';
        }
        if ($this->sameSite !== '') {
            $headerValue .= sprintf('; SameSite=%s', $this->sameSite);
        }
        return $headerValue;
    }

    public function __toString()
    {
        return $this->toHeaderValue();
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function withName(string $name): self
    {
        $clone = clone($this);
        $clone->name = $name;
        return $clone;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function withValue(?string $value = null): self
    {
        $clone = clone($this);
        $clone->value = $value;
        return $clone;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param $expires
     * @return Cookie
     */
    public function withExpires($expires): Cookie
    {
        $clone = clone($this);
        $clone->expires = $this->toTimestamp($expires);
        return $clone;
    }

    /**
     * @param $expires
     * @return Cookie
     */
    public function thatRemainsUntil($expires): self
    {
        return $this->withExpires($expires);
    }

    /**
     * @param $expires
     * @return Cookie
     */
    public function thatExpiresOn($expires): Cookie
    {
        return $this->withExpires($expires);
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * @param string|null $path
     * @return Cookie
     */
    public function withPath(?string $path): Cookie
    {
        $clone = clone($this);
        $clone->path = $path;
        return $clone;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param $maxAge
     * @return Cookie
     */
    public function withMaxAge($maxAge): Cookie
    {
        $clone = clone($this);
        $clone->maxAge = $this->toTimestamp($maxAge, true);
        return $clone;
    }

    public function thatExpiresIn($maxAge)
    {
        return $this->withMaxAge($maxAge);
    }

    /**
     * @return Cookie
     */
    public function thatStayForever(): Cookie
    {
        $fiveYear = 5 * 365 * 3600 * 24;
        return $this->thatStayFor($fiveYear);
    }

    /**
     * @param $date
     * @return Cookie
     */
    public function thatStayFor($date): Cookie
    {
        return $this->thatExpiresIn($date);
    }

    /**
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * @param string|null $domain
     * @return Cookie
     */
    public function withDomain(?string $domain): Cookie
    {
        $clone = clone($this);
        $clone->domain = $domain;
        return $clone;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param bool $secure
     * @return Cookie
     */
    public function withSecure(bool $secure): self
    {
        $clone = clone($this);
        $clone->secure = $secure;
        return $clone;
    }

    /**
     * @return Cookie
     */
    public function secured(): self
    {
        return $this->withSecure(true);
    }

    /**
     * @return Cookie
     */
    public function unsecured(): Cookie
    {
        return $this->withSecure(false);
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $httpOnly
     * @return Cookie
     */
    public function withHttpOnly(bool $httpOnly): Cookie
    {
        $clone = clone($this);
        $clone->httpOnly = $httpOnly;
        return $clone;
    }

    /**
     * @return $this
     */
    public function httpOnly(): self
    {
        return $this->withHttpOnly(true);
    }

    /**
     * @return $this
     */
    public function availableInJs(): self
    {
        return $this->withHttpOnly(false);
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @param string|null $sameSite
     * @return Cookie
     */
    public function withSameSite(?string $sameSite): self
    {
        $this->assertValidSameSite($sameSite);
        $clone = clone($this);
        $clone->sameSite = $sameSite;
        return $clone;
    }

    /**
     * @return string|null
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }


    /**
     * @param null $date
     * @param bool $isInterval
     * @return int
     */
    private function toTimestamp($date = null, bool $isInterval = false)
    {
        if ($date === null) {
            return 0;
        }
        if ($date instanceof DateTimeInterface) {
            return $date->getTimestamp();
        }
        if (is_numeric($date)) {
            return (int)$date;
        }
        $time = strtotime($date, !$isInterval ? time() : 0);
        if (!is_int($time)) {
            throw new InvalidArgumentException(sprintf('Invalid expires "%s" provided', $date));
        }
        return $time;
    }

    /**
     * @param string $sameSite
     */
    private function assertValidSameSite(string $sameSite)
    {
        if (!in_array($sameSite, self::SAME_SITE_ATTRIBUTES)) {
            throw new InvalidArgumentException('The same site attribute must be "lax", "strict", "none" or ""');
        }
        if ($sameSite === self::SAME_SITE_NONE && !$this->isSecure()) {
            throw new InvalidArgumentException('The same site attribute can only be "none" when secure is set to true');
        }
    }

    /**
     * @param string $name
     */
    private function assertValidName(string $name)
    {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InvalidArgumentException(
                sprintf('The cookie name "%s" contains invalid characters.', $name)
            );
        }

        if (empty($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty.');
        }
    }

    /**
     * @param string $cookieHeader
     * @return static
     * @throws Exception
     */
    public static function fromHeader(string $cookieHeader): self
    {
        if (empty($cookieHeader) ||
            !preg_match('/^(.*?)=(.*?)(?:; (.*?))?$/i', $cookieHeader, $matches) ||
            count($matches) < 3
        ) {
            throw new Exception('Not a valid Set-Cookie header.');
        }
        $cookie = (new self($matches[1], $matches[2]))->withPath('')
            ->withHttpOnly(false);
        if (!isset($matches[3])) {
            return $cookie;
        }
        $attributes = explode('; ', $matches[3]);
        foreach ($attributes as $attribute) {
            if (strcasecmp($attribute, 'HttpOnly') === 0) {
                $cookie = $cookie->withHttpOnly(true);
            } elseif (strcasecmp($attribute, 'Secure') === 0) {
                $cookie = $cookie->withSecure(true);
            } elseif (stripos($attribute, 'Expires=') === 0) {
                $cookie = $cookie->withExpires((int)strtotime(substr($attribute, 8)));
            } elseif (stripos($attribute, 'Domain=') === 0) {
                $cookie = $cookie->withDomain(substr($attribute, 7));
            } elseif (stripos($attribute, 'Path=') === 0) {
                $cookie = $cookie->withPath(substr($attribute, 5));
            } elseif (stripos($attribute, 'SameSite=') === 0) {
                $cookie = $cookie->withSameSite(substr(ucwords($attribute), 9));
            }
        }
        return $cookie;
    }
}
