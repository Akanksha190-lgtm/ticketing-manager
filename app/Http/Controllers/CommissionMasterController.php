<?php

namespace App\Http\Controllers;
use App\Models\AirlineCommission;
use Illuminate\Http\Request;

class CommissionMasterController extends Controller
{
    public function updateCommission(Request $request, $id)
    {
        $commission = AirlineCommission::findOrFail($id);

        $commission->update([
        'airline' => $request->airline,
        'code' => $request->code,
        'numeric' => $request->numeric,
        'au_commission' => $request->au_commission,
        'ex_au_commission' => $request->ex_au_commission,
        ]);

        return response()->json([
        'success' => true,
        'message' => 'Updated successfully.'
        ]);
    }

    public function destroyCommission($id)
    {
        AirlineCommission::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully.'
        ]);
    }
}
