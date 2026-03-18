<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(): View
    {
        $user = auth()->user()->loadMissing('employee.position');

        return view('profile.show', compact('user'));
    }
}
