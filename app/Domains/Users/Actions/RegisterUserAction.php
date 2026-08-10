<?php

declare(strict_types=1);

namespace App\Domains\Users\Actions;

use App\Domains\Users\DataTransferObjects\RegisterUserData;
use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

final class RegisterUserAction
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(RegisterUserData $data): User
    {
        $user = $this->users->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        event(new Registered($user));

        return $user;
    }
}
