<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
///////////////////    LOGIN METHODS   /////////////////
    public function getLogin()
    {
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt(['username' => $request['username'], 'password' => $request['password']]))
        {
           
            return redirect('/home');
        }
        else return redirect()->back()->with('info', "Incorrect username or password");
    }
/////////////////////    LOGOUT     ////////////////////
    public function getLogout()
    {
        Auth::guard('web')->logout();
        return redirect('login');
    }

}
