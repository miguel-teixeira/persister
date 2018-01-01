<?php

namespace Persister;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class PersisterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('persister', function () {
            $pdo = DB::connection()->getPdo();

            return new SqlPersister(
                $pdo,
                new SqlGrammar($pdo)
            );
        });
    }

}