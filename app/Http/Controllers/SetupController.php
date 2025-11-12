<?php

namespace App\Http\Controllers;

use App\Models\SetupState;
use Illuminate\Support\Facades\Auth;

class SetupController extends Controller
{
    public function __invoke()
    {
        if (SetupState::isCompleted()) {
            return redirect()->route(Auth::check() ? 'dashboard' : 'login');
        }

        return view('setup.index');
    }
}
