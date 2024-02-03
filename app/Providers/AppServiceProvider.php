<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\Product;
use App\Models\User;
use App\Observers\LanguageObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
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
        Product::observe(ProductObserver::class);
        User::observe(UserObserver::class);
        Language::observe(LanguageObserver::class);
    }
}
