<?php

declare(strict_types=1);

namespace App\Domains\Feedback\Contracts;

interface GithubIssueCreatorInterface
{
    /**
     * @param  array<int, string>  $labels
     */
    public function create(string $title, string $body, array $labels = []): void;
}
