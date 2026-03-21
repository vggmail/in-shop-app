<?php
namespace App\Traits;
use App\Models\ActivityLog;

trait LogsActivity {
    protected static function bootLogsActivity() {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => $event,
                    'model_type' => class_basename($model),
                    'model_id' => $model->id,
                    'details' => $event === 'updated' ? json_encode($model->getChanges()) : null,
                    'ip_address' => request()->ip()
                ]);
            });
        }
    }
}
