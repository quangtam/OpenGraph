<?php

namespace quangtam\OpenGraph\Facades;

use Illuminate\Support\Facades\Facade;

class OpenGraphFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'OpenGraph';
    }
}
