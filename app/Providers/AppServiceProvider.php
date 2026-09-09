<?php

namespace App\Providers;

use App\Support\SearchIndex;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.knowledge-base.layout', function ($view): void {
            $view->with('searchIndex', SearchIndex::build());
        });
    }
}
