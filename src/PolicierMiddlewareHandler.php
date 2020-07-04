<?php

namespace Policier;

use Bow\Http\Request;
use Policier\Policier;

abstract class PolicierMiddlewareHandler
{
    /**
     * Process Middleware
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    final public function process(Request $request, callable $next)
    {
        $bearer = $this->getTokenHeader($request);

        if (is_null($bearer) || !preg_match('/^Bearer\s+(.+)/', trim($bearer), $match)) {
            return response()->json(
                $this->getUnauthorizedMessage(),
                $this->getUnauthorizedStatusCode()
            );
        }

        $token = trim(end($match));

        $policier = Policier::getInstance();

        if (!$policier->verify($token)) {
            return response()->json(
                $this->getUnauthorizedMessage(),
                $this->getUnauthorizedStatusCode()
            );
        }

        if ($policier->isExpired($token)) {
            return response()->json(
                $this->getExpirationMessage(),
                $this->getExpirationStatusCode()
            );
        }

        $policier->plug($token);

        return $next($request);
    }

    /**
     * Get token header
     *
     * @param Request $request
     * @return string
     */
    abstract protected function getTokenHeader(Request $request);

    /**
     * Get Error message
     *
     * @return array
     */
    public function getUnauthorizedMessage()
    {
        return [
            'message' => 'Unauthorized',
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
            'message' => 'Token is expired',
            'expired' => true,
            'error' => true
        ];
    }

    /**
     * Get Expire response code
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
