<?php

namespace App\Http\Controllers;

use App\Models\Cabin;
use App\Models\Currency;
use App\Models\Fare;
use App\Models\FareSource;
use App\Models\FareCommissionEntries;
use App\Models\AirlineCommission;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FareController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $fares = Fare::with(['cabin', 'fareSource', 'currency'])->orderBy('id', 'asc');
        
        $farecomissentry = FareCommissionEntries::with(['route','cabin', 'fareSource','currency'])
            ->orderBy('id', 'asc')
            ->get();
        $airlineCommissionsQuery = AirlineCommission::query();

        if ($search) {
            $airlineCommissionsQuery->where(function ($query) use ($search) {
                $query->where('airline', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }
        
        $airlineCommissions = $airlineCommissionsQuery->orderBy('id', 'asc')->get();
        
        return view('ticketing.index', [
            'fares' => $fares,
            'farecomissentry' => $farecomissentry,
            'cabins' => Cabin::orderBy('id')->get(),
            'fareSources' => FareSource::orderBy('id')->get(),
            'currencies' => Currency::orderBy('code')->get(),
            'airlineCommissions' => $airlineCommissions,
        ]);
    }

    public function store(Request $request)
    {

        Fare::create($this->validatedData($request));
    
        return redirect()
        ->route('ticketing.index')
        ->with('success', 'Fare created successfully.');
    }

    public function update(Request $request, Fare $fare)
    {
        $fare->update($this->validatedData($request));

        return response()->json([
            'message' => 'Fare updated successfully.',
            'fare' => $fare->fresh()->load(['cabin', 'fareSource', 'currency']),
        ]);
    }

    public function destroy(Fare $fare)
    {
        $fare->delete();

        return response()->json([
            'message' => 'Fare deleted successfully.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'airline' => ['required', 'string', 'max:150'],
            'airline_code' => ['required', 'string', 'max:10'],
            'origin' => ['required', 'string', 'max:100'],
            'destination' => ['required', 'string', 'max:100'],
            'cabin_id' => ['required', 'integer', 'exists:cabins,id'],
            'fare_source_id' => ['required', 'integer', 'exists:fare_sources,id'],
            'tour_code' => ['nullable', 'string', 'max:100'],
            'pcc_iata_ref' => ['nullable', 'string', 'max:100'],
            'published_fare' => ['required', 'numeric', 'min:0'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'discount_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'agency_markup' => ['required', 'numeric', 'min:0'],
            'travel_from' => ['nullable', 'date'],
            'travel_to' => ['nullable', 'date', 'after_or_equal:travel_from'],
            'valid_until' => ['required', 'date'],
            'internal_notes' => ['nullable', 'string'],
            'booking_notes' => ['nullable', 'string'],
        ]);

        foreach (['tour_code', 'pcc_iata_ref', 'internal_notes', 'booking_notes'] as $field) {
            $data[$field] = $data[$field] ?? '';
        }

        return $data;
    }


}
