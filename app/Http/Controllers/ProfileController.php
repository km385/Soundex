<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Song;
use App\Models\TemporarySong;
use App\Models\SuccessfulJobs;
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function getAllUsers()
    {
        $users = User::select('id', 'nickname', 'email', 'storage_used', 'files_stored', 'created_at')->get();
        $users->each(function ($user) {
            $toolUsageCount = SuccessfulJobs::where('user_id', $user->id)->count();
            $user->tool_usage = $toolUsageCount;
        });
        return response()->json(['users' => $users]);
    }

    public function updateUsers($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        Song::where('user_id', $user->id)->delete();
        TemporarySong::Where('user_id', $user->id)->delete();
        $user->storage_used = 0;
        $user->files_stored = 0;

        $user->save();

        $users = User::select('id', 'nickname', 'email', 'storage_used', 'files_stored', 'created_at')->get();
        $users->each(function ($user) {
            $toolUsageCount = SuccessfulJobs::where('user_id', $user->id)->count();
            $user->tool_usage = $toolUsageCount;
        });
        return response()->json(['users' => $users]);
    }

    public function destroyUsers($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();

        $users = User::select('id', 'nickname', 'email', 'storage_used', 'files_stored', 'created_at')->get();
        $users->each(function ($user) {
            $toolUsageCount = SuccessfulJobs::where('user_id', $user->id)->count();
            $user->tool_usage = $toolUsageCount;
        });
        return response()->json(['users' => $users]);
    }
}
