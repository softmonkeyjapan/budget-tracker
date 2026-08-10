<?php

declare(strict_types=1);

namespace App\Domains\Feedback\Http\Controllers;

use App\Domains\Feedback\Actions\SubmitFeedbackAction;
use App\Domains\Feedback\DataTransferObjects\SubmitFeedbackData;
use App\Domains\Feedback\Http\Requests\StoreFeedbackRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

final class FeedbackController extends Controller
{
    public function store(StoreFeedbackRequest $request, SubmitFeedbackAction $action): RedirectResponse
    {
        $this->authorize('access-feedback');

        $action->execute($request->user(), SubmitFeedbackData::fromRequest($request), $request->userAgent());

        return Redirect::back();
    }
}
