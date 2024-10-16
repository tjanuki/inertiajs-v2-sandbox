<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'users' => User::all()
        ]);
    }
}
