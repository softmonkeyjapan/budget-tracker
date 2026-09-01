<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Expenses\Jobs\ProcessBciWebhookNotificationJob;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class BciWebhookController extends Controller
{
    public function store(Request $request): Response
    {
        ProcessBciWebhookNotificationJob::dispatch(
            (int) config('services.bci_webhook.user_id'),
            $request->getContent(),
        );

        return response()->noContent();
    }
}
