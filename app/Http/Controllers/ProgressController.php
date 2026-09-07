<?php

namespace App\Http\Controllers;

use App\Services\ActivityCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function show(Request $request, ActivityCalendarService $calendarService): View
    {
        $user = $request->user()->load('streak');

        return view('progress.show', [
            'user' => $user,
            'calendar' => $calendarService->yearlyCalendar($user),
            'masteries' => DB::table('user_concept_masteries')
                ->join('concepts', 'concepts.id', '=', 'user_concept_masteries.concept_id')
                ->where('user_id', $user->id)
                ->select('concepts.name', 'mastery_score', 'last_practiced_at')
                ->orderByDesc('mastery_score')
                ->get(),
        ]);
    }

    public function streak(Request $request): JsonResponse
    {
        return response()->json($request->user()->streak()->firstOrCreate(['user_id' => $request->user()->id]));
    }

    public function calendar(Request $request, ActivityCalendarService $calendarService): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);

        $from = CarbonImmutable::parse($validated['from'], $request->user()->timezone);
        $to = CarbonImmutable::parse($validated['to'], $request->user()->timezone);

        abort_if($from->diffInDays($to) > 370, 422, 'Calendar range cannot exceed 370 days.');

        return response()->json($calendarService->range($request->user(), $from, $to));
    }
}
