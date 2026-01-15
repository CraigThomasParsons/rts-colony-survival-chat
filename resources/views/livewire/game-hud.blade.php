<div class="space-y-4">
    {{-- Resources bar --}}
    <div class="space-y-1">
        <div>Wood: {{ $wood }}</div>
        <div>Stone: {{ $stone }}</div>
        <div>Gold: {{ $gold }}</div>
    </div>

    {{-- Menu buttons --}}
    <div class="space-y-3">
        <button wire:click="newGame" class="w-full">
            <img src="/ui/buttons/wood/new_game.png" alt="New Game" class="w-full">
        </button>

        <button wire:click="loadGame" class="w-full">
            <img src="/ui/buttons/wood/load_game.png" alt="Load Game" class="w-full">
        </button>

        <button wire:click="loadGame" class="w-full">
            <img src="/ui/buttons/wood/settings.png" alt="Settings" class="w-full">
        </button>

        <button class="w-full">
            <img src="/ui/buttons/wood/sign_out.png" alt="Sign Out" class="w-full">
        </button>
    </div>
</div>
