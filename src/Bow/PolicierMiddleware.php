<?php

namespace Policier\Bow;

use Bow\Http\Request;
use Policier\Policier;

class PolicierMiddleware extends \Policier\PolicierMiddlewareHandler
{
    /**
     * Process middleware
     *
     * @param Reqyest $request
     * @param callable $callable
     * @return mixed
     */
    final public function process(Request $request, callable $next)
    {
        return $this->make($request, $next);
    }
}
