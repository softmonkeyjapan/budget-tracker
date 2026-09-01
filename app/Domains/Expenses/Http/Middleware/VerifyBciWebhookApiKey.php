<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyBciWebhookApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.bci_webhook.key');
        $provided = $request->header('X-Webhook-Key');

        if (
            ! is_string($expected) || $expected === ''
            || ! is_string($provided) || ! hash_equals($expected, $provided)
        ) {
            abort(401);
        }

        return $next($request);
    }
}
