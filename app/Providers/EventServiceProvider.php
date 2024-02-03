<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Color;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\Language;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use App\Observers\CategoryObserver;
use App\Observers\ColorObserver;
use App\Observers\LanguageObserver;
use App\Observers\ProductObserver;
use App\Observers\SizeObserver;
use App\Observers\UserObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        User::observe(UserObserver::class);
        Language::observe(LanguageObserver::class);
        Size::observe(SizeObserver::class);
        Color::observe(ColorObserver::class);
        Category::observe(CategoryObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
