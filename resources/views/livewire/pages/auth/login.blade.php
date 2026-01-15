<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $showPassword = false;

    public function login()
    {
        // Validate input
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Trim any whitespace
        $email = trim($this->email);
        $password = $this->password;

        // Debug logging
        \Log::info('Login attempt', [
            'email' => $email,
            'password_length' => strlen($password),
            'password_first_char' => substr($password, 0, 1),
        ]);

        $result = Auth::attempt(['email' => $email, 'password' => $password]);
        
        \Log::info('Login result', ['success' => $result]);

        if (! $result) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials. Please check your email and password.',
            ]);
        }

        session()->regenerate();

        return redirect()->intended(route('control-panel'));
    }
};
?>

<div class="min-h-screen flex items-center justify-center px-4">
    <form wire:submit.prevent="login" class="p-12 rounded-xl bg-slate-900 text-white space-y-8 w-full max-w-3xl">
        <h1 class="text-6xl font-bold">Login</h1>

        @if (session('status'))
            <div class="p-6 rounded bg-green-600 text-white text-3xl">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-6 rounded bg-red-600 text-white text-3xl">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="space-y-3">
            <label class="block text-3xl font-medium">Email</label>
            <input
                wire:model="email"
                type="email"
                placeholder="your@email.com"
                class="w-full p-6 rounded-lg bg-slate-800 text-3xl placeholder-slate-500"
                required
            >
        </div>

        <div class="space-y-3">
            <label class="block text-3xl font-medium">Password</label>
            <div class="relative">
                <input
                    wire:model="password"
                    type="{{ $showPassword ? 'text' : 'password' }}"
                    placeholder="Your password"
                    class="w-full p-6 pr-20 rounded-lg bg-slate-800 text-3xl placeholder-slate-500"
                    required
                >
                <button
                    type="button"
                    wire:click="$toggle('showPassword')"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-4xl"
                >
                    @if($showPassword)
                        👁️
                    @else
                        👁️‍🗨️
                    @endif
                </button>
            </div>
        </div>

        <button class="w-full p-6 bg-blue-600 rounded-lg hover:bg-blue-700 transition text-3xl font-bold">
            Login
        </button>
    </form>
</div>

