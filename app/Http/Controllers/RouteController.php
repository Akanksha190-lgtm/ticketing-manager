<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;

class RouteController extends Controller
{
    public function updateCodes(Request $request, $id)
    {
        $request->validate([
            'origin_code' => 'nullable|string|max:10',
            'destination_code' => 'nullable|string|max:10',
        ]);

        $route = Route::findOrFail($id);

        $route->update([
            'origin_code' => strtoupper(trim($request->origin_code)),
            'destination_code' => strtoupper(trim($request->destination_code)),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully.',
            'origin_code' => $route->origin_code,
            'destination_code' => $route->destination_code,
        ]);
    }
}
