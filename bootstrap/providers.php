<?php

use App\Domains\Categories\Providers\CategoriesServiceProvider;
use App\Domains\Expenses\Providers\ExpensesServiceProvider;
use App\Domains\Feedback\Providers\FeedbackServiceProvider;
use App\Domains\Incomes\Providers\IncomesServiceProvider;
use App\Domains\Users\Providers\UsersServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    UsersServiceProvider::class,
    IncomesServiceProvider::class,
    CategoriesServiceProvider::class,
    ExpensesServiceProvider::class,
    FeedbackServiceProvider::class,
];
