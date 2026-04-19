<?php
use App\Http\Controllers\OrderTrackingController;

Route::middleware(['auth:sanctum', 'throttle:30,1'])
    ->post('/orders/{order}/position', [OrderTrackingController::class, 'update']);
