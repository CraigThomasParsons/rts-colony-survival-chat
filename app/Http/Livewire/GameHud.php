namespace App\Http\Livewire;

use Livewire\Component;

class GameHud extends Component
{
    public int $wood = 100;
    public int $stone = 100;
    public int $gold = 100;

    public function newGame()
    {
        // later: call API to reset game, broadcast, etc.
        $this->dispatch('newGameRequested');
    }

    public function loadGame()
    {
        $this->dispatch('loadGameRequested');
    }

    public function render()
    {
        return view('livewire.game-hud');
    }
}
