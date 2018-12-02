<?php

namespace Bow\Jwt;

use Bow\Http\Request;
use Bow\Jwt\Policier;

class PolicierMiddleware
{
    /**
     * Process middleware
     *
     * @param Reqyest $request
     * @param callable $callable
     * @param array $args
     * @return mixed
     */
    final public function process(Request $request, callable $next)
    {
        $bearer = $request->getHeader('Authorization');

        if (is_null($bearer) || !preg_match('/^Bearer\s+(.+)/', trim($bearer), $match)) {
            return response()->json($this->getUnauthorizedMessage(), $this->getUnauthorizedStatusCode());
        }

        $token = trim(end($match));

        $policier = Policier::getInstance();

        if (!$policier->verify($token)) {
            return response()->json($this->getUnauthorizedMessage(), $this->getUnauthorizedStatusCode());
        }

        if ($policier->isExpired($token)) {
            return response()->json($this->getExpirationMessage(), $this->getExpirationStatusCode());
        }

        $policier->plug($token);

        return $next($request);
    }

    /**
     * Get Error message
     *
     * @return array
     */
    public function getUnauthorizedMessage()
    {
        return [
            'message' => 'unauthorized',
            'error' => true
        ];
    }

    /**
     * Get Error message
     *
     * @return array
     */
    public function getExpirationMessage()
    {
        return [
            'message' => 'token is expired',
            'expired' => true,
            'error' => true
        ];
    }

    /**
     * Get Expirate response code
     *
     * @return int
     */
    public function getExpirationStatusCode()
    {
        return 403;
    }

    /**
     * Get Unauthorized response code
     *
     * @return int
     */
    public function getUnauthorizedStatusCode()
    {
        return 403;
    }
}
