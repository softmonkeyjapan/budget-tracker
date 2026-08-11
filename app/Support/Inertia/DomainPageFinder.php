<?php

declare(strict_types=1);

namespace App\Support\Inertia;

use Illuminate\View\FileViewFinder;
use InvalidArgumentException;

/**
 * Resolves Inertia component names of the form "{Domain}/{Page}" against
 * resources/js/Domains/{Domain}/Pages/{Page}.vue, mirroring the frontend
 * resolver in resources/js/app.js.
 */
final class DomainPageFinder extends FileViewFinder
{
    public function find($view)
    {
        if (isset($this->views[$view])) {
            return $this->views[$view];
        }

        [$domain, $page] = array_pad(explode('/', $view, 2), 2, null);

        if ($page === null) {
            throw new InvalidArgumentException("View [{$view}] has an invalid name.");
        }

        return $this->views[$view] = $this->findInPaths($page, [resource_path("js/Domains/{$domain}/Pages")]);
    }
}
