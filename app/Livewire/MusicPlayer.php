<?php

namespace App\Livewire;

use App\Models\MusicTrack;
use Livewire\Component;

class MusicPlayer extends Component
{
    public $currentTrackId = null;
    public $isPlaying = false;
    public $hasMusicConsole = true; // Placeholder for game state integration

    public function getTracksProperty()
    {
        return MusicTrack::orderBy('artist')
            ->orderBy('album')
            ->orderBy('track_number')
            ->get();
    }

    public function getCurrentTrackProperty()
    {
        return $this->currentTrackId 
            ? $this->tracks->firstWhere('id', $this->currentTrackId)
            : null;
    }

    public function play($trackId)
    {
        $this->currentTrackId = $trackId;
        $this->isPlaying = true;
        // Dispatch browser event handled by Alpine
        $this->dispatch('play-track', url: route('music.stream', $trackId));
    }
    
    public function render()
    {
        return view('livewire.music-player');
    }
}
