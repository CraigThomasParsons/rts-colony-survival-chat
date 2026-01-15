<div class="flex h-screen bg-gray-900 text-white">
    {{-- Left side: menu / HUD --}}
    <div class="w-64 p-4 space-y-4 bg-gray-950/80">
        @livewire('game-hud')
    </div>

    {{-- Right side: Phaser canvas --}}
    <div class="flex-1 flex items-center justify-center">
        <div id="game-container" wire:ignore></div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.startFeudalFrontiersGame) {
            window.startFeudalFrontiersGame('game-container');
        }
    });
</script>
@endpush