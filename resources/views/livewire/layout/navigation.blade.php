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

<nav x-data="{ open: false }" class="topnav">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-4">
                <!-- Site Title (replaces logo) -->
                <a href="{{ route('control-panel') }}" wire:navigate class="title text-lg font-semibold text-gray-800 dark:text-gray-100">
                    RTS Colony Survival
                </a>

                <!-- Primary Links -->
                <div class="hidden space-x-8 sm:-my-px sm:flex">
                    <x-nav-link :href="route('control-panel')" :active="request()->routeIs('control-panel')" wire:navigate>
                        {{ __('Control Panel') }}
                    </x-nav-link>
                    <x-nav-link :href="route('game.load')" :active="request()->routeIs('game.load')" wire:navigate>
                        {{ __('Load Games') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Top-right Auth / Profile -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @php $authUser = auth()->user(); @endphp
                @if (!$authUser)
                    <!-- Login button when logged out -->
                    <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); border: 1px solid rgba(255,255,255,0.08);">
                        {{ __('Log in') }}
                    </a>
                @else
                    <!-- Profile avatar with hover info -->
                    <div class="relative group">
                        <a href="{{ route('profile') }}" wire:navigate class="inline-flex items-center gap-2">
                            <span class="text-sm" style="color: #d6d9ff;">{{ $authUser->name }}</span>
                            <img src="{{ $authUser->avatar_url ?? asset('images/avatar-default.png') }}" alt="avatar" class="avatar h-8 w-8 rounded-full" />
                        </a>
                        <div class="absolute right-0 mt-2 w-56 z-50 hidden group-hover:block rounded-md shadow-lg p-3" style="background: linear-gradient(180deg,#0f1424 0%, #0b1020 100%); border: 1px solid rgba(255,215,0,0.18);">
                            <div class="text-sm font-semibold" style="color: #f3f4f6;">{{ $authUser->name }}</div>
                            <div class="text-xs" style="color: #9ca3af;">{{ $authUser->email }}</div>
                            <div class="mt-2 flex gap-2">
                                <a href="{{ route('profile') }}" wire:navigate class="text-xs px-2 py-1 rounded" style="background: #111827; color: #e5e7eb; border: 1px solid rgba(255,215,0,0.18);">{{ __('Profile') }}</a>
                                <button wire:click="logout" class="text-xs px-2 py-1 rounded text-white" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">{{ __('Log Out') }}</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('control-panel')" :active="request()->routeIs('control-panel')" wire:navigate>
                {{ __('Control Panel') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                @php /* ensure $authUser is available in template scope */ $authUser = $authUser ?? auth()->user(); @endphp
                <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => $authUser ? $authUser->name : 'Guest']) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ $authUser ? $authUser->email : '' }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
