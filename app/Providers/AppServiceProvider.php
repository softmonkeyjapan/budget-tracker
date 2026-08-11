<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\Inertia\DomainPageFinder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
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
        Vite::prefetch(concurrency: 3);

        JsonResource::withoutWrapping();

        $this->bindInertiaDomainPageFinders();
    }

    private function bindInertiaDomainPageFinders(): void
    {
        $this->app->bind('inertia.view-finder', fn ($app) => new DomainPageFinder(
            $app['files'],
            [],
            $app['config']->get('inertia.page_extensions'),
        ));

        $this->app->bind('inertia.testing.view-finder', fn ($app) => new DomainPageFinder(
            $app['files'],
            [],
            $app['config']->get('inertia.testing.page_extensions'),
        ));
    }
}
