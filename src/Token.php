<?php

namespace Policier;

use DateTimeInterface;

final class Token
{
    /**
     * The token
     *
     * @var \Lcobucci\JWT\Token
     */
    private \Lcobucci\JWT\Token $token;

    /**
     * Token constructor
     *
     * @param \Lcobucci\JWT\Token $token
     * @return void
     */
    public function __construct(\Lcobucci\JWT\Token $token)
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
        return (string) $this->token;
    }

    /**
     * Get the token exp value
     *
     * @return int
     */
    public function expireIn(): int
    {
        return $this->token->getClaim('exp');
    }

    /**
     * Get the token exp value
     *
     * @param ?DateTimeInterface $time
     * @return bool
     */
    public function isExpired(?DateTimeInterface $time = null): bool
    {
        return $this->token->isExpired($time);
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
    public function toArray(): array
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
     * @param string|null $default
     * @return mixed
     */
    public function get(string $name, ?string $default = null): mixed
    {
        return $this->token->getClaim($name, $default);
    }

    /**
     * Return the token headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->token->getHeaders();
    }

    /**
     * Return the token specific header
     *
     * @param string $name
     * @return array
     */
    public function getHeader(string $name): mixed
    {
        return $this->token->getHeader($name);
    }
}
