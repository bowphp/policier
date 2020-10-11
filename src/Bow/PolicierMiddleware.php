<?php

namespace Policier\Bow;

use Bow\Http\Request;

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
        return $request->getHeader('Authorization');
    }
}
