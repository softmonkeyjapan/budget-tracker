<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Expenses\Actions\UpdateEcheanceOccurrenceAction;
use App\Domains\Expenses\DataTransferObjects\UpdateEcheanceOccurrenceData;
use App\Domains\Expenses\Http\Requests\UpdateEcheanceOccurrenceRequest;
use App\Http\Controllers\Controller;
use App\Models\EcheanceOccurrence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

final class EcheanceOccurrencesController extends Controller
{
    public function update(
        UpdateEcheanceOccurrenceRequest $request,
        EcheanceOccurrence $echeanceOccurrence,
        UpdateEcheanceOccurrenceAction $action,
    ): RedirectResponse {
        $this->authorize('update', $echeanceOccurrence);

        $action->execute($echeanceOccurrence, UpdateEcheanceOccurrenceData::fromRequest($request));

        return Redirect::back();
    }
}
