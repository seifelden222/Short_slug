<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UpdateController;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::view('/register','auth.register')->name('register');
Route::view('/login','auth.login')->name('login');

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);

Route::group(['middleware' => 'auth'], function () {
    Route::view('index','index')->name('index');
    Route::patch('/index', [UpdateController::class,'update'])->name('update');
    Route::view('/reset_password','auth.reset_password')->name('reset_password.view');
    Route::post('/reset_password', [UpdateController::class,'reset_password'])->name('reset_password');
    Route::post('/logout', [LoginController::class,'logout'])->name('logout');
});

