<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        logger(__METHOD__.':debug-log: request()->input() '.var_export(request()->input(), true));
        $page = $request->input('page', 1);
        $perPage = 10;

        return Inertia::render('Dashboard', [
            'pollData' => $this->getPollData(),
            'userStats' => Inertia::defer(fn () => $this->getUserStats()),
            'users' => Inertia::merge($this->getUsers($page, $perPage)),
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

    private function getUsers($page, $perPage)
    {
        return User::select('id', 'name', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
