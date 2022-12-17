<?php

namespace Policier\Bow;

use Bow\Http\Request;
use Bow\Middleware\BaseMiddleware;
use Policier\Exception\TokenInvalidException;

class PolicierMiddleware extends \Policier\PolicierMiddlewareHandler implements BaseMiddleware
{
    /**
     * Process middleware
     *
     * @param Request $request
     * @param callable $callable
     * @param array $args
     *
     * @return mixed
     */
    public function process(Request $request, callable $next, array $args = []): mixed
    {
        return $this->make($request, $next);
    }

    /**
     * @inheritdoc
     */
    protected function getTokenHeader($request): string
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
