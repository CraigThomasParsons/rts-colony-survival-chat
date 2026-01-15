<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle a traditional POST login submission as a fallback for non-Livewire flows.
     */
    public function postLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => trans('auth.failed'),
            ])->withInput($request->only('email', 'remember'));
        }

        $request->session()->regenerate();

        // Redirect to control panel (matches Livewire intended redirect)
        return redirect()->intended(route('control-panel'));
    }
}
