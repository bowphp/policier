<?php

namespace Bow\Jwt;

use Bow\Jwt\Policier;

class PolicierMiddeware
{
    /**
     * Process middleware
     *
     * @param Reqyest $request
     * @param callable $callable
     * @param array $args
     * @return mixed
     */
    public function process(Request $request, callable $next, array $args = [])
    {
        if (in_array('guest', $args)) {
            return $next($request);
        }

        $bearer = $request->getHeader('Authorization');

        if (is_null($bearer) || !preg_match('/Bearer\s+(.+)/', trim($bearer), $match)) {
            return response()->json(['message' => 'unauthorized', 'error' => false], 403);
        }

        $token = trim(end($match));

        $policier = Policier::getInstance();

        if ($policier->verify($token)) {
            return response()->json(['message' => 'unauthorized', 'error' => false], 403);
        }

        return $next($request);
    }
}
