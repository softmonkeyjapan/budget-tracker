<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Services\FeedbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

final class FeedbackController extends Controller
{
    public function __construct(
        private readonly FeedbackService $feedback,
    ) {}

    public function store(StoreFeedbackRequest $request): RedirectResponse
    {
        $this->authorize('access-feedback');

        $this->feedback->submit($request->user(), $request->validated(), $request->userAgent());

        return Redirect::back();
    }
}
