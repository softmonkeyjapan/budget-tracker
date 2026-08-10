<?php

declare(strict_types=1);

namespace App\Domains\Feedback\Contracts;

interface FeedbackClassifierInterface
{
    /**
     * @return array{type: string, title: string}|null
     */
    public function classify(string $message): ?array;
}
