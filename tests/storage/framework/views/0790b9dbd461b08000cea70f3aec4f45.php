<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div class="min-h-screen flex items-center justify-center">
    <form wire:submit.prevent="login" class="p-6 rounded-xl bg-slate-900 text-white space-y-4 w-96">
        <h1 class="text-xl font-bold">Login</h1>

        <input
            wire:model="email"
            type="email"
            placeholder="Email"
            class="w-full p-2 rounded bg-slate-800"
            required
        >

        <input
            wire:model="password"
            type="password"
            placeholder="Password"
            class="w-full p-2 rounded bg-slate-800"
            required
        >

        <button class="w-full p-2 bg-blue-600 rounded">
            Login
        </button>
    </form>
</div><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/livewire/pages/auth/login.blade.php ENDPATH**/ ?>