<?php

namespace Policier\Laravel;

use Illuminate\Support\Facades\Facade;

class Policier extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'policier';
    }
}
