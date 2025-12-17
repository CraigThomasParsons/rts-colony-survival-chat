<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\MapDatabase\MapModel;
use App\Services\SettlementPlacer;

class MapGenController extends Controller
{
    public function index()
    {
        return view('admin.mapgen.index');
    }

    public function placeSettlements(Request $request, SettlementPlacer $placer)
    {
        $map = MapModel::loadFromDatabase();
        $seed = $request->input('seed', null);
        $placed = $placer->placeSettlements($map, 3, 20, $seed, 1);
        return response()->json(['count' => count($placed)]);
    }
}
