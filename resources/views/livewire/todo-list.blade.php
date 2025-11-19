<div class="container center">
    <div>
        <input type="text" wire:model="task" wire:keydown.enter="addTodo" placeholder="Add new todo"/>
    </div>
    @forelse ($todos as $todo)
    <div class="box" :wire:key="$loop->index">
        <input type="checkbox" id='markAsDone-{{$todo->id}}'
               wire:change="markAsDone({{$todo->id}})"
        />
        <label for="markAsDone-{{$todo->id}}"
               style="{{$todo->getTextStyle()}}">
            {{$todo->description}}
        </label>
        @if($todo->status == 'done')
          &nbsp;
          <button wire:click="remove({{$todo->id}})">
            delete
          </button>
        @endif
        <br />
   </div>
    @empty
        <p>No Todos</p>
    @endforelse
</div>