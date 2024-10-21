<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        logger(__METHOD__.':debug-log: request()->input() '.var_export(request()->input(), true));
        $page = $request->input('page', 1);
        $perPage = 10;

        return Inertia::render('Users', [
            'users' => Inertia::merge($this->getUsers($page, $perPage)),
        ]);
    }


    private function getUsers($page, $perPage)
    {
        return User::select('id', 'name', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
