<?php

namespace App\Http\Controllers;

use App\Models\FareCommissionEntries;
use App\Models\Route;
use App\Models\AuditLog;
use App\Models\Cabin;
use App\Models\Currency;
use App\Models\AirlineCommission;
use App\Models\FareSource;
use Illuminate\Http\Request;
class FareCommissionEntriesController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        
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
        if ($request->expectsJson()) {
            return response()->json([
                'airlineCommissions' => $airlineCommissions,
            ]);
        }
        return view('ticketing.index', [
            // 'fares' => $fares,
            'farecomissentry' => $farecomissentry,
            'cabins' => Cabin::orderBy('id')->get(),
            'fareSources' => FareSource::orderBy('id')->get(),
            'currencies' => Currency::orderBy('code')->get(),
            'airlineCommissions' => $airlineCommissions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        
        $route = Route::firstOrCreate(
            [
                'origin' => $request->origin,
                'destination' => $request->destination,
            ],
            [
                'origin_code' => null,
                'destination_code' => null,
            ]
        );

        $data['route_id'] = $route->id;

        unset($data['origin'], $data['destination']);

        $entry = FareCommissionEntries::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Created successfully.',
            'data' => $entry,
        ]);
        
    }
    
    public function update(Request $request, $id)
    {
        $entry = FareCommissionEntries::findOrFail($id);
        $cabin = Cabin::where('name', $request->cabin)->first();
        $source = FareSource::where('name', $request->source)->first();
        $currency = Currency::where('code', $request->currency)->first();
    
        // Find existing route
        $route = Route::where('origin', $request->origin)->where('destination', trim($request->destination))->first();
        // Find or create route
        if (!$route) {
            $route = Route::create([
                'origin' => trim($request->origin),
                'destination' =>trim($request->destination),
                'origin_code' => null,
                'destination_code' => null,
            ]);
        }
        $entry->update([
            'airline' => $request->airline,
            'route_id' => $route->id,
            // 'origin' => $request->origin,
            // 'destination' => $request->destination,
            'cabin_id' => $cabin ? $cabin->id : null,
            'source_id' => $source ? $source->id : null,
            'published' => $request->published,
            'disc_comm' => $request->disc_comm,
            'net'     =>  $request->net,
            'markup' => $request->markup,
            'travel_from' => $request->travel_from,
            'travel_to' => $request->travel_to,
            'gross' => $request->gross,
            'valid_until' => $request->valid_until,
            'status' => $request->status,
        ]);

        // $entry->update($this->validatedData($request));
        
        return response()->json([
            'success' => true,
            'message' => 'Updated successfully.',
            'data' => $entry->load('route')
        ]);
    }

    public function destroy($id)
    {
        $entry = FareCommissionEntries::findOrFail($id);

        $routeId = $entry->route_id;

        $entry->delete();

        // Route delete
        if ($routeId) {
            $routeStillUsed = FareCommissionEntries::where('route_id',$routeId)->exists();

            // delete when not in use
            if (!$routeStillUsed) {
                Route::where('id', $routeId)->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully.'
        ]);
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'airline' => ['required', 'string', 'max:150'],
            'airline_code' => ['required', 'string', 'max:10'],
            'origin' => ['required', 'string', 'max:100'],
            'destination' => ['required', 'string', 'max:100'],
            'route_id' => ['nullable', 'integer', 'exists:routes,id'],
            'cabin_id' => ['required', 'integer', 'exists:cabins,id'],
            'source_id' => ['required', 'integer', 'exists:fare_sources,id'],
            'tour_code' => ['nullable', 'string', 'max:100'],
            'pcc_iata_ref' => ['nullable', 'string', 'max:100'],
            'published' => ['required', 'numeric', 'min:0'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'disc_comm' => ['required', 'numeric', 'min:0', 'max:100'],
            'net' => ['nullable', 'numeric', 'min:0'],
            'markup' => ['required', 'numeric', 'min:0'],
            'travel_from' => ['nullable', 'date'],
            'travel_to' => ['nullable', 'date', 'after_or_equal:travel_from'],
            'gross' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['required', 'date'],
            'internal_notes' => ['nullable', 'string'],
            'booking_notes' => ['nullable', 'string'],
        ]);

        foreach (['tour_code', 'pcc_iata_ref', 'internal_notes', 'booking_notes'] as $field) {
            $data[$field] = $data[$field] ?? '';
        }

        return $data;
    }

    //for ticketing team data 
    public function ticketingTeam(Request $request)
    {
        $search = $request->input('fare_search');
        $source = $request->input('source');
        $status = $request->input('status');
        
        $farecomissentryQuery = FareCommissionEntries::with('route','fareSource','cabin');

        if ($search) {
            $search = trim($search);

            $farecomissentryQuery->where(function ($query) use ($search) {
                $query->where('airline', 'LIKE', "%{$search}%")
                    ->orWhere('origin', 'LIKE', "%{$search}%")
                    ->orWhere('destination', 'LIKE', "%{$search}%");
            });
        }

        // SOURCE FILTER
        if ($source) {
            $farecomissentryQuery->whereHas('fareSource', function ($query) use ($source) {
                $query->where('name', $source);
            });
        }

        // STATUS FILTER
        if ($status) {
            $farecomissentryQuery->where('status', $status);
        }
        $farecomissentry = $farecomissentryQuery->orderBy('id', 'asc')->get();

        $activeFares = FareCommissionEntries::where('status', 'Active')->count();

        $avgCommissionDiscount = FareCommissionEntries::whereNotNull('disc_comm')->avg('disc_comm');

        $totalMargin = FareCommissionEntries::whereNotNull('gross')->whereNotNull('net')->selectRaw('SUM(gross - net) as total_margin')->value('total_margin');

        $expiringFares = FareCommissionEntries::where('status', 'Expiring Soon')->count();

        $statuses = FareCommissionEntries::whereNotNull('status')
                ->where('status', '!=', '')
                ->distinct()
                ->orderBy('status')
                ->pluck('status');
            
        
        $airlineCommissions = AirlineCommission::orderBy('id', 'asc')->get();
        
        if ($request->expectsJson()) {
            return response()->json([
                'airlineCommissions' => $airlineCommissions,
                'farecomissentry' => $farecomissentry,
                'status' => $statuses,
                'source' => $source,
            ]);
        }
       
        return view('ticketing-team.index', [
            'farecomissentry' => $farecomissentry,
            'airlineCommissions' => $airlineCommissions,
            'fareSources' => FareSource::orderBy('id')->get(),
            'statuses' => $statuses,
            'source' => $source,
            'status' => $status,
            'search' => $search,

            // Dashboard stats
            'activeFares' => $activeFares,
            'avgCommissionDiscount' => $avgCommissionDiscount,
            'totalMargin' => $totalMargin,
            'expiringFares' => $expiringFares,
        ]);
    }

    public function history($id)
    {
        $history = AuditLog::where('model_type', FareCommissionEntries::class)
            ->where('model_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
    
        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }
}
