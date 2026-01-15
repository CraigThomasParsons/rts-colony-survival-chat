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

<nav x-data="{ open: false }" class="topnav" style="position: sticky; top: 0; z-index: 50; width: 100%; backdrop-filter: blur(10px); background-color: rgba(15, 20, 36, 0.9);">
    <!-- Primary Navigation Menu -->
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; height: 4rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <!-- Site Title -->
                <a href="{{ route('control-panel') }}" wire:navigate class="title" style="font-size: 1.125rem; font-weight: 600; text-decoration: none;">
                    RTS Colony Survival
                </a>

                <!-- Primary Links -->
                <div style="display: flex; gap: 2rem;">
                    <a href="{{ route('control-panel') }}" wire:navigate class="link" style="text-decoration: none; font-size: 0.95rem;">
                        Control Panel
                    </a>
                </div>
            </div>

            <!-- Top-right Auth / Profile -->
            <div style="display: flex; align-items: center;">
                @php $authUser = auth()->user(); @endphp
                @if (!$authUser)
                    <!-- Login button when logged out -->
                    <a href="{{ route('login') }}" wire:navigate style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; color: white; text-decoration: none; background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); border: 1px solid rgba(255,255,255,0.08);">
                        Log in
                    </a>
                @else
                    <!-- Profile section -->
                    <div style="position: relative; display: inline-block;" x-data="{ dropdownOpen: false }">
                        <button @click="dropdownOpen = !dropdownOpen" style="display: flex; align-items: center; gap: 0.5rem; background: none; border: none; cursor: pointer; padding: 0.25rem;">
                            <span class="link" style="font-size: 0.875rem;">{{ $authUser->name }}</span>
                            <img src="{{ $authUser->avatar_url ?? asset('images/avatar-default.png') }}" alt="avatar" class="avatar" style="height: 2rem; width: 2rem; border-radius: 50%;" />
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
