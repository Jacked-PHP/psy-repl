<?php

namespace App\Http\Controllers;

use App\Models\Shell;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('tinker.editor', [
            'shellId' => auth()->user()->shells()->first()?->id ?? null,
        ]);
    }
}
