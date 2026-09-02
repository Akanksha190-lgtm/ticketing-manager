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

        $entries = \App\Models\FareCommissionEntries::where('airline_id', $commission->id)->get();
        foreach ($entries as $entry) {
            $commPct = (float) ($commission->au_commission ?? 0);
            $published = (float) ($entry->published ?? 0);
            $markup = (float) ($entry->markup ?? 0);
            $net = $commPct > 0 ? ($published * ($commPct / 100)) * $markup : $published;
            $gross = $net + $markup;
            $entry->update([
                'net' => $net,
                'gross' => $gross,
            ]);
        }

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
