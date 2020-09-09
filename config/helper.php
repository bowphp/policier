<?php

use Policier\Policier;
use Policier\Token;

if (!function_exists('policier')) {
    /**
     * Policier Helper
     * 
     * @param string $action
     * @param array $args
     * @return Policier|Token
     */
    function policier($action = null, ...$args)
    {
        if (class_exists(\App::class)) {
            $policier = app('policier');
        } else {
            $policier = Policier::getInstance();
        }

        if (is_null($action)) {
            return $policier;
        }

        if (!in_array($action, ['decode', 'encode', 'verify', 'validate', 'parse'])) {
            throw new \BadMethodCallException("Action not define");
        }

        return call_user_func_array([$policier, $action], $args);
    }
}
