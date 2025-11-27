<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('control-panel', absolute: false), navigate: true);
    }
}; ?>

<style>
    .login-card {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    .login-card h2 {
        margin: 0;
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    .glass-panel {
        border-radius: 28px;
        padding: 2rem;
        background: rgba(14, 17, 35, 0.92);
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.55);
        border: 1px solid rgba(255,255,255,0.06);
    }
    .login-btn {
        border-radius: 999px;
        padding: 0.9rem 1.8rem;
        font-weight: 600;
    }
</style>

<div class="login-card">
    <!-- Session Status -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <div class="center-align" style="color:#cdd7ff;">
        <h2>Welcome Back</h2>
        <p>Sign in to resume your colony.</p>
    </div>

    <form wire:submit="login" method="POST" action="{{ route('login.post') }}" class="glass-panel">
        @csrf
        <div class="input-field">
            <i class="material-icons prefix">mail</i>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" class="validate" />
            <label for="email">{{ __('Email') }}</label>
            <x-input-error :messages="$errors->get('form.email')" class="red-text text-lighten-3 mt-1" />
        </div>

        <div class="input-field">
            <i class="material-icons prefix">lock</i>
            <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" class="validate" />
            <label for="password">{{ __('Password') }}</label>
            <x-input-error :messages="$errors->get('form.password')" class="red-text text-lighten-3 mt-1" />
        </div>

        <div class="row" style="margin-bottom:0;">
            <label for="remember" class="col s12 m6">
                <p>
                    <label>
                        <input wire:model="form.remember" id="remember" type="checkbox" class="filled-in" />
                        <span>{{ __('Remember me') }}</span>
                    </label>
                </p>
            </label>
            <div class="col s12 m6 right-align">
                @if (Route::has('password.request'))
                    <a class="text-sm" style="color:#b1c5ff;" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="center-align" style="margin-top:1rem;">
            <button type="submit" class="btn-large waves-effect waves-light purple accent-3 login-btn">
                {{ __('Log in') }}
            </button>
        </div>
    </form>
</div>
