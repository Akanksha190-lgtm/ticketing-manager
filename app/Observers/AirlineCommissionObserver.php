<?php

namespace App\Observers;

use App\Models\AirlineCommission;
use App\Models\AuditLog;

class AirlineCommissionObserver
{
    public function created(AirlineCommission $airlineCommission): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => AirlineCommission::class,
            'model_id' => $airlineCommission->id,
            'old_values' => null,
            'new_values' => $airlineCommission->getAttributes(),
        ]);
    }

    public function updated(AirlineCommission $airlineCommission): void
    {
        $oldValues = $airlineCommission->getOriginal();
        $newValues = $airlineCommission->getChanges();

        if (empty($newValues)) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => AirlineCommission::class,
            'model_id' => $airlineCommission->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public function deleted(AirlineCommission $airlineCommission): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'model_type' => AirlineCommission::class,
            'model_id' => $airlineCommission->id,
            'old_values' => $airlineCommission->getOriginal(),
            'new_values' => null,
        ]);
    }
}
