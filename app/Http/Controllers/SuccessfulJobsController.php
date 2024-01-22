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
    public function statistics(): \Illuminate\Http\JsonResponse
    {
        $jobs = SuccessfulJobs::select(
            'tool_name',
            \DB::raw('COUNT(*) as count'),
            \DB::raw('AVG(time) as avg_time'),
            \DB::raw('100 * SUM(CASE WHEN is_guest = 1 THEN 1 ELSE 0 END) / COUNT(*) as guest_percentage')
        )
            ->groupBy('tool_name')
            ->orderBy('count', 'desc')
            ->get();
    
        if ($jobs->isEmpty()) {
            return abort(404);
        }
    
        return response()->json(['data' => $jobs]);
    }
    public function index(): \Illuminate\Http\JsonResponse
    {
        $jobs = SuccessfulJobs::orderBy('updated_at', 'desc')->get();
    
        if ($jobs->isEmpty()) {
            return abort(404);
        }
    
        return response()->json(['data' => $jobs]);
    }
}