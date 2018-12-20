<?php

namespace Policier\Laravel;

use Illuminate\Http\Request;

class PolicierMiddleware extends \Policier\PolicierMiddlewareHandler
{
    /**
     * Policier Middleware
     *
     * @param Request $request
     * @param callable $next
     */
    public function handle(Request $request, callable $next)
    {
        return $this->make($request, $next);
    }
}
