<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the student user's profile.
     */
    public function show(Request $request): View
    {
        $user = $request->user()->load(['student.university']);

        return view('profile.show', [
            'user' => $user,
            'student' => $user->student,
            'university' => $user->university,
        ]);
    }

    /**
     * Show the form for editing allowed profile attributes.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['student.university']);

        Gate::authorize('update', $user);

        return view('profile.edit', [
            'user' => $user,
            'student' => $user->student,
            'university' => $user->university,
        ]);
    }

    /**
     * Update the allowed profile fields.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        Gate::authorize('update', $user);

        // Handle avatar upload if provided
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        // Update allowed display name
        $user->name = $request->validated('name');
        $user->save();

        return redirect()->route('profile')->with('status', 'Profil berhasil diperbarui.');
    }
}
