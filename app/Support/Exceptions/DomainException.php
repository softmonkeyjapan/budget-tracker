<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class DomainException extends Exception
{
    /**
     * Render this exception as a redirect back with an error message,
     * matching how Inertia surfaces validation/business errors.
     */
    public function render(Request $request): RedirectResponse
    {
        return back()->withErrors(['message' => $this->getMessage()]);
    }
}
