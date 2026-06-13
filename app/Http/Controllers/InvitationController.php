<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:holding_admin,company_admin,dept_manager,employee'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $invitation = Invitation::create([
            'email' => $validated['email'],
            'token' => Str::random(64),
            'role' => $validated['role'],
            'company_id' => $validated['company_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays(7),
        ]);

        return redirect()->back()->with('success', __('invitation.invitation_sent'));
    }

    public function accept(string $token)
    {
        $invitation = Invitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        if (Auth::check()) {
            $user = Auth::user();
            $user->update(['department_id' => $invitation->department_id]);
            $user->assignRole($invitation->role);
            $invitation->update(['accepted_at' => now()]);
            return redirect('/dashboard')->with('success', __('invitation.accept'));
        }

        return Inertia::render('Auth/AcceptInvitation', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function register(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'department_id' => $invitation->department_id,
            'locale' => app()->getLocale(),
        ]);

        $user->assignRole($invitation->role);
        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
