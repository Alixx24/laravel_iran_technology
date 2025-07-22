<?php

namespace App\Services;

class GithubRepositoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function isRepositoryActive(array $repo): bool
    {
        return !($repo['private'] ?? true);
    }

}
