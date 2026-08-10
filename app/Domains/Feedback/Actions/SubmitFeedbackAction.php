<?php

declare(strict_types=1);

namespace App\Domains\Feedback\Actions;

use App\Domains\Feedback\Contracts\FeedbackClassifierInterface;
use App\Domains\Feedback\Contracts\GithubIssueCreatorInterface;
use App\Domains\Feedback\DataTransferObjects\SubmitFeedbackData;
use App\Models\User;
use Illuminate\Support\Str;

final class SubmitFeedbackAction
{
    public function __construct(
        private readonly GithubIssueCreatorInterface $github,
        private readonly FeedbackClassifierInterface $classifier,
    ) {}

    public function execute(User $user, SubmitFeedbackData $data, ?string $userAgent): void
    {
        $classification = $this->classifier->classify($data->message);

        $title = $classification['title'] ?? $this->fallbackTitle($data->message);
        $labels = $classification !== null ? [$classification['type']] : [];
        $body = $this->buildBody($data->message, $data->pageUrl, $user, $userAgent);

        $this->github->create($title, $body, $labels);
    }

    private function fallbackTitle(string $message): string
    {
        return Str::limit(trim(strtok($message, "\n")), 80);
    }

    private function buildBody(string $message, string $pageUrl, User $user, ?string $userAgent): string
    {
        return implode("\n", [
            $message,
            '',
            '---',
            "**Page** : {$pageUrl}",
            "**Utilisateur** : {$user->name} ({$user->email})",
            '**Navigateur** : '.($userAgent ?? 'inconnu'),
            '**Date** : '.now()->toDateTimeString(),
        ]);
    }
}
