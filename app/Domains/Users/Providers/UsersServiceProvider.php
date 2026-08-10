<?php

declare(strict_types=1);

namespace App\Domains\Users\Providers;

use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Domains\Users\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

final class UsersServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => EloquentUserRepository::class,
    ];
}
