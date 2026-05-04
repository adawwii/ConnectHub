<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }
    //
    public function show(){
        return view('auth.registeration');
    }
    //show login page
    public function login(){
        return view('auth.login');
    }
    //create user
    public function create(Request $request){
        $this->userService->newUser($request);
        return redirect()->route('chat-view');
    }
    //authenticate user
    public function authenticate(Request $request){
        $attempt = $this->userService->authUser($request);
        return redirect()->route('chat-view');
    }

    public function logout(Request $request){
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login-user');
    }
}
