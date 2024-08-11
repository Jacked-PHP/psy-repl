<?php

use App\Enums\ShellMeta;
use App\Enums\SshPasswordType;
use App\Helpers\ShellHelper;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShellController;
use App\Livewire\Dashboard;
use App\Models\Shell;
use Illuminate\Http\Response;
use phpseclib3\Crypt\RSA\PrivateKey;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use phpseclib3\Net\SSH2;

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
    $user = User::first();
    if (null === $user) {
        return view('home');
    }
    Auth::login($user);
    return redirect()->route('dashboard');
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
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/shell/{shell?}', [ShellController::class, 'index'])
        ->name('shells.show');
});

Route::get('execute-remote/{shell}', [ShellController::class, 'executeRemoteCode'])
    ->name('execute-remote');
