<?php

use App\Domains\Users\Actions\UpdatePasswordAction;
use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates the user with a newly hashed password', function () {
    $user = User::factory()->make();

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('update')
        ->once()
        ->withArgs(fn (User $u, array $data) => $u === $user && Hash::check('new-secret', $data['password']))
        ->andReturn($user);

    $action = new UpdatePasswordAction($users);

    $action->execute($user, 'new-secret');
});
