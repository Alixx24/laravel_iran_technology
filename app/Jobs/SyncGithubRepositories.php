<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Repository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class SyncGithubRepositories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;
    protected string $accessToken;

    public function __construct(User $user, string $accessToken)
    {
        $this->user = $user;
        $this->accessToken = $accessToken;
    }

    public function handle(): void
    {
        $response = Http::withHeaders([
            'Authorization' => "token {$this->accessToken}",
            'Accept' => 'application/vnd.github.v3+json',
        ])->get('https://api.github.com/user/repos', [
            'visibility' => 'all',
            'affiliation' => 'owner',
            'per_page' => 100,
        ]);

        if (!$response->successful()) {
            return;
        }

        $repos = $response->json();

        $repoNames = collect($repos)->pluck('name')->toArray();


        Repository::where('user_id', $this->user->id)
            ->whereNotIn('name', $repoNames)
            ->update(['active' => false]);

        foreach ($repos as $repo) {
            $isPublic = !$repo['private'];

            Repository::updateOrCreate(
                ['user_id' => $this->user->id, 'name' => $repo['name']],
                [
                    'description' => $repo['description'],
                    'url' => $repo['html_url'],
                    'stars' => $repo['stargazers_count'],
                    'last_updated_at' => Carbon::parse($repo['updated_at']),
                    'active' => $isPublic,
                ]
            );
        }
    }
}
