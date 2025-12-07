<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Redirects\DashboardRedirector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(Request $request)
    {
        $g = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $g->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $g->getName() ?: $g->getNickname() ?: 'Google User',
                'email' => $g->getEmail(),
                'password' => bcrypt(str()->random(32)),
            ]);
            $user->syncRoles(['student_free']);
        }

        Auth::login($user, true);

        $locale = $request->route('locale') ?? app()->getLocale();

        return redirect()->intended(
            DashboardRedirector::resolve($user, $locale)
        );
    }
}
