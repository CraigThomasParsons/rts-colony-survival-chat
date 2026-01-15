import './bootstrap';
import { createApp } from 'vue';
import { startFeudalFrontiersGame } from './game/feudal-frontiers';

// Simple Vue boot (expand as needed)
createApp({}).mount('#app');

// Expose start function globally for Blade / Livewire triggers
window.startFeudalFrontiersGame = startFeudalFrontiersGame;

// Optional auto-start if container present (can remove if manual only)
if (document.getElementById('phaser-game')) {
    startFeudalFrontiersGame();
}

