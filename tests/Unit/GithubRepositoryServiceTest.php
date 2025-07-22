<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GithubRepositoryService;

class GithubRepositoryServiceTest extends TestCase
{
    public function test_repository_is_active_when_private_false()
    {
        $service = new GithubRepositoryService();

        $repo = ['private' => false];
        $this->assertTrue($service->isRepositoryActive($repo));
    }

    public function test_repository_is_not_active_when_private_true()
    {
        $service = new GithubRepositoryService();

        $repo = ['private' => true];
        $this->assertFalse($service->isRepositoryActive($repo));
    }

    public function test_repository_is_not_active_when_private_not_set()
    {
        $service = new GithubRepositoryService();

        $repo = [];
        $this->assertFalse($service->isRepositoryActive($repo));
    }
}
