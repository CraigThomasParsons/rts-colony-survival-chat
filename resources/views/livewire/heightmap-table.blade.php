<div class="overflow-auto" wire:poll.5s>
  <table class="border-collapse">
    <caption class="text-sm mb-2">
      Water ≤ {{ $waterThreshold }}, Foothills ≥ {{ $mountainThresholdLow }}, Peaks ≥ {{ $mountainThresholdHigh }}
    </caption>
    @for ($y = 0; $y < $sizeY; $y++)
      <tr>
        @for ($x = 0; $x < $sizeX; $x++)
          @php
            $h = $grid[$y][$x] ?? 0;
            $val = max(0, min(255, (int) $h));
            // Palette mapping:
            // - Water (≤ waterThreshold): shades of blue
            // - Mountain (≥ mountainThreshold): shades of brown
            // - Land (otherwise): shades of green
            if ($h <= $waterThreshold) {
              $color = sprintf('rgb(%1$d,%1$d,%2$d)', 0, max(80, min(255, (int) ($val + 100)))); // blue
              // Better: derive blue intensity by height value, but keep higher contrast
              $color = sprintf('rgb(%d,%d,%d)', 0, (int) max(60, min(180, $val)), (int) max(120, min(255, 100 + $val)));
            } elseif ($h >= $mountainThresholdHigh) {
              // Brown tones: more red, some green, little blue
              $color = sprintf('rgb(%d,%d,%d)', (int) max(100, min(200, 80 + $val)), (int) max(60, min(140, 40 + ($val/2))), (int) max(20, min(80, $val/4)));
            } elseif ($h >= $mountainThresholdLow) {
              // Foothills: olive/khaki tones between land and mountain
              $color = sprintf('rgb(%d,%d,%d)', (int) max(80, min(160, 60 + $val)), (int) max(90, min(170, 70 + ($val/1.5))), (int) max(30, min(100, 20 + ($val/3))));
            } else {
              // Green land
              $color = sprintf('rgb(%d,%d,%d)', (int) max(20, min(80, $val/4)), (int) max(100, min(220, 60 + $val)), (int) max(20, min(80, $val/4)));
            }
          @endphp
          <td title="{{ $h }}" style="width:6px;height:6px;background-color: {{ $color }}; padding:0; border:0;"></td>
        @endfor
      </tr>
    @endfor
  </table>
</div>
