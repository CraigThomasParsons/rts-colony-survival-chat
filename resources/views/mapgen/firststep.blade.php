<!doctype html>
<html>
    <head>
      <meta charset="utf-8">
      <title>Map Loader :: Step 1</title>
      <style>
        table {
            width: 30px;
            border: 1px solid black;
        }
        td {
            text-align: center;
            valign: center;
        }
        table td > div {
            overflow: hidden;
            height: 10px;
            width: 10px;
        }
      </style>
    </head>
    <body>
<?php
/**
 * This view has to go through the arrays from the top down.
 */
?>
      <table border="1" class="Preview">
      @for ($y = $size - 1; $y > -1; $y--)
        <tr>
          @for ($x = 0; $x < $size; $x += 1)
            @if (isset($cells[$x][$y])) 
              @if ($cells[$x][$y]->name == 'Passable Land')
                <td bgcolor="#ffffcc" valign="middle">
                  <div>
                  </div>
                </td>
              @elseif ($cells[$x][$y]->name == 'Trees')
                <td bgcolor="#006600" valign="middle">
                  <div>
                  </div>
                </td>
              @elseif ($cells[$x][$y]->name == 'Water')
                <td bgcolor="#333399" valign="middle">
                  <div>
                  </div>
                </td>
              @else
                <td bgcolor="#996633">
                  <div>
                  </div>
                </td>
              @endif
            @else
                <td bgcolor="#996633"><div>{{ $x.'-'.$y }}</div></td>
            @endif
          @endfor
        </tr>
      @endfor
      </table>
        <br />
        Going to run first step on MapId for id[ {{ $mapId }} ]
        <div>
            <a href="{{URL::route('mapgen.step2', '1')}}">
              Next Step
            </a>
        </div>
    </body>
</html>
