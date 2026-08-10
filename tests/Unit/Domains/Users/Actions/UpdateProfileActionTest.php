<?php

use App\Domains\Users\Actions\UpdateProfileAction;
use App\Domains\Users\DataTransferObjects\UpdateProfileData;
use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;

it('resets email_verified_at when the email address changes', function () {
    $user = User::factory()->make(['email' => 'old@example.com']);

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('update')
        ->once()
        ->with($user, ['name' => 'New Name', 'email' => 'new@example.com', 'email_verified_at' => null])
        ->andReturn($user);

    $action = new UpdateProfileAction($users);

    $action->execute($user, new UpdateProfileData(
        name: 'New Name',
        email: 'new@example.com',
    ));
});

it('keeps email_verified_at untouched when the email address is unchanged', function () {
    $user = User::factory()->make(['email' => 'same@example.com']);

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('update')
        ->once()
        ->with($user, ['name' => 'New Name', 'email' => 'same@example.com'])
        ->andReturn($user);

    $action = new UpdateProfileAction($users);

    $action->execute($user, new UpdateProfileData(
        name: 'New Name',
        email: 'same@example.com',
    ));
});
