<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'pollData' => $this->getPollData(),
            'userStats' => Inertia::defer(fn () => $this->getUserStats()),
        ]);
    }

    public function poll()
    {
        return response()->json([
            'pollData' => $this->getPollData(),
        ]);
    }

    private function getPollData()
    {
        return [
            'currentTime' => Carbon::now()->format('Y-m-d H:i:s'),
            'randomNumber' => rand(1, 100),
            'activeUsers' => rand(50, 200), // Simulated active user count
        ];
    }

    private function getUserStats()
    {
        // Simulate a slow query
        sleep(2);

        return [
            'totalUsers' => User::count(),
            'newUsersToday' => User::whereDate('created_at', Carbon::today())->count(),
            'activeUsersLastWeek' => User::where('last_active_at', '>=', now()->subWeek())->count(),
        ];
    }
}
