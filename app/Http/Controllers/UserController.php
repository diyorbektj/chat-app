<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $user = User::query()->where('id', "!=", auth()->id())->get();
        return $this->success($user);
    }
}
