<?php

namespace App\Services;

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserService
{
    protected $user;
    /**
     * Create a new class instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    //create user
    public function newUser(Request $request){
        //validate form data
        $formData=$request->validate([
            'name'=>['required','min:3','max:255'],
            'email'=>['required','email',Rule::unique('users','email')],
            'password'=>['required','min:3','max:255','confirmed']
        ]);

        //Hashing the password
        $formData['password']=bcrypt($formData['password']);

        //storing the user data in database
        $user = $this->user->create($formData);

        //firing mail event
        event(new UserRegistered($user));
        
        //login
        auth()->login($user);
        
        //return to the controller
        return;
    }
    //authenticate user
    public function authUser(Request $request){
        //form validation
        $formData=$request->validate([
            'email'=>'required||email',
            'password'=>'required'
        ]);
        
        //remember me 
        $rememberMe = $request->boolean('remember');

        //attempt to login
        if(auth()->attempt($formData,$rememberMe))
            {
                $request->session()->regenerate();
                $attempt = auth()->user();
                //attempt success
                return $attempt;
            }
            return $attempt = false;

    }
    
}
