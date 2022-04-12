<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

Route::middleware('guest')->group(function ()
{
    Route::get('/', '\App\Http\Controllers\UserController@getLogin');
    Route::get('/login', '\App\Http\Controllers\UserController@getLogin')
        ->name('login');
    Route::post('/login', '\App\Http\Controllers\UserController@postLogin');
});

Route::middleware('auth:web')->group(function ()
{

    // Route::get('/home', '\App\Http\Controllers\HomeController@getHome');
    

    Route::get('/home', function ()
    {
        
        return view('home');
    });
    Route::get('/Add-coin', function ()
    {
        
        return view('add_coin');
    });
    Route::post('add_coin','\App\Http\Controllers\UserController@postAddCoin');



    

    

});



