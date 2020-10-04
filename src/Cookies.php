<?php


namespace Atom\Cookies;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cookies
{
    /**
     * @var array<CookieContract>
     */
    protected $cookies = [];

    /**
     * @param MessageInterface $message
     * @return Cookies
     * @throws Exception
     */
    public static function of(MessageInterface $message)
    {
        return (new self())
            ->fillWithDataOfMessage($message);
    }

    /**
     * @param MessageInterface $message
     * @return $this
     * @throws Exception
     */
    public function fillWithDataOfMessage(MessageInterface $message): self
    {
        if ($message instanceof ServerRequestInterface) {
            /**
             * @var $message ServerRequestInterface
             */
            foreach ($message->getCookieParams() as $key => $value) {
                $this->cookies[$key] = new RequestCookie($key, $value);
            }
            return $this;
        }

        if ($message instanceof ResponseInterface) {
            /**
             * @var $message ResponseInterface
             */
            foreach ($message->getHeader("Set-Cookie") as $header) {
                $this->add(Cookie::fromHeader($header));
            }
            return $this;
        }
        throw new InvalidArgumentException("We are unable to load the cookies of the message");
    }

    protected function add(CookieContract $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
    }

    public function get(string $key, ?string $defaultValue = null): ?string
    {
        if (!$this->has($key)) {
            return $defaultValue;
        }
        return $this->cookies[$key]->getValue() ?? $defaultValue;
    }

    /**
     * @param string $key
     * @return CookieContract|Cookie|RequestCookie
     */
    public function getCookie(string $key): ?CookieContract
    {
        if (!$this->has($key)) {
            return null;
        }
        return $this->cookies[$key];
    }

    public function has(string $key)
    {
        return array_key_exists($key, $this->cookies);
    }
}
