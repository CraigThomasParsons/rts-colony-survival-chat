 @if ($tile->tileTypeId == 1)
landTile
 @elseif ($tile->tileTypeId == 2)
rockTile
 @elseif ($tile->tileTypeId == 3)
waterTile
@elseif ($tile->tileTypeId == 4)
{{-- TopRightConvexedCorner Mountain --}}
rockTile tileTopRight

@elseif ($tile->tileTypeId == 5)
{{-- BottomLeftConvexedCorner Mountain --}}
rockTile tileBottomLeft

@elseif ($tile->tileTypeId == 6)
{{-- TopLeftConvexedCorner Mountain --}}
rockTile tileTopLeft

 @elseif ($tile->tileTypeId == 7)
rockTile tileBottomRight

 @elseif ($tile->tileTypeId == 8)
TopRightConcaveCorner rockTile

 @elseif ($tile->tileTypeId == 9)
TopLeftConcaveCorner rockTile

 @elseif ($tile->tileTypeId == 10)
bottomRightConcaveCorner rockTile

 @elseif ($tile->tileTypeId == 11)
bottomLeftConcaveCorner rockTile

 @elseif ($tile->tileTypeId == 12)
topEdge rockTile

 @elseif ($tile->tileTypeId == 13)
rightEdge rockTile

 @elseif ($tile->tileTypeId == 14)
bottomEdge rockTile

 @elseif ($tile->tileTypeId == 15)
leftEdge rockTile

{{-- 16  TopRightConvexedCorner waterTile  Water Tile Top Right Convexed Corner --}}
 @elseif ($tile->tileTypeId == 16)
waterTile tileTopRight

{{-- 17  BottomLeftConvexedCorner waterTile  Water Tile Bottom Left Convexed Corner --}}
 @elseif ($tile->tileTypeId == 17)
waterTile tileBottomLeft

{{-- 18  TopLeftConvexedCorner waterTile  Water Tile Top Left Convexed Corner --}}
 @elseif ($tile->tileTypeId == 18)
waterTile tileTopLeft

{{-- 19  BottomRightConvexedCorner waterTile  Water Tile Bottom Right Convexed Corner --}}
 @elseif ($tile->tileTypeId == 19)
waterTile tileBottomRight

{{-- 20  TopRightConcaveCorner waterTile  Water Tile Top Right Concave Corner --}}
 @elseif ($tile->tileTypeId == 20)
TopRightConcaveCorner waterTile

{{-- 21  TopLeftConcaveCorner waterTile  Water Tile Top Left Concave Corner --}}
 @elseif ($tile->tileTypeId == 21)
TopLeftConcaveCorner waterTile

{{-- 22  bottomRightConcaveCorner waterTile  Water Tile Bottom Right Concave Corner --}}
 @elseif ($tile->tileTypeId == 22)
bottomRightConcaveCorner waterTile

{{-- 23  bottomLeftConcaveCorner waterTile  Water Tile Bottom Left Concave Corner --}}
 @elseif ($tile->tileTypeId == 23)
bottomLeftConcaveCorner waterTile

{{-- 24  topEdge waterTile  Water Tile Top Edge --}}
 @elseif ($tile->tileTypeId == 24)
topEdge waterTile

{{-- 25  rightEdge waterTile  Water Tile Right Edge --}}
 @elseif ($tile->tileTypeId == 25)
rightEdge waterTile

{{-- 26  bottomEdge waterTile  Water Tile Bottom Edge --}}
 @elseif ($tile->tileTypeId == 26)
bottomEdge waterTile

{{-- 27  leftEdge waterTile  Water Tile Left Edge --}}
 @elseif ($tile->tileTypeId == 27)
leftEdge waterTile

 @else ($tile->tileTypeId == 29)
treeTile
 @endif
