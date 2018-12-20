<?php

use Policier\Policier;

if (!function_exists('policier')) {
    /**
     * Policier Helper
     *
     * @return Policier
     */
    function policier($action = null, ...$args)
    {
        $policier = Policier::getInstance();

        if (is_null($action)) {
            return $policier;
        }

        if (!in_array($action, ['decode', 'encode', 'verify', 'validate', 'parse'])) {
            throw new \BadMethodCallException("Action not define");
        }

        return call_user_func_array([$policier, $action], $args);
    }
}
