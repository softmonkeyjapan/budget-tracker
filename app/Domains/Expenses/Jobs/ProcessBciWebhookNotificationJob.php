<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Jobs;

use App\Domains\Expenses\Actions\CreateExpenseFromBciNotificationAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessBciWebhookNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $userId,
        private readonly string $rawBody,
    ) {}

    public function handle(CreateExpenseFromBciNotificationAction $action): void
    {
        $action->execute($this->userId, $this->rawBody);
    }
}
