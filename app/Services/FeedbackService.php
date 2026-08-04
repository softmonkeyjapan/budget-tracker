<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

final class FeedbackService
{
    public function __construct(
        private readonly GithubIssueService $github,
        private readonly FeedbackClassificationService $classifier,
    ) {}

    /**
     * @param  array{message: string, page_url: string}  $data
     */
    public function submit(User $user, array $data, ?string $userAgent): void
    {
        $classification = $this->classifier->classify($data['message']);

        $title = $classification['title'] ?? $this->fallbackTitle($data['message']);
        $labels = $classification !== null ? [$classification['type']] : [];
        $body = $this->buildBody($data['message'], $data['page_url'], $user, $userAgent);

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
