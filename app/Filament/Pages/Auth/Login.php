<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    /**
     * Custom heading for the NHC login page.
     */
    public function getHeading(): string|Htmlable
    {
        return 'NHC Tenant Portal';
    }

    /**
     * Custom subheading with NHC branding.
     */
    public function getSubheading(): string|Htmlable|null
    {
        return 'National Housing Corporation — Papua New Guinea';
    }
}
