<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect("/", navigate: true);
    }
};
?>

<nav x-data="{ open: false }" class="topnav" style="backdrop-filter: blur(10px); background-color: rgba(12, 14, 22, 0.92);">
    <!-- Primary Navigation Menu -->
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; height: 4rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <!-- Site Title -->
                <a href="{{ route('control-panel') }}" wire:navigate class="title" style="font-size: 1.125rem; font-weight: 600; text-decoration: none;">
                    RTS Colony Survival
                </a>

                <!-- Primary Links -->
                <div style="display: none; gap: 2rem; @media (min-width: 640px) { display: flex; }">
                    <a href="{{ route('control-panel') }}" class="link" style="text-decoration: none;">Dashboard</a>
                    <a href="{{ route('game.index') }}" class="link" style="text-decoration: none;">Games</a>
                    <a href="{{ route('map.index') }}" class="link" style="text-decoration: none;">Map Generator</a>
                </div>
            </div>

            <!-- User Dropdown -->
            <div style="display: none; @media (min-width: 640px) { display: block; }">
                @if($authUser = auth()->user())
                    <div x-data="{ dropdownOpen: false }" style="position: relative;">
                        <button @click="dropdownOpen = !dropdownOpen" style="display: flex; align-items: center; gap: 0.5rem; background: none; border: none; cursor: pointer;">
                            <img src="{{ $authUser->avatar_url ?? 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($authUser->email))) . '?d=mp' }}" alt="Avatar" class="avatar" style="width: 2.5rem; height: 2.5rem; border-radius: 9999px;">
                            <span style="color: #d1d5db;">{{ $authUser->name }}</span>
                            <svg style="width: 1.25rem; height: 1.25rem; color: #9ca3af;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-cloak style="position: absolute; right: 0; margin-top: 0.5rem; width: 14rem; z-index: 50; border-radius: 0.375rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3); padding: 0.75rem; background: linear-gradient(180deg,#0f1424 0%, #0b1020 100%); border: 1px solid rgba(255,215,0,0.18);">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #f3f4f6;">{{ $authUser->name }}</div>
                            <div style="font-size: 0.75rem; color: #9ca3af;">{{ $authUser->email }}</div>
                            <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                                <a href="{{ route('profile') }}" wire:navigate style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; background: #111827; color: #e5e7eb; border: 1px solid rgba(255,215,0,0.18); text-decoration: none;">Profile</a>
                                <button wire:click="logout" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; color: white; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border: none; cursor: pointer;">Log Out</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>
