<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

final class UserService
{
    public function __construct(
        private readonly UserRepositoryContract $users,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        return $user;
    }

    /**
     * @param  array{name: string, email: string}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $emailChanged = $user->email !== $data['email'];

        $user = $this->users->update($user, $data);

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();
        }

        return $user;
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $this->users->update($user, [
            'password' => Hash::make($newPassword),
        ]);
    }

    public function deleteAccount(User $user): void
    {
        $this->users->delete($user);
    }
}
