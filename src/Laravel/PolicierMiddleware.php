<?php

namespace Policier\Laravel;

use Illuminate\Http\Request;

class PolicierMiddleware extends \Policier\PolicierMiddlewareHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     *
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        return $this->make($request, $next);
    }

    /**
     * @inheritdoc
     */
    protected function getTokenHeader($request)
    {
        return $request->header('Authorization');
    }
}
