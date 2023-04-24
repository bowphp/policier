<?php

namespace Policier;

use Policier\Exception\TokenExpiredException;
use Policier\Exception\TokenInvalidException;
use Policier\Policier;

abstract class PolicierMiddlewareHandler
{
    /**
     * Process Middleware
     *
     * @param callable $next
     * @return mixed
     */
    public function make($request, callable $next)
    {
        $bearer = $this->getTokenHeader($request);

        if (is_null($bearer) || !preg_match('/^Bearer\s+(.+)/', trim($bearer), $match)) {
            throw new TokenInvalidException($this->getExpirationMessage());
        }

        $token = trim(end($match));

        $policier = Policier::getInstance();

        if (!$policier->verify($token)) {
            throw new TokenInvalidException($this->getInvalidMessage());
        }

        if ($policier->isExpired($token)) {
            throw new TokenExpiredException($this->getExpirationMessage());
        }

        $policier->useToken($token);

        return $next($request);
    }

    /**
     * Get Expiration message
     *
     * @return string
     */
    public function getExpirationMessage()
    {
        return 'Token is expired';
    }

    /**
     * Get Invalid message
     *
     * @return string
     */
    public function getInvalidMessage()
    {
        return "Token is invalid";
    }

    /**
     * Get token header
     *
     * @param mixed $request
     * @return string
     */
    abstract protected function getTokenHeader($request);
}
