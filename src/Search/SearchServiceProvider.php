<?php

namespace Sympla\Search\Search;

use Illuminate\Support\ServiceProvider;
use Sympla\Search\Commands\NegotiateDocumentator;

class SearchServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/the-brick-search.php' => config_path('the-brick-search.php'),
        ]);
        
        $this->commands([
            NegotiateDocumentator::class
        ]);
    }

    public function register()
    {
        // Get namespace
        $nameSpace = $this->app->getNamespace();

        $this->app->bind('search', 'Sympla\Search\Search\Search');

        // Routes
        $this->app->router->group(['prefix' => '_negotiate', 'namespace' => $nameSpace . 'Http\Controllers'], function() {
            require __DIR__.'/../Http/routes.php';
        });

        $this->loadViewsFrom(__DIR__.'/../../views/', 'negotiate');
    }
}