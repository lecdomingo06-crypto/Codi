<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Services\ActivityCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request, ActivityCalendarService $calendarService): View
    {
        $user = $request->user()->load('streak');
        $timezone = $user->timezone ?: config('app.timezone');
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $calendar = $calendarService->range($user, $today->subDays(83), $today);
        $acceptedSubmissions = $user->submissions()->where('verdict', 'ACCEPTED')->count();
        $recentSubmissions = $user->submissions()->with('exercise')->latest()->limit(4)->get();
        $recentPosts = $user->communityPosts()->latest()->limit(4)->get();

        return view('profile.edit', [
            'user' => $user,
            'timezones' => ['UTC', 'Asia/Manila', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London'],
            'calendar' => $calendar,
            'acceptedSubmissions' => $acceptedSubmissions,
            'submissionCount' => $user->submissions()->count(),
            'communityPostCount' => $user->communityPosts()->count(),
            'communityCommentCount' => $user->communityComments()->count(),
            'recentSubmissions' => $recentSubmissions,
            'recentPosts' => $recentPosts,
            'totalCommunityPosts' => CommunityPost::count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', Rule::in(['UTC', 'Asia/Manila', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London'])],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($validated['avatar']);

        $user->update($validated);

        return back()->with('status', 'Profile updated. Historical activity dates were left unchanged.');
    }
}
