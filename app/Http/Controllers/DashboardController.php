<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'pollData' => $this->getPollData(),
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
}
