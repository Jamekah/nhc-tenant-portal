<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = auth()->user();

        if ($user->hasRole('client')) {
            return new RedirectResponse(route('portal.dashboard'));
        }

        return new RedirectResponse(filament()->getUrl());
    }
}
