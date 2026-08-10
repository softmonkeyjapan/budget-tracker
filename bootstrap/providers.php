<?php

use App\Domains\Incomes\Providers\IncomesServiceProvider;
use App\Domains\Users\Providers\UsersServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    UsersServiceProvider::class,
    IncomesServiceProvider::class,
];
