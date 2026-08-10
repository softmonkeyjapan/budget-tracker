<?php

use App\Domains\Feedback\Actions\SubmitFeedbackAction;
use App\Domains\Feedback\Contracts\FeedbackClassifierInterface;
use App\Domains\Feedback\Contracts\GithubIssueCreatorInterface;
use App\Domains\Feedback\DataTransferObjects\SubmitFeedbackData;
use App\Models\User;

it('creates a github issue using the ai-classified label and title', function () {
    $user = User::factory()->make(['name' => 'Loic', 'email' => 'loic@example.com']);

    $classifier = Mockery::mock(FeedbackClassifierInterface::class);
    $classifier->shouldReceive('classify')
        ->once()
        ->with('Bug sur le dashboard')
        ->andReturn(['type' => 'bug', 'title' => 'Bug sur le dashboard']);

    $github = Mockery::mock(GithubIssueCreatorInterface::class);
    $github->shouldReceive('create')
        ->once()
        ->with(
            'Bug sur le dashboard',
            Mockery::on(fn (string $body) => str_contains($body, $user->email) && str_contains($body, '/dashboard')),
            ['bug'],
        );

    $action = new SubmitFeedbackAction($github, $classifier);

    $action->execute(
        $user,
        new SubmitFeedbackData(message: 'Bug sur le dashboard', pageUrl: '/dashboard'),
        'Mozilla/5.0',
    );
});

it('falls back to a truncated title and no labels when classification fails', function () {
    $user = User::factory()->make(['name' => 'Loic', 'email' => 'loic@example.com']);

    $classifier = Mockery::mock(FeedbackClassifierInterface::class);
    $classifier->shouldReceive('classify')->once()->andReturn(null);

    $github = Mockery::mock(GithubIssueCreatorInterface::class);
    $github->shouldReceive('create')
        ->once()
        ->with('Le total ne se met pas à jour', Mockery::type('string'), []);

    $action = new SubmitFeedbackAction($github, $classifier);

    $action->execute(
        $user,
        new SubmitFeedbackData(message: 'Le total ne se met pas à jour', pageUrl: '/dashboard'),
        null,
    );
});
