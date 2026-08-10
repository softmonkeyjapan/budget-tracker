<?php

declare(strict_types=1);

namespace App\Domains\Feedback\Adapters;

use App\Domains\Feedback\Contracts\GithubIssueCreatorInterface;
use App\Domains\Feedback\Exceptions\GithubIssueCreationFailedException;
use Illuminate\Support\Facades\Http;
use Throwable;

final class GithubIssueCreator implements GithubIssueCreatorInterface
{
    /**
     * @param  array<int, string>  $labels
     */
    public function create(string $title, string $body, array $labels = []): void
    {
        try {
            $response = Http::withToken(config('services.github.token'))
                ->post(sprintf('https://api.github.com/repos/%s/issues', config('services.github.repo')), [
                    'title' => $title,
                    'body' => $body,
                    'labels' => $labels,
                ]);
        } catch (Throwable) {
            throw new GithubIssueCreationFailedException;
        }

        if (! $response->successful()) {
            throw new GithubIssueCreationFailedException;
        }
    }
}
