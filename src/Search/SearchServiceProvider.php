<?php

namespace Sympla\Search\Search;

use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->bind('search', 'Sympla\Search\Search\Search');
    }
}