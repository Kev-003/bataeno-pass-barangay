<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super admin') ? true : null;
        });

        Relation::morphMap([
            'IndigencySPSCertificate' => \App\Models\IndigencySPSCertificate::class,
        ]);

        Storage::disk('documents')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
            return URL::temporarySignedRoute(
                'documents.temp',
                $expiration,
                array_merge($options, [
                    'path' => $path,
                    'token' => $options['token'] ?? null
                ])
            );
        });

        // ==========================================
        // Rate Limiter Definitions
        // ==========================================
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('auth', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(6)->by($request->input('email') ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('lookup', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('document-request', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
