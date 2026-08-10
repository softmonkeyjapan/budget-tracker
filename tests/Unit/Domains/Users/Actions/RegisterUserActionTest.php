<?php

use App\Domains\Users\Actions\RegisterUserAction;
use App\Domains\Users\DataTransferObjects\RegisterUserData;
use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('creates a user with a hashed password and fires Registered', function () {
    Event::fake();

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('create')
        ->once()
        ->withArgs(fn (array $data) => $data['name'] === 'Jane Doe'
            && $data['email'] === 'jane@example.com'
            && Hash::check('secret-password', $data['password']))
        ->andReturn(new User(['name' => 'Jane Doe', 'email' => 'jane@example.com']));

    $action = new RegisterUserAction($users);

    $user = $action->execute(new RegisterUserData(
        name: 'Jane Doe',
        email: 'jane@example.com',
        password: 'secret-password',
    ));

    expect($user->name)->toBe('Jane Doe');
    Event::assertDispatched(Registered::class);
});
