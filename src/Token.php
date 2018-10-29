<?php

namespace Bow\Jwt;

final class Token
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $expired_in;

    /**
     * Token contructor
     *
     * @param string $token
     * @param int $expired_in
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
    public function getExpirateTime()
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
