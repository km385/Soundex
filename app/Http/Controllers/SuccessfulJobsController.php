<?php

namespace App\Http\Controllers;

use App\Models\SuccessfulJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuccessfulJobsController extends Controller
{
    public function show(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if(!$user) {
            abort(404);
        }

        $jobs = SuccessfulJobs::select('tool_name', \DB::raw('COUNT(*) as count'))
            ->where('user_id', $user->id)
            ->groupBy('tool_name')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        if (!$jobs) {
            return abort(404);
        }

        return response()->json(['data' => $jobs]);
    }
}
