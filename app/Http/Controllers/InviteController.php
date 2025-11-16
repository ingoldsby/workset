<?php

namespace App\Http\Controllers;

use App\Actions\SendInviteAction;
use App\Http\Requests\SendInviteRequest;
use App\Mail\InviteMail;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InviteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invite::class);

        $query = Invite::with(['inviter', 'personalTrainer'])
            ->latest();

        // PTs can only see their own invites
        if ($request->user()->isPt()) {
            $query->where('invited_by', $request->user()->id);
        }

        $invites = $query->paginate(15);

        return view('invites.index', compact('invites'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Invite::class);

        $personalTrainers = User::query()
            ->where('role', 'pt')
            ->orderBy('name')
            ->get();

        return view('invites.create', compact('personalTrainers'));
    }

    public function store(SendInviteRequest $request, SendInviteAction $action): RedirectResponse
    {
        $this->authorize('create', Invite::class);

        $invite = $action->execute(
            inviter: $request->user(),
            email: $request->input('email'),
            role: $request->enum('role', \App\Enums\Role::class),
            ptId: $request->input('pt_id'),
        );

        return to_route('invites.index')
            ->with('success', "Invitation sent to {$invite->email}");
    }

    public function destroy(Request $request, Invite $invite): RedirectResponse
    {
        $this->authorize('delete', $invite);

        $invite->delete();

        return to_route('invites.index')
            ->with('success', 'Invitation deleted successfully');
    }

    public function resend(Request $request, Invite $invite, SendInviteAction $action): RedirectResponse
    {
        // Check basic authorization first (user can manage invites)
        if (! $request->user()->role->canInvite()) {
            abort(403);
        }

        // Check ownership
        if (! $request->user()->isAdmin() && $invite->invited_by !== $request->user()->id) {
            abort(403);
        }

        // Check if invite can be resent
        if (! $invite->isPending()) {
            return back()->with('error', 'This invitation cannot be resent');
        }

        // Update expiry
        $invite->update([
            'expires_at' => now()->addDays(7),
        ]);

        // Resend the email
        Mail::to($invite->email)->send(new InviteMail($invite, $invite->inviter));

        return to_route('invites.index')
            ->with('success', "Invitation resent to {$invite->email}");
    }
}
