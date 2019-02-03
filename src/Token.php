<?php

namespace Policier;

final class Token
{
    /**
     * The token
     *
     * @var string
     */
    private $token;

    /**
     * The expiration time
     *
     * @var int
     */
    private $expired_in;

    /**
     * Token contructor
     *
     * @param string $token
     * @param int $expired_in
     *
     * @return void
     */
    public function __construct($token, $expired_in)
    {
        $this->token = $token;

        $this->expired_in = $expired_in;
    }

    /**
     * Get the token value
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get the token value
     *
     * @return string
     */
    public function expireIn()
    {
        return $this->expired_in;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->token;
    }
}
