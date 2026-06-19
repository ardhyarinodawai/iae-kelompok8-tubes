<?php
 
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;
 
Route::middleware('verify.sso')->group(function () {
    Route::get('/v1/ticket-service/tickets', [TicketController::class, 'index']);
    Route::get('/v1/ticket-service/tickets/{id}', [TicketController::class, 'show']);
    Route::post('/v1/ticket-service/tickets', [TicketController::class, 'store']);
});
 



