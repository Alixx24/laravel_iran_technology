
# Laravel GitHub OAuth Login & Repository Sync API

This project is a Laravel-based API that allows users to log in using GitHub OAuth. After authentication, it synchronizes the user's public repositories from GitHub and stores them in the database. The synchronization process runs in the background using Laravel queues for better performance.

---

## Features

- GitHub OAuth login using Laravel Socialite
- User creation and login via GitHub credentials
- Fetch and store user's public repositories with details:
  - Name
  - Description
  - URL
  - Star count
  - Last updated date
  - Active status (inactive for private or deleted repos)
- Background synchronization of repositories using Laravel Job Queue
- API token generation with Laravel Sanctum

---

## Requirements

- PHP 8.2 or higher  
- Laravel 12.x  
- Composer  
- MySQL or other supported database  
- GitHub OAuth App credentials (Client ID and Client Secret)  
- Queue driver (database, Redis, etc.)  

---

## Installation

1. Clone the repository:

```bash
git clone <your-repo-url>
cd <project-directory>
```

2. Install dependencies:

```bash
composer install
```

3. Copy `.env.example` to `.env` and set your environment variables, including GitHub credentials:

```env
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT=http://localhost:8000/api/auth/github/callback
QUEUE_CONNECTION=database

DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. Run migrations and queue table setup:

```bash
php artisan queue:table
php artisan migrate
```

5. Start the queue worker:

```bash
php artisan queue:work
```

6. Run the Laravel development server:

```bash
php artisan serve
```

---

## Usage

* Request a GitHub login URL from `/api/auth/github/redirect`.
* Authenticate via GitHub.
* After successful authentication, the user will be created or updated.
* Repository synchronization will be dispatched as a background job.
* Use the returned API token to authenticate further API requests.

---

## Notes

* Private and deleted repositories are marked as inactive in the database.
* Adjust the GitHub redirect URI in your GitHub OAuth App settings according to your environment.
* You can switch queue drivers as needed (e.g., Redis for production).

---

## Resources

* [Laravel Documentation](https://laravel.com/docs/12.x)
* [Laravel Socialite](https://laravel.com/docs/12.x/socialite)
* [GitHub OAuth Apps](https://docs.github.com/en/developers/apps/building-oauth-apps)
* [Laravel Queues](https://laravel.com/docs/12.x/queues)

---

## License

This project is open source and licensed under the MIT License.




