<div>
    <div class="flex space-x-4">
        <div class="w-3/4">
            <canvas id="mapEditorCanvas" width="{{ $mapWidth * 4 }}" height="{{ $mapHeight * 4 }}" style="border:1px solid #ccc;"></canvas>
        </div>
        <div class="w-1/4">
            <div class="mb-4">
                <label class="block font-medium">Brush</label>
                <select id="brushSelect" class="border p-2 rounded w-full">
                    <option value="terrain:grass">Grass</option>
                    <option value="terrain:road">Road</option>
                    <option value="terrain:water">Water</option>
                    <option value="terrain:forest">Forest</option>
                    <option value="terrain:mountain">Mountain</option>
                    <option value="resource:trees">Resource: Trees</option>
                    <option value="resource:ore">Resource: Ore</option>
                    <option value="resource:none">Remove Resource</option>
                </select>
            </div>
            <div class="mb-4">
                <button id="undoBtn" class="bg-gray-200 p-2 rounded w-full">Undo</button>
            </div>
            <div>
                <button id="placeSettlementBtn" class="bg-green-600 text-white p-2 rounded w-full">Place Settlements</button>
            </div>
        </div>
    </div>

    <script src="/js/map-editor.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.emit('requestPreview');
        });
    </script>
</div>
