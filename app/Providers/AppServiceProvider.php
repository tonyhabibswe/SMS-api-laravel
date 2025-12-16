<?php

namespace App\Providers;

use App\Models\GradeableItem;
use App\Observers\GradeableItemObserver;
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
        // Register the GradeableItem observer
        // Note: Grade auto-creation is handled in GradeableItemService::createItem()
        // This observer is registered for reference and alternative implementation
        // GradeableItem::observe(GradeableItemObserver::class);
    }
}
