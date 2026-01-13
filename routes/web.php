<?php

use App\Livewire\Dashboard;
use App\Livewire\Admin\Metrics\Dashboard as MetricsDashboard;
use App\Livewire\Analytics\ComponentStatusChart;
use App\Livewire\Public\IncidentHistory;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\ComponentForm;
use App\Livewire\Settings\ComponentList;
use App\Livewire\Settings\IncidentForm;
use App\Livewire\Settings\IncidentList;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\SubscriberList;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Settings\UserList;
use App\Livewire\Public\StatusPage;
use App\Livewire\Public\SubscribeForm;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Public Status Pages
Route::get('/', StatusPage::class)->name('home');
Route::get('/history', IncidentHistory::class)->name('history');
Route::get('/subscribe', SubscribeForm::class)->name('subscribe');
Route::get('/subscription/verify/{token}', [SubscriptionController::class, 'verify'])->name('subscription.verify');
Route::get('/subscription/unsubscribe/{email}', [SubscriptionController::class, 'unsubscribe'])->name('subscription.unsubscribe');

// Dashboard
Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Components Management
    Route::get('admin/components', ComponentList::class)->name('components.index');
    Route::get('admin/components/create', ComponentForm::class)->name('components.create');
    Route::get('admin/components/{componentId}/edit', ComponentForm::class)->name('components.edit');
    
    // Incidents Management
    Route::get('admin/incidents', IncidentList::class)->name('incidents.index');
    Route::get('admin/incidents/create', IncidentForm::class)->name('incidents.create');
    Route::get('admin/incidents/{incidentId}/edit', IncidentForm::class)->name('incidents.edit');
    
    // Subscribers Management
    Route::get('admin/subscribers', SubscriberList::class)->name('subscribers.index');
    
    // Users Management (Super Admin only)
    Route::get('admin/users', UserList::class)->name('users.index');
    
    // Analytics
    Route::get('admin/analytics', ComponentStatusChart::class)->name('analytics.index');
    
    // Metrics Dashboard
    Route::get('admin/metrics', MetricsDashboard::class)->name('metrics.dashboard');
    
    // User Settings
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');
    
    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
