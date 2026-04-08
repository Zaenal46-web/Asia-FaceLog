<?php

namespace App\Http\Controllers\Integrasi;

use App\Http\Controllers\Controller;
use App\Services\Fingerspot\FingerWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FingerWebhookController extends Controller
{
    public function __construct(
        protected FingerWebhookService $service
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            $payload = $request->all();
        }

        try {
            $this->service->handle($payload);

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('FingerWebhookController handle error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            // tetap OK agar mesin / server fingerspot tidak retry spam
            return response('OK', 200);
        }
    }
}