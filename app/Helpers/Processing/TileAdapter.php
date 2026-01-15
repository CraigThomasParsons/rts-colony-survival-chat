<?php

namespace App\Helpers\Processing;

use App\Models\Tile as EloquentTile;

/**
 * TileAdapter bridges the legacy MountainProcessing helper with
 * the Eloquent MySQL Tile model. It provides the minimal API
 * expected by MountainProcessing: isRocky/notRocky, tileTypeId,
 * setTileDisplayType, and save().
 *
 * Contract:
 * - Rocky tiles have tileTypeId == 2 (Impassable Rocks)
 * - Passable land uses tileTypeId == 1
 * - Cliff-side classifications use ids 4–15 as per legacy mapping
 * - Out-of-bounds neighbors can be represented by synthetic
 *   adapters marked as rocky and not persisted.
 */
class TileAdapter
{
    /** @var EloquentTile|null */
    protected $model;

    /** @var int */
    public $x;

    /** @var int */
    public $y;

    /** @var int */
    public $tileTypeId = 1;

    /** @var string|null */
    protected $tileDisplayType = null;

    /** @var bool Synthetic adapters are not persisted. */
    protected $synthetic = false;

    /**
     * Construct from an existing Eloquent Tile model.
     */
    public static function fromModel(EloquentTile $model): self
    {
        $inst = new self();
        $inst->model = $model;
        // Coordinate fields in MySQL schema
        $inst->x = (int)($model->coordinateX ?? $model->x ?? 0);
        $inst->y = (int)($model->coordinateY ?? $model->y ?? 0);
        // Tile type field uses camelCase with underscore in schema: tileType_id
        $inst->tileTypeId = (int)($model->tileType_id ?? $model->tileTypeId ?? 1);
        // Optional display type may not exist in current schema
        $inst->tileDisplayType = property_exists($model, 'tile_display_type') ? $model->tile_display_type : ($model->tileDisplayType ?? null);
        return $inst;
    }

    /**
     * Construct a synthetic non-persisted tile at coordinates.
     */
    public static function synthetic(int $x, int $y, int $tileTypeId = 2): self
    {
        $inst = new self();
        $inst->synthetic = true;
        $inst->x = $x;
        $inst->y = $y;
        $inst->tileTypeId = $tileTypeId;
        return $inst;
    }

    /**
     * Legacy API expected by MountainProcessing.
     * Rocky tiles are those with tileTypeId == 2.
     */
    public function isRocky(): bool
    {
        return $this->tileTypeId === 2;
    }

    public function notRocky(): bool
    {
        return !$this->isRocky();
    }

    public function setTileDisplayType(string $type): void
    {
        $this->tileDisplayType = $type;
    }

    /** Get current display type (optional). */
    public function getTileDisplayType(): ?string
    {
        return $this->tileDisplayType;
    }

    /**
     * Persist changes to the underlying Eloquent model when available.
     */
    public function save(): void
    {
        if ($this->synthetic) {
            return; // do not persist synthetic neighbors
        }
        if ($this->model) {
            // Assign via Eloquent's dynamic attributes; no property_exists checks needed
            $this->model->tileType_id = $this->tileTypeId;
            // Optional column: only set when schema contains it
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('tile', 'tile_display_type') && $this->tileDisplayType !== null) {
                    $this->model->tile_display_type = $this->tileDisplayType;
                }
            } catch (\Throwable $e) {
                // Ignore schema inspection errors
            }
            $this->model->save();
        }
    }
}
