<?php

namespace App\Providers;

use App\Models\Tag;
use App\Models\Size;
use App\Models\User;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use App\Models\Language;
use App\Models\ContactUs;
use App\Models\Advertisement;
use App\Observers\TagObserver;
use App\Observers\SizeObserver;
use App\Observers\UserObserver;
use App\Observers\ColorObserver;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;
use App\Observers\LanguageObserver;
use App\Observers\ContactUsObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Observers\AdvertisementObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        Tag::observe(TagObserver::class);
        ContactUs::observe(ContactUsObserver::class);
        Advertisement::observe(AdvertisementObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
