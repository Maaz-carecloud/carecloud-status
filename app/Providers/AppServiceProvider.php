<?php

namespace App\Providers;

use App\Models\Component;
use App\Models\Incident;
use App\Models\Subscriber;
use App\Models\User;
use App\Policies\ComponentPolicy;
use App\Policies\IncidentPolicy;
use App\Policies\SubscriberPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Component::class => ComponentPolicy::class,
        Incident::class => IncidentPolicy::class,
        Subscriber::class => SubscriberPolicy::class,
        User::class => UserPolicy::class,
    ];

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
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Register custom notification channels only if classes exist
        if (class_exists(\App\Notifications\Channels\SmsChannel::class)) {
            Notification::extend('sms', function ($app) {
                return $app->make(\App\Notifications\Channels\SmsChannel::class);
            });
        }

        if (class_exists(\App\Notifications\Channels\TeamsChannel::class)) {
            Notification::extend('teams', function ($app) {
                return $app->make(\App\Notifications\Channels\TeamsChannel::class);
            });
        }
    }
}
