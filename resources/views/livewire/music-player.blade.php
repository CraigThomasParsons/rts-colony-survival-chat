<div 
    x-data="{ 
        audio: new Audio(), 
        isPlaying: @entangle('isPlaying'),
        progress: 0,
        init() {
            this.audio.addEventListener('ended', () => {
                this.isPlaying = false;
            });
            this.audio.addEventListener('play', () => { this.isPlaying = true; });
            this.audio.addEventListener('pause', () => { this.isPlaying = false; });
            this.audio.addEventListener('timeupdate', () => {
                if (this.audio.duration) {
                    this.progress = (this.audio.currentTime / this.audio.duration) * 100;
                }
            });
        }
    }"
    @play-track.window="
        audio.src = $event.detail.url; 
        audio.play();
    "
    class="fixed bottom-4 right-4 bg-gray-900 text-white p-4 rounded-lg shadow-xl w-80 z-50 border border-gray-700"
    x-show="$wire.hasMusicConsole"
    x-transition
    style="display: none;"
>
    <!-- Header -->
    <div class="flex justify-between items-center mb-3 border-b border-gray-700 pb-2">
        <h3 class="font-bold text-base text-gray-200">Music Console</h3>
        <span class="text-xs text-green-500 font-mono tracking-widest">ONLINE</span>
    </div>

    <!-- Current Track Display -->
    <div class="mb-4 bg-black/50 p-2 rounded border border-gray-800">
        @if($this->currentTrack)
            <div class="text-sm font-semibold text-blue-300 truncate">{{ $this->currentTrack->title }}</div>
            <div class="text-xs text-gray-400 truncate">{{ $this->currentTrack->artist }} - {{ $this->currentTrack->album }}</div>
        @else
            <div class="text-xs text-gray-500 italic">Select a track...</div>
        @endif
        
        <!-- Progress Bar -->
        <div class="w-full bg-gray-700 h-1 mt-2 rounded-full overflow-hidden">
            <div class="bg-blue-500 h-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
        </div>
    </div>

    <!-- Controls -->
    <div class="flex justify-center gap-3 mb-4">
        <button class="text-gray-400 hover:text-white" @click="audio.currentTime = 0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
        </button>
        <button 
            class="px-6 py-1 bg-gradient-to-r from-blue-600 to-blue-500 rounded-full hover:from-blue-500 hover:to-blue-400 shadow-lg transform transition active:scale-95"
            @click="isPlaying ? audio.pause() : audio.play()"
        >
            <span x-show="!isPlaying" class="text-sm font-bold">PLAY</span>
            <span x-show="isPlaying" class="text-sm font-bold">PAUSE</span>
        </button>
         <!-- Forward button (optional) -->
         <!-- <button class="text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg></button> -->
    </div>

    <!-- Playlist -->
    <div class="space-y-1 max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-900 border-t border-gray-800 pt-2">
        @foreach($this->tracks as $track)
            <button 
                class="w-full text-left px-2 py-1.5 rounded text-xs flex justify-between items-center group transition-colors {{ $currentTrackId === $track->id ? 'bg-blue-900/40 text-blue-200' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}"
                wire:click="play({{ $track->id }})"
            >
                <div class="truncate flex-1 pr-2">
                    <span class="opacity-50 inline-block w-4 text-gray-500">{{ $loop->iteration }}.</span>
                    {{ $track->title }}
                </div>
                <div class="opacity-0 group-hover:opacity-100 text-[10px] text-gray-500 whitespace-nowrap">
                    {{ $track->duration > 0 ? gmdate('i:s', $track->duration) : '' }}
                </div>
            </button>
        @endforeach
    </div>
</div>
