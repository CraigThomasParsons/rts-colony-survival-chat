@php
    // Keep markup as a <td> so TerrainMap can render a pure table.
    // The emoji is also duplicated into a data attribute to make it easy for JS/hover tooling.
    $mx = $tile['map_x'] ?? 0;
    $my = $tile['map_y'] ?? 0;
    $type = $tile['type'] ?? null;
    $hasTrees = (bool)($tile['has_trees'] ?? false);
    $emoji = $this->treeEmoji;
@endphp

<td
    class="{{ $this->cssClass }} w-4 h-4 text-center align-middle select-none"
    style="width:16px;height:16px;line-height:16px;font-size:12px;border:1px solid rgba(255,255,255,0.04);"
    data-map-x="{{ $mx }}"
    data-map-y="{{ $my }}"
    data-tile-type="{{ $type !== null ? (int)$type : '' }}"
    data-has-trees="{{ $hasTrees ? '1' : '0' }}"
    data-tree-emoji="{{ $emoji }}"
    title="({{ $mx }},{{ $my }}) type={{ $type !== null ? (int)$type : '-' }}{{ $hasTrees ? ' trees' : '' }}">
    {{ $emoji }}
</td>