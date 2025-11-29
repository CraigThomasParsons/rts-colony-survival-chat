<!doctype html>
<html>
    <head>
      <meta charset="utf-8">
      <title>Map Loader :: Step 2</title>
      <style>
        table {
            width: 180px;
            border: 2px outset black;
            background-color: black;
            border-color: black;
        }
        td {
            text-align: center;
            valign: center;
            border-width: medium;
            background-color: #003366;
            border-color: #996600;
        }
        table td > div {
            overflow: hidden;
            height: 20px;
            width: 20px;
        }

        .landTile {
          background-color: #2E8A2E;
          border-color: #996600;
          border-style: solid;
          border-width: 0px;
        }
        .treeTile {
          background-color: #003300;
          border-color: #006600;
          border-bottom-style: solid;
          border-bottom-width: 5px;
          border-bottom-color: #533118;
        }
        .waterTile {
          background-color: #003366;
          border-color: #0A0A0A;
        }
        .rockTile {
          background-color: #C0C0C0;
          border-color: #606060;
        }

        .leftEdge {
          border-left-style: outset;
        }
        .rightEdge {
          border-right-style: outset;
        }
        .topEdge {
          border-top-style: outset;
        }
        .bottomEdge {
          border-bottom-style: outset;
        }
        .tileTopRight {
            border-top-style: outset;
            border-right-style: outset;
        }
        .tileTopLeft {
            border-top-style: outset;
            border-left-style: outset;
        }
        .tileBottomLeft {
            border-bottom-style: outset;
            border-left-style: outset;
        }
        .tileBottomRight {
            border-right-style: outset;
            border-bottom-style: outset;
        }
        .TopRightConcaveCorner {
            border-right-style: double;
            border-top-style: double;
        }
        .TopLeftConcaveCorner {
            border-top-style: double;
            border-left-style: double;
        }
        .bottomRightConcaveCorner {
            border-right-style: double;
            border-bottom-style: double;
        }
        .bottomLeftConcaveCorner {
            border-left-style: double;
            border-bottom-style: double;
        }
      </style>
    </head>
    <body>
<?php
/**
 * This view has to go through the arrays from the top down.
 */
?>
      <br />
      <table class="Preview">
        @for ($y = ($size * 2); $y > -1; $y-=1)
          @if (isset($tiles[$y]))
            <tr>
              @for ($x = 0; $x < ($size * 2); $x += 1)
                @php
                  $tileExists = isset($tiles[$y]) && isset($tiles[$y][$x]) && $tiles[$y][$x] !== null;
                @endphp
                @if ($tileExists)
                  <td title="{{$tiles[$y][$x]->mapCoordinateX}},{{$tiles[$y][$x]->mapCoordinateY}}-{{$tiles[$y][$x]->tileTypeId}}" class='@include('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]))'>
                    <div>&nbsp;</div>
                  </td>
                @else
                  <td class="waterTile">
                    <div>&nbsp;</div>
                  </td>
                @endif
              @endfor
            </tr>
          @else
            <tr>
              @for ($x = 0; $x < ($size * 2); $x += 1)
                <td class="waterTile"><div>&nbsp;</div></td>
              @endfor
            </tr>
          @endif
        @endfor
      </table>
    <div>
    @if (isset($next))
            <a href="{{URL::route($next, '1')}}">
          Next Step
        </a>
    @else
        <a href="{{ route('mapgen.preview', ['mapId' => request()->route('mapId') ?? 1]) }}">
          Next Step
        </a>
    @endif
    </div>
  </body>
</html>
