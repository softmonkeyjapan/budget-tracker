<?php

declare(strict_types=1);

namespace App\Domains\Feedback\DataTransferObjects;

use App\Domains\Feedback\Http\Requests\StoreFeedbackRequest;

final readonly class SubmitFeedbackData
{
    public function __construct(
        public string $message,
        public string $pageUrl,
    ) {}

    public static function fromRequest(StoreFeedbackRequest $request): self
    {
        return new self(
            message: $request->string('message')->toString(),
            pageUrl: $request->string('page_url')->toString(),
        );
    }
}
