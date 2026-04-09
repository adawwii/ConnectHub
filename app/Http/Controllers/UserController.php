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
    public function index(){
        return view('auth.registeration');
    }
    //create user
    public function create(Request $request){
        $this->userService->newUser($request);
        return redirect()->back();
    }
}
