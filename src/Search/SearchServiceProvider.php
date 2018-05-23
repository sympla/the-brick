<?php

namespace Sympla\Search\Search;

use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/the-brick-search.php' => config_path('the-brick-search.php'),
        ]);
    }

    public function register()
    {
        $this->app->bind('search', 'Sympla\Search\Search\Search');
    }
}