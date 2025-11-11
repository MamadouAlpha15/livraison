<?php
use App\Http\Controllers\OrderTrackingController;

Route::middleware('auth:sanctum')->post('/orders/{order}/position', [OrderTrackingController::class, 'update']);
