                          @if (isset($cells[$x / 2][($y / 2)]))
                            @if ($cells[$x / 2][($y / 2)]->name == 'Passable Land')
                              landCell
                            @elseif ($cells[$x / 2][($y / 2)]->name == 'Trees')
                              treeCell
                            @elseif ($cells[$x / 2][($y / 2)]->name == 'Water')
                              waterCell
                            @else
                              rockCell
                            @endif
                          @endif