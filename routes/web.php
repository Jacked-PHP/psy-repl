<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
    return view('home');
})->name('home');

Route::get('login', function() {
    $user = User::first();
    if (null === $user) {
        $user = User::factory()->create();
    }
    Auth::login($user);
    return redirect()->route('dashboard');
})->name('login');

Route::post('logout', function() {
    $user = User::firstOrFail();
    $user->purgeMeta();
    $user->shells()->delete();
    $user->delete();
    return redirect()->route('home');
})->name('logout');

Route::get('registration', function() {
    return redirect()->route('home');
})->name('register');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');
});
