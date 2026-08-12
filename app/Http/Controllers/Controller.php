<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function logActivity(string $action, ?string $modelType = null, ?int $modelId = null, string $description = '', ?array $properties = null): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
