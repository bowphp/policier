<?php

namespace Policier\Bow;

use Bow\Http\Request;
use Policier\Exception\TokenInvalidException;

class PolicierMiddleware extends \Policier\PolicierMiddlewareHandler
{
    /**
     * Process middleware
     *
     * @param Request $request
     * @param callable $callable
     *
     * @return mixed
     */
    public function process(Request $request, callable $next)
    {
        return $this->make($request, $next);
    }

    /**
     * @inheritdoc
     */
    protected function getTokenHeader($request)
    {
        $token = $request->getHeader('Authorization');

        if (is_null($token)) {
            $token = $request->getHeader('X-Authorization');

            if (is_null($token)) {
                $token = $request->getHeader('X-Policier-Authorization');
            }
        }

        if (is_null($token)) {
            throw new TokenInvalidException(
                "The token header is not valid. Possible values (X-Policier-Authorization, X-Authorization, Authorization)"
            );
        }

        return $token;
    }
}
