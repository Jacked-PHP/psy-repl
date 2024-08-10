<?php

namespace App\Http\Controllers;

use App\Models\Shell;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard', [
            'shells' => auth()->user()->shells ?? collect(),
        ]);
    }
}
