<?php

namespace App\Providers;

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
        if ($appUrl = config('app.url')) {
            \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);
            if (str_starts_with($appUrl, 'https://')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        // Ensure pagination links always respect forced root URL (e.g. /asset subdirectory on IIS)
        \Illuminate\Pagination\Paginator::currentPathResolver(function () {
            return url(request()->path());
        });

        // Implicitly grant "Super Admin" role all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
