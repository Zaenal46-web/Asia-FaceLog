<?php

use App\Http\Controllers\Integrasi\FingerWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/fingerspot/webhook', [FingerWebhookController::class, 'handle'])
    ->name('api.fingerspot.webhook');