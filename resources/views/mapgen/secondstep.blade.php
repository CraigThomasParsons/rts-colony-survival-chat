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
            height: 12px;
            width: 12px;
        }

        .landCell {
          border-color: #996600;
        }
        .treeCell {
          border-color: #006600;
        }
        .waterCell {
          border-color: #0A0A0A;
        }
        .rockCell {
          border-color: #606060;
        }

        .landTile {
          background-color: #2E8A2E;
        }
        .treeTile {
          background-color: #003300;
        }
        .waterTile {
          background-color: #003366;
        }
        .rockTile {
          background-color: #C0C0C0;
        }

        .tileTopLeft {
            border-top-style: outset;
            border-right-style: double;
            border-bottom-style: double;
            border-left-style: outset;
        }
        .tileBottomLeft {
            border-top-style: double;
            border-right-style: double;
            border-bottom-style: outset;
            border-left-style: outset;
        }
        .tileTopRight {
            border-top-style: outset;
            border-right-style: outset;
            border-bottom-style: double;
            border-left-style: double;
        }
        .tileBottomRight {
            border-top-style: double;
            border-right-style: outset;
            border-bottom-style: outset;
            border-left-style: double;
        }
      </style>
    </head>
    <body>
<?php
/**
 * This view has to go through the arrays from the top down.
 */
?>
      Going to run second step on MapId for id[ {{ $mapId }} ]
      <br />
      <table class="Preview">
        @for ($y = ($size * 2); $y > -1; $y-=2)
          @for ($offset = 0; $offset < 2; $offset+=1)
            @if (($y + $offset) == $y)
              @if (isset($tiles[$y]))
                <tr>
                  @for ($x = 0; $x < 100; $x += 1)
                    @if (isset($tiles[$y][$x]))
                      @if ($tiles[$y][$x]->coordinateX == 1)
                        <td title="{{$tiles[$y][$x]->mapCoordinateX}},{{$tiles[$y][$x]->mapCoordinateY}}"
                        class='tileTopRight @include('mapgen.tileclassname', array('x' => $x, 'y' => $y, 'cells' => $cells)) @include('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]))'>
                          <div>&nbsp;</div>
                        </td>
                      @else
                        <td title="{{$tiles[$y][$x]->mapCoordinateX}},{{$tiles[$y][$x]->mapCoordinateY}}"
                        class='tileTopLeft @include('mapgen.tileclassname', array('x' => $x, 'y' => $y, 'cells' => $cells)) @include('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]))'>
                          <div>&nbsp;</div>
                        </td>
                      @endif
                    @endif
                  @endfor
                </tr>
              @endif
            @else
              @if (isset($tiles[$y + $offset]))
                <tr>
                  @for ($x = 0; $x < 100; $x += 1)
                    @if (isset($tiles[$y + $offset][$x]))
                      @if ($tiles[$y][$x]->coordinateX == 1)
                        <td title="{{$tiles[$y][$x]->mapCoordinateX}},{{$tiles[$y][$x]->mapCoordinateY}}"
                        class='tileBottomRight @include('mapgen.tileclassname', array('x' => $x, 'y' => $y, 'cells' => $cells)) @include('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]))'>
                          <div>&nbsp;</div>
                        </td>
                      @else
                        <td title="{{$tiles[$y][$x]->mapCoordinateX}},{{$tiles[$y][$x]->mapCoordinateY}}"
                        class='tileBottomLeft @include('mapgen.tileclassname', array('x' => $x, 'y' => $y, 'cells' => $cells)) @include('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]))'>
                          <div>&nbsp;</div>
                        </td>
                      @endif
                    @endif
                  @endfor
                </tr>
              @endif
            @endif
          @endfor
        @endfor
      </table>
    <div>
        <a href="{{URL::route('mapgen.step3', '1')}}">
          Next Step
        </a>
    </div>
  </body>
</html>
