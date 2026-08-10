<?php

use App\Domains\Users\Actions\DeleteAccountAction;
use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;

it('deletes the given user', function () {
    $user = User::factory()->make();

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('delete')->once()->with($user);

    $action = new DeleteAccountAction($users);

    $action->execute($user);
});
