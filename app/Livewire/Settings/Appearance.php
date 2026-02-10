<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Appearance Settings')]
class Appearance extends Component
{
    use WithFileUploads;

    #[Validate('nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048')]
    public $adminLogo;

    #[Validate('nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048')]
    public $publicLogo;

    #[Validate('nullable|file|mimes:ico,png,svg|max:1024')]
    public $favicon;

    public $themeColor;

    #[Validate('nullable|string|max:255')]
    public $siteName;

    #[Validate('nullable|string|max:5000')]
    public $aboutUs;

    public $currentAdminLogo;

    public $currentPublicLogo;

    public $currentFavicon;

    public $currentSiteName;

    public function mount(): void
    {
        $this->currentAdminLogo = Setting::get('admin_logo');
        $this->currentPublicLogo = Setting::get('public_logo');
        $this->currentFavicon = Setting::get('favicon');
        $this->currentSiteName = Setting::get('site_name', config('app.name'));
        $this->siteName = $this->currentSiteName;
        $this->themeColor = Setting::get('theme_color', '#009BDE');
        $this->aboutUs = Setting::get('about_us');
    }

    public function save(): void
    {
        $this->validate([
            'adminLogo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'publicLogo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png,svg|max:1024',
            'siteName' => 'nullable|string|max:255',
            'themeColor' => 'nullable|string|max:7',
            'aboutUs' => 'nullable|string|max:5000',
        ]);

        // Handle admin logo upload
        if ($this->adminLogo) {
            // Delete old logo if exists
            if ($this->currentAdminLogo && \Storage::disk('public')->exists($this->currentAdminLogo)) {
                \Storage::disk('public')->delete($this->currentAdminLogo);
            }

            $path = $this->adminLogo->store('logos', 'public');
            Setting::set('admin_logo', $path);
            $this->currentAdminLogo = $path;
            $this->adminLogo = null;
        }

        // Handle public logo upload
        if ($this->publicLogo) {
            // Delete old logo if exists
            if ($this->currentPublicLogo && \Storage::disk('public')->exists($this->currentPublicLogo)) {
                \Storage::disk('public')->delete($this->currentPublicLogo);
            }

            $path = $this->publicLogo->store('logos', 'public');
            Setting::set('public_logo', $path);
            $this->currentPublicLogo = $path;
            $this->publicLogo = null;
        }

        // Handle favicon upload
        if ($this->favicon) {
            // Delete old favicon if exists
            if ($this->currentFavicon && \Storage::disk('public')->exists($this->currentFavicon)) {
                \Storage::disk('public')->delete($this->currentFavicon);
            }

            $path = $this->favicon->store('logos', 'public');
            Setting::set('favicon', $path);
            $this->currentFavicon = $path;
            $this->favicon = null;
        }

        // Handle site name
        if ($this->siteName !== $this->currentSiteName) {
            Setting::set('site_name', $this->siteName);
            $this->currentSiteName = $this->siteName;
        }

        // Handle theme color
        if ($this->themeColor) {
            Setting::set('theme_color', $this->themeColor);
        }

        // Handle about us content
        Setting::set('about_us', $this->aboutUs ?? '');

        $this->dispatch('notify', type: 'success', message: 'Appearance settings updated successfully.');
    }

    public function removeAdminLogo(): void
    {
        if ($this->currentAdminLogo && \Storage::disk('public')->exists($this->currentAdminLogo)) {
            \Storage::disk('public')->delete($this->currentAdminLogo);
        }

        Setting::forget('admin_logo');
        $this->currentAdminLogo = null;

        $this->dispatch('notify', type: 'success', message: 'Admin logo removed successfully.');
    }

    public function removePublicLogo(): void
    {
        if ($this->currentPublicLogo && \Storage::disk('public')->exists($this->currentPublicLogo)) {
            \Storage::disk('public')->delete($this->currentPublicLogo);
        }

        Setting::forget('public_logo');
        $this->currentPublicLogo = null;

        $this->dispatch('notify', type: 'success', message: 'Public logo removed successfully.');
    }

    public function removeFavicon(): void
    {
        if ($this->currentFavicon && \Storage::disk('public')->exists($this->currentFavicon)) {
            \Storage::disk('public')->delete($this->currentFavicon);
        }

        Setting::forget('favicon');
        $this->currentFavicon = null;

        $this->dispatch('notify', type: 'success', message: 'Favicon removed successfully.');
    }
}
