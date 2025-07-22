<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Repository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Jobs\SyncGithubRepositories;

class SocialAuthController extends Controller
{
    public function redirect(): JsonResponse
    {
        $url = Socialite::driver('github')->stateless()->redirect()->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function callback(): JsonResponse
    {
        try {
            $githubUser = Socialite::driver('github')->stateless()->user();
            $email = $githubUser->getEmail();

            if (!$email) {
                return response()->json(['error' => 'ایمیل از گیت‌هاب دریافت نشد!'], 422);
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                    'password' => Hash::make(Str::random(16)),
                    'github_id' => $githubUser->getId(),
                ]
            );

            $accessToken = $githubUser->token;

            // Dispatch Job → sync repositories in background
            dispatch(new SyncGithubRepositories($user, $accessToken));

            $token = $user->createToken('github-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'خطا در احراز هویت',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    private function fetchAndSaveRepositories(User $user, string $accessToken)
    {
        $response = Http::withHeaders([
            'Authorization' => "token $accessToken",
            'Accept' => 'application/vnd.github.v3+json',
        ])->get('https://api.github.com/user/repos', [
            'visibility' => 'all',
            'affiliation' => 'owner',
            'per_page' => 100,
        ]);

        if ($response->successful()) {
            $repos = $response->json();


            $fetchedNames = collect($repos)->pluck('name')->toArray();


            Repository::where('user_id', $user->id)
                ->whereNotIn('name', $fetchedNames)
                ->update(['active' => false]);

            foreach ($repos as $repo) {
                $isPublic = !$repo['private']; // فقط publicها را فعال نگه می‌داریم

                Repository::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $repo['name'],
                    ],
                    [
                        'description' => $repo['description'],
                        'url' => $repo['html_url'],
                        'stars' => $repo['stargazers_count'],
                        'last_updated_at' => \Carbon\Carbon::parse($repo['updated_at']),
                        'active' => $isPublic, // ← فقط ریپوهای عمومی فعال‌اند
                    ]
                );
            }
        }
    }


}
