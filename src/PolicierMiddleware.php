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
    public function process(Request $request, callable $next)
    {
        $bearer = $request->getHeader('Authorization');

        if (is_null($bearer) || !preg_match('/^Bearer\s+(.+)/', trim($bearer), $match)) {
            return response()->json($this->getUnauthorizedMessage(), $this->getCode());
        }

        $token = trim(end($match));

        $policier = Policier::getInstance();

        if (!$policier->verify($token)) {
            return response()->json($this->getUnauthorizedMessage(), $this->getCode());
        }

        if ($policier->isExpired($token)) {
            return response()->json($this->getExporateMessage(), $this->getCode());
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
    public function getExporateMessage()
    {
        return [
            'message' => 'token is expired',
            'expired' => true,
            'error' => true
        ];
    }

    /**
     * Get response code
     *
     * @return int
     */
    public function getCode()
    {
        return 403;
    }
}
