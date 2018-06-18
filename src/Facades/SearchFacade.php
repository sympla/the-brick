<?php

namespace Sympla\Search\Facades;

use Illuminate\Support\Facades\Facade;

class SearchFacade extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'search';
    }
}