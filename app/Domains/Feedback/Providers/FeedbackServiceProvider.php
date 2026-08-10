<?php

declare(strict_types=1);

namespace App\Domains\Feedback\Providers;

use App\Domains\Feedback\Adapters\AnthropicFeedbackClassifier;
use App\Domains\Feedback\Adapters\GithubIssueCreator;
use App\Domains\Feedback\Contracts\FeedbackClassifierInterface;
use App\Domains\Feedback\Contracts\GithubIssueCreatorInterface;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class FeedbackServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        FeedbackClassifierInterface::class => AnthropicFeedbackClassifier::class,
        GithubIssueCreatorInterface::class => GithubIssueCreator::class,
    ];

    public function boot(): void
    {
        Gate::define('access-feedback', fn (User $user): bool => $user->isAdmin());
    }
}
