<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\AcceptInviteRequest;
use App\Models\Invite;
use App\Models\PtAssignment;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InviteAcceptanceController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invite = Invite::where('token', $token)->first();

        if (! $invite) {
            return to_route('login')
                ->with('error', 'Invalid invitation link');
        }

        if ($invite->isAccepted()) {
            return to_route('login')
                ->with('error', 'This invitation has already been accepted');
        }

        if ($invite->isExpired()) {
            return to_route('login')
                ->with('error', 'This invitation has expired');
        }

        return view('auth.accept-invite', compact('invite'));
    }

    public function store(AcceptInviteRequest $request, string $token): RedirectResponse
    {
        $invite = Invite::where('token', $token)->first();

        if (! $invite) {
            return to_route('login')
                ->with('error', 'Invalid invitation link');
        }

        if ($invite->isAccepted()) {
            return to_route('login')
                ->with('error', 'This invitation has already been accepted');
        }

        if ($invite->isExpired()) {
            return to_route('login')
                ->with('error', 'This invitation has expired');
        }

        // Create user and accept invite in a transaction
        $user = DB::transaction(function () use ($request, $invite) {
            // Create the user
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $invite->email,
                'password' => Hash::make($request->input('password')),
                'role' => $invite->role,
                'timezone' => $request->input('timezone', config('app.timezone')),
            ]);

            // Mark invite as accepted
            $invite->update([
                'accepted_at' => now(),
            ]);

            // If this is a member invite with a PT, create PT assignment
            if ($invite->role === Role::Member && $invite->pt_id) {
                PtAssignment::create([
                    'pt_id' => $invite->pt_id,
                    'member_id' => $user->id,
                    'assigned_at' => now(),
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard')
            ->with('success', 'Welcome to Workset! Your account has been created successfully.');
    }
}
