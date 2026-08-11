<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Controllers;

use App\Domains\Users\Actions\DeleteAccountAction;
use App\Domains\Users\Actions\UpdateProfileAction;
use App\Domains\Users\DataTransferObjects\UpdateProfileData;
use App\Domains\Users\Http\Requests\DeleteAccountRequest;
use App\Domains\Users\Http\Requests\ProfileUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfileAction $updateProfile,
        private readonly DeleteAccountAction $deleteAccount,
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Users/Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->updateProfile->execute($request->user(), UpdateProfileData::fromRequest($request));

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $this->deleteAccount->execute($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
