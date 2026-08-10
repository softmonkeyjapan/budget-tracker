<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Controllers\Auth;

use App\Domains\Users\Actions\UpdatePasswordAction;
use App\Domains\Users\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class PasswordController extends Controller
{
    public function __construct(
        private readonly UpdatePasswordAction $updatePassword,
    ) {}

    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->updatePassword->execute($request->user(), $request->validated('password'));

        return back();
    }
}
