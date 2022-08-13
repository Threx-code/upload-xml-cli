<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\XMLInterface;
use App\Repositories\XMLRepository;

class XMLServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(XMLInterface::class, XMLRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
