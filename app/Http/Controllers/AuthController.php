<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\Login;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Login $request)
    {
        if (Auth::attempt($request->validated(), true)) {
            $request->session()->regenerate();
            return ['message' => 'ur logged in'];
        }
        abort(404, 'Account not found');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return ['message' => 'logged out'];
    }

    public function me(Request $request)
    {
        return new UserResource($request->user())->additional(['with_permissions' => true]);
    }
}
