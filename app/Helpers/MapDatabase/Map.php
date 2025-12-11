<?php

namespace App\Helpers\MapDatabase;

use App\Models\Map as EloquentMap;
use App\Models\MapStatus;

/**
 * Represents 1 single square Map of a Rts game's entire world.
 * This should be moved to helpers, Create a Map class in the model folder
 * to used exclusivly for loading and saving the Map record.
 */
class Map extends MapModel
{
    /**
     * Initialize a helper-side Map record with sensible defaults.
     *
     * @param array $attributes Optional seed values provided by callers/upstream loaders.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct();

        // Seed base attributes so downstream processors always have coordinates + name/description.
        $defaults = [
            'id' => null,
            'coordinateX' => 0,
            'coordinateY' => 0,
            'name' => 'Initial Map',
            'description' => 'Initial Map Description',
        ];

        // Merge defaults with any explicit attributes passed in.
        foreach (array_merge($defaults, $attributes) as $key => $value) {
            $this->set($key, $value);
        }

        // Ensure a known starting state so the UI/status pollers have a defined value.
        if (!isset($this->data['mapstatuses_id'])) {
            $this->setState(MapStatus::CREATED_EMPTY);
        }
    }

    /**
     * Convenience accessor for the underlying map primary key.
     */
    public function getId(): ?int
    {
        return isset($this->data['id']) ? (int) $this->data['id'] : null;
    }

    /**
     * Persist the helper payload using the Eloquent Map model.
     */
    public function save(): int
    {
        // Reuse existing DB row when id is present; otherwise create a new record.
        $mapRecord = $this->getId()
            ? EloquentMap::query()->findOrFail($this->getId())
            : new EloquentMap();

        // Copy helper data onto the Eloquent model for persistence.
        $payload = $this->data ?? [];

        if (!is_array($payload)) {
            $payload = (array) $payload;
        }
        $mapRecord->fill($payload);
        $mapRecord->save();

        // Sync any database-assigned columns (id, timestamps, etc.) back into helper state.
        foreach ($mapRecord->getAttributes() as $key => $value) {
            $this->data[$key] = $value;
        }

        return (int) $mapRecord->id;
    }
}
