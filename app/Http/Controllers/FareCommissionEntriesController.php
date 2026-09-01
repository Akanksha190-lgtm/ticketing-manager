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
        $search = trim($request->input('search', ''));
        
        
        $farecomissentry = FareCommissionEntries::with(['route','cabin', 'fareSource','currency','airline'])
            ->orderBy('id', 'asc')
            ->get();
        $airlineCommissionsQuery = AirlineCommission::query();

        if ($search !== '') {
            $exactCodeExists = AirlineCommission::where('code', $search)->exists();

            if ($exactCodeExists) {
                $airlineCommissionsQuery->where('code', $search);
            } else {
                $airlineCommissionsQuery->where('airline', 'LIKE', "%{$search}%");
            }
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
        
        // Airline master table find/create
        $airline = AirlineCommission::where(
            ['airline' => trim($request->airline),],
            ['code' => trim($request->airline_code_id),]
        )->first();

        if (!$airline) {
            $airline = AirlineCommission::create([
                'airline' => trim($request->airline),
                'code' => trim($request->airline_code_id),
            ]);
        }
        // Fare commission airline_id save
        $data['airline_id'] = $airline->id;

        //airline code id
        $data['airline_code_id'] = $airline->id;
        $data['au_commission'] = $airline->au_commission;

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

        unset($data['origin'], $data['destination'],$data['airline'],$data['code'],);

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

        $route->update([
            'origin_code' => strtoupper(trim($request->origin_code ?? '')),
            'destination_code' => strtoupper(trim($request->destination_code ?? '')),
        ]);

        $airline = AirlineCommission::where('airline',trim($request->airline_id))->first();
        $oldAuCommission = $airline?->au_commission;
        $hasAuCommission = $request->has('au_commission') && $request->input('au_commission') !== '';
        
        $entry->update([
            'airline_id' => $airline ? $airline->id : null,
            'route_id' => $route->id,
            'cabin_id' => $cabin ? $cabin->id : null,
            'source_id' => $source ? $source->id : null,
            'published' => $request->published,
            'net'     =>  $request->net,
            'markup' => $request->markup,
            'travel_from' => $request->travel_from,
            'travel_to' => $request->travel_to,
            'gross' => $request->gross,
            'valid_until' => $request->valid_until,
            'status' => $request->status,
        ]);

        if ($hasAuCommission && $airline) {
            $airline->update([
                'au_commission' => $request->au_commission,
            ]);
            $airline->refresh();

            // Keep the master commission change in the fare entry's existing audit card.
            $auditLog = AuditLog::where('model_type', FareCommissionEntries::class)
                ->where('model_id', $entry->id)
                ->latest('id')
                ->first();

            if ($auditLog && $hasAuCommission) {
                $oldValues = $auditLog->old_values ?? [];
                $newValues = $auditLog->new_values ?? [];
                $oldValues['au_commission'] = $oldAuCommission;
                $newValues['au_commission'] = $airline->au_commission;

                $auditLog->update([
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                ]);
            }
        }
        
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
        $privateTourCode = FareSource::where('name', 'PRIVATE/TOUR CODE')->value('id');
        $data = $request->validate([
            'airline' => ['required', 'string', 'max:150'],
            // 'airline_code_id' => ['required', 'string', 'max:10'],
            'origin' => ['required', 'string', 'max:100'],
            'destination' => ['required', 'string', 'max:100'],
            'route_id' => ['nullable', 'integer', 'exists:routes,id'],
            'cabin_id' => ['required', 'integer', 'exists:cabins,id'],
            'source_id' => ['required', 'integer', 'exists:fare_sources,id'],
            'tour_code' => ["required_if:source_id,{$privateTourCode}", 'string', 'max:100'],
            'pcc_iata_ref' => ['nullable', 'string', 'max:100'],
            'published' => ['required', 'numeric', 'min:0'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            // 'disc_comm' => ['required', 'numeric', 'min:0', 'max:100'],
            'net' => ['nullable', 'numeric', 'min:0'],
            'markup' => ['required', 'numeric', 'min:0'],
            'travel_from' => ['nullable', 'date'],
            'travel_to' => ['nullable', 'date', 'after_or_equal:travel_from'],
            'gross' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['required', 'date'],
            'internal_notes' => ['nullable', 'string'],
            'booking_notes' => ['nullable', 'string'],
        ],
        [
        // 'airline_code_id.required' => 'Airline is required.',
        'cabin_id.required' => 'Cabin is required.',
        'currency_id.required' => 'Currency is required.',
        'tour_code.required_if' => 'Private fares need a tour code.',
        'source_id.required' => 'Source is required'
        ]
        );

        foreach (['tour_code', 'pcc_iata_ref', 'internal_notes', 'booking_notes'] as $field) {
            $data[$field] = $data[$field] ?? '';
        }

        return $data;
    }

    //for ticketing team data 
    public function ticketingTeam(Request $request)
    {
        $search = trim((string) ($request->input('fare_search', $request->input('search', ''))));
        $masterSearch = trim($request->input('master_search', ''));
        $source = $request->input('source');
        $status = $request->input('status');
        $sort = $request->input('sort', 'valid_until_asc');
        
        $farecomissentryQuery = FareCommissionEntries::with('route','fareSource','cabin','airline','currency');

        if ($search !== '') {
            $farecomissentryQuery->where(function ($query) use ($search) {

                // Airline master table
                $query->whereHas('airline', function ($q) use ($search) {
                    $q->where('airline', 'LIKE', "%{$search}%");
                })

                // Route table
                ->orWhereHas('route', function ($q) use ($search) {
                    $q->where('origin', 'LIKE', "%{$search}%")
                    ->orWhere('destination', 'LIKE', "%{$search}%")
                    ->orWhere('origin_code', 'LIKE', "%{$search}%")
                    ->orWhere('destination_code', 'LIKE', "%{$search}%");
                });

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
        $farecomissentry = $farecomissentryQuery->get();

        //sort filter
        switch ($sort) {
            case 'commission_desc': $farecomissentry = $farecomissentry->sortByDesc(function ($entry) {
                        return $entry->airline?->au_commission ?? 0;
                    })->values();
            break;
            case 'margin_desc':$farecomissentry = $farecomissentry->sortByDesc(function ($entry) {
                        return ($entry->gross ?? 0) - ($entry->net ?? 0);
                    })->values();
            break;
            case 'valid_until_asc':
            default: $farecomissentry = $farecomissentry->sortBy('valid_until')->values();
            break;
        }

        $activeFares = FareCommissionEntries::where('status', 'Active')->count();

        $avgCommissionDiscount = AirlineCommission::whereNotNull('au_commission')->avg('au_commission');

        $totalMargin = FareCommissionEntries::whereNotNull('gross')->whereNotNull('net')->selectRaw('SUM(gross - net) as total_margin')->value('total_margin');

        $expiringFares = FareCommissionEntries::where('status', 'Expiring Soon')->count();

        $statuses = FareCommissionEntries::whereNotNull('status')
                ->where('status', '!=', '')
                ->distinct()
                ->orderBy('status')
                ->pluck('status');
            
        
        $airlineCommissionsQuery = AirlineCommission::query();

        if ($masterSearch !== '') {
            $exactCodeExists = AirlineCommission::where('code', $masterSearch)->exists();

            if ($exactCodeExists) {
                $airlineCommissionsQuery->where('code', $masterSearch);
            } else {
                $airlineCommissionsQuery->where('airline', 'LIKE', "%{$masterSearch}%");
            }
        }

        $airlineCommissions = $airlineCommissionsQuery->orderBy('id', 'asc')->get();
        
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
        $entry = FareCommissionEntries::with('airline')->findOrFail($id);

        $fareHistory = AuditLog::with('user')
            ->where('model_type', FareCommissionEntries::class)
            ->where('model_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $airlineHistory = collect();

        if ($entry->airline_id) {
            $airlineHistory = AuditLog::with('user')
                ->where('model_type', AirlineCommission::class)
                ->where('model_id', $entry->airline_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $history = $fareHistory->merge($airlineHistory)
            ->sortByDesc('created_at')
            ->values()
            ->filter(function ($log) use ($entry, $fareHistory) {
                if ($log['model_type'] === FareCommissionEntries::class && $log['model_id'] == $entry->id) {
                    return true;
                }

                if ($log['model_type'] === AirlineCommission::class && $log['model_id'] == $entry->airline_id) {
                    $isDuplicateSameTimestamp = $fareHistory->contains(function ($fareLog) use ($log, $entry) {
                        return $fareLog['model_type'] === FareCommissionEntries::class
                            && $fareLog['model_id'] == $entry->id
                            && isset($fareLog['created_at'])
                            && isset($log['created_at'])
                            && $fareLog['created_at'] == $log['created_at'];
                    });

                    return !$isDuplicateSameTimestamp;
                }

                return true;
            })
            ->values();

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }
}
