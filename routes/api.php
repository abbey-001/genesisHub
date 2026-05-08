<?php
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingController;

Route::middleware('auth:sanctum')->group(function () {
    
    // Tracking endpoints
    Route::get('/deliveries/{delivery}/tracking', [TrackingController::class, 'getDeliveryTracking']);
    Route::get('/orders/{orderNumber}/track', [TrackingController::class, 'trackOrder']);
    
    // Rider endpoints
    Route::post('/rider/location', [TrackingController::class, 'updateRiderLocation']);
    Route::get('/rider/stats', [TrackingController::class, 'getRiderStats']);
    Route::get('/rider/available-deliveries', [TrackingController::class, 'getAvailableDeliveries']);
});

Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle']);