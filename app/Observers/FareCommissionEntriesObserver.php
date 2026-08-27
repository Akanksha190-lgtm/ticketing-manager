<?php

namespace App\Observers;

use App\Models\FareSource;
use App\Models\AuditLog;
use App\Models\Route;
use App\Models\FareCommissionEntries;
use App\Models\Cabin;

class FareCommissionEntriesObserver
{
    /**
     * Handle the FareCommissionEntries "created" event.
     */
    public function created(FareCommissionEntries $fareCommissionEntries): void
    {
        $newValues = $fareCommissionEntries->toArray();

        if (!empty($newValues['route_id'])) {

            $route = Route::find($newValues['route_id']);

            if ($route) {
                $newValues['route'] =
                    $route->origin . ' → ' . $route->destination;
            }

            unset($newValues['route_id']);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => FareCommissionEntries::class,
            'model_id' => $fareCommissionEntries->id,
            'old_values' => null,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Handle the FareCommissionEntries "updated" event.
     */
    public function updated(FareCommissionEntries $fareCommissionEntries): void
    {
        $oldValues = $fareCommissionEntries->getOriginal();
        $newValues = $fareCommissionEntries->getAttributes();

        $this->convertAuditValues($oldValues);
        $this->convertAuditValues($newValues);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => FareCommissionEntries::class,
            'model_id' => $fareCommissionEntries->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Handle the FareCommissionEntries "deleted" event.
     */
    public function deleted(FareCommissionEntries $fareCommissionEntries): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'model_type' => FareCommissionEntries::class,
            'model_id' => $fareCommissionEntries->id,
            'old_values' => $fareCommissionEntries->getOriginal(),
            'new_values' => null,
            'user_name' => auth()->user()->name,
        ]);
    }

    /**
     * Handle the FareCommissionEntries "restored" event.
     */
    public function restored(FareCommissionEntries $fareCommissionEntries): void
    {
        //
    }

    /**
     * Handle the FareCommissionEntries "force deleted" event.
     */
    public function forceDeleted(FareCommissionEntries $fareCommissionEntries): void
    {
        //
    }

    private function convertAuditValues(&$values): void
    {
        // Route
        if (!empty($values['route_id'])) {
            $route = Route::find($values['route_id']);

            if ($route) {
                $values['route'] =trim($route->origin) . ' → ' . trim($route->destination);
            }
            unset($values['route_id']);
        }

        // Cabin
        if (!empty($values['cabin_id'])) {
            $cabin = Cabin::find($values['cabin_id']);

            if ($cabin) {
                $values['cabin'] = $cabin->name;
            }
            unset($values['cabin_id']);
        }
        
        //source
        if (!empty($values['source_id'])) {
            $source = FareSource::find($values['source_id']);

            if ($source) {
                $values['source'] = $source->name;
            }
            unset($values['source_id']);
        }
    }
}
