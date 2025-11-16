<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * Note: Direct registration is disabled. Users must be invited.
     */
    public function create(): RedirectResponse
    {
        return to_route('login')
            ->with('info', 'Registration is by invitation only. Please contact an administrator.');
    }

    /**
     * Handle an incoming registration request.
     *
     * Note: Direct registration is disabled. Users must be invited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        return to_route('login')
            ->with('info', 'Registration is by invitation only. Please contact an administrator.');
    }
}
