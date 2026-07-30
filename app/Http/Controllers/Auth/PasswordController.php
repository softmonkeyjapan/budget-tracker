<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;

final class PasswordController extends Controller
{
    public function __construct(
        private readonly UserService $users,
    ) {}

    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->users->updatePassword($request->user(), $request->validated('password'));

        return back();
    }
}
