<?php

namespace App\Support\Branding;

use App\Domain\Hotel\Hotel;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\File;

class HotelLogo
{
    public const DEFAULT_PATH = 'images/logo-full.png';

    public function currentAsset(): string
    {
        return $this->assetFor($this->currentHotel());
    }

    public function assetFor(?Hotel $hotel): string
    {
        return asset($this->pathFor($hotel));
    }

    public function dataUriFor(?Hotel $hotel): ?string
    {
        $path = public_path($this->pathFor($hotel));

        if (! File::exists($path)) {
            return null;
        }

        $mime = File::mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(File::get($path));
    }

    public function pathFor(?Hotel $hotel): string
    {
        $settingsPath = $hotel?->settings['logo_path'] ?? null;
        if ($settingsPath && $this->publicFileExists($settingsPath)) {
            return ltrim($settingsPath, '/');
        }

        $codePath = $this->pathForHotelCode($hotel?->code);
        if ($codePath && $this->publicFileExists($codePath)) {
            return $codePath;
        }

        if ($this->publicFileExists(self::DEFAULT_PATH)) {
            return self::DEFAULT_PATH;
        }

        return 'assets/images/logo-full.png';
    }

    private function currentHotel(): ?Hotel
    {
        return app(TenantContext::class)->hotel() ?? auth()->user()?->hotel;
    }

    private function pathForHotelCode(?string $code): ?string
    {
        return match (strtoupper((string) $code)) {
            'ORIA' => 'assets/images/logo-oria-wide.png',
            default => null,
        };
    }

    private function publicFileExists(string $path): bool
    {
        return File::exists(public_path(ltrim($path, '/')));
    }
}
