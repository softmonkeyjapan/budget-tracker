<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Controllers\Auth;

use App\Domains\Users\Actions\RegisterUserAction;
use App\Domains\Users\DataTransferObjects\RegisterUserData;
use App\Domains\Users\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUser,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $user = $this->registerUser->execute(RegisterUserData::fromRequest($request));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
