<?php

namespace Policier;

use DateTimeImmutable;
use DateTimeInterface;
use Lcobucci\JWT\UnencryptedToken;

final class Token
{
    /**
     * The token
     *
     * @var UnencryptedToken
     */
    private UnencryptedToken $token;

    /**
     * Token constructor
     *
     * @param UnencryptedToken $token
     */
    public function __construct(UnencryptedToken $token)
    {
        $this->token = $token;
    }

    /**
     * Get the token value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->token->toString();
    }

    /**
     * Get the token exp value
     *
     * @return int
     */
    public function expireIn(): int
    {
        $exp = $this->token->claims()->get('exp');

        if ($exp instanceof DateTimeInterface) {
            return $exp->getTimestamp();
        }

        return (int) $exp;
    }

    /**
     * Check whether the token is expired
     *
     * @param DateTimeInterface|null $time
     * @return bool
     */
    public function isExpired(?DateTimeInterface $time = null): bool
    {
        return $this->token->isExpired($time ?? new DateTimeImmutable());
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getValue();
    }

    /**
     * Transform token to array
     *
     * @return array
     */
    public function accessToken(): array
    {
        return [
            'access_token' => $this->getValue(),
            'expire_in' => $this->expireIn(),
        ];
    }

    /**
     * Get the value on claims
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        if (!$this->token->claims()->has($name)) {
            return $default;
        }

        return $this->token->claims()->get($name);
    }

    /**
     * Check the claims key
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->token->claims()->has($name);
    }

    /**
     * Get the values
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->token->claims()->all();
    }

    /**
     * Return the token headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->token->headers()->all();
    }

    /**
     * Return the token specific header
     *
     * @param string $name
     * @return mixed
     */
    public function getHeader(string $name): mixed
    {
        return $this->token->headers()->get($name);
    }
}
