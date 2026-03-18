# 🚀 EuroTaxi - GitHub Integration Setup Guide

## ✅ Completed Setup Steps

1. ✅ Git installed and initialized
2. ✅ Initial commit created and pushed to GitHub
3. ✅ GitHub OAuth authentication controller created
4. ✅ GitHub API integration service created
5. ✅ GitHub Actions CI/CD pipelines configured
6. ✅ Routes configured for GitHub integration

---

## 📋 Next Steps for Complete Integration

### Step 1: Update `.env` file

Add these GitHub configuration variables to your `.env` file:

```env
# GitHub OAuth Configuration
GITHUB_CLIENT_ID=your_client_id_here
GITHUB_CLIENT_SECRET=your_client_secret_here
GITHUB_CALLBACK_URL=http://localhost:8000/auth/github/callback

# GitHub API Token
GITHUB_API_TOKEN=your_personal_access_token_here

# Repository Info
GITHUB_REPO_OWNER=Sony0012
GITHUB_REPO_NAME=eurotaxisystem
```

### Step 2: Create GitHub OAuth Application

1. Go to: https://github.com/settings/developers
2. Click "OAuth Apps" → "New OAuth App"
3. Fill in:
   - **Application name:** EuroTaxi System
   - **Homepage URL:** http://localhost:8000
   - **Authorization callback URL:** http://localhost:8000/auth/github/callback
4. Copy the `Client ID` and `Client Secret` to your `.env` file

### Step 3: Generate Personal Access Token

1. Go to: https://github.com/settings/tokens/new
2. Select scopes:
   - ✅ `repo` (Full control of private repositories)
   - ✅ `workflow` (Update GitHub Actions workflows)
   - ✅ `user` (Read user profile data)
   - ✅ `read:repo_hook` (Read repository hooks)
3. Copy the token to `.env`:
   ```env
   GITHUB_API_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxxx
   ```

### Step 4: Install Laravel Socialite

```bash
composer require laravel/socialite
php artisan vendor:publish --provider="Laravel\Socialite\SocialiteServiceProvider"
```

### Step 5: Configure `config\services.php`

Add GitHub configuration:

```php
'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_CALLBACK_URL'),
    'token' => env('GITHUB_API_TOKEN'),
],
```

### Step 6: Update User Model

Add GitHub columns to users table migration:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('github_id')->nullable()->unique();
    $table->string('github_token')->nullable();
    $table->string('github_refresh_token')->nullable();
});
```

Run migration:
```bash
php artisan migrate
```

### Step 7: Configure GitHub Actions Secrets

In your GitHub repository:

1. Go to: Settings → Secrets and variables → Actions
2. Add these secrets:
   - `DEPLOY_SSH_KEY` - Your server SSH private key
   - `DEPLOY_SERVER_IP` - Your server IP address
   - `DEPLOY_USER` - SSH username
   - `SLACK_WEBHOOK` - (Optional) Slack webhook for notifications

---

## 🔑 Features Now Available

### 1. GitHub OAuth Login
- **Route:** `/auth/github`
- Users can login with their GitHub account

### 2. GitHub API Integration
Available endpoints:

```
GET  /api/github/stats             - Repository statistics
GET  /api/github/commits           - Latest commits
GET  /api/github/pulls             - Pull requests
GET  /api/github/issues            - Issues
POST /api/github/issue             - Create issue
GET  /api/github/contributors      - Contributors list
GET  /api/github/workflow/{id}     - Workflow status
POST /api/github/workflow/trigger  - Trigger workflow
```

### 3. CI/CD Pipelines (GitHub Actions)

**Automated on every push to `main` or `develop`:**

1. **tests.yml** - Run automated tests
   - PHP 8.1, 8.2 compatibility
   - MySQL test database
   - PHPUnit tests
   - Code coverage upload

2. **code-quality.yml** - Code quality checks
   - PHPStan static analysis
   - Pint code style checking
   - LaraStand analysis

3. **deploy.yml** - Production deployment
   - SSH deployment to server
   - Post-deployment notifications
   - Slack integration

---

## 🛠️ Usage Examples

### Login with GitHub (in your login view)

```html
<a href="{{ route('auth.github') }}" class="btn btn-primary">
    Login with GitHub
</a>
```

### Get Repository Statistics (API call)

```javascript
fetch('/api/github/stats')
    .then(res => res.json())
    .then(data => console.log('Repo stats:', data));
```

### Get Latest Commits

```javascript
fetch('/api/github/commits?branch=main&limit=5')
    .then(res => res.json())
    .then(data => console.log('Latest commits:', data));
```

### Create an Issue programmatically

```javascript
fetch('/api/github/issue', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        title: 'New bug report',
        description: 'Bug description here',
        labels: ['bug', 'urgent']
    })
})
.then(res => res.json())
.then(data => console.log('Issue created:', data));
```

### Trigger GitHub Actions Workflow

```javascript
fetch('/api/github/workflow/trigger', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        workflow_id: 'tests.yml'
    })
})
.then(res => res.json())
.then(data => console.log('Workflow triggered:', data));
```

---

## 📁 GitHub Integration Files Created

```
.github/
├── workflows/
│   ├── tests.yml              # Automated testing pipeline
│   ├── code-quality.yml       # Code quality checks
│   └── deploy.yml             # Production deployment

app/
├── Http/Controllers/
│   ├── GitHubAuthController.php         # OAuth login handler
│   └── GitHubIntegrationController.php  # GitHub API endpoints
└── Services/
    └── GitHubService.php               # GitHub API communication

routes/
└── web.php                             # Updated with GitHub routes

config/
└── .env.github                         # GitHub configuration template
```

---

## 🔒 Security Notes

⚠️ **Never commit these to Git:**
- `.env` files with tokens
- SSH private keys
- Client secrets

✅ **Always use:**
- Environment variables for secrets
- Personal Access Tokens (not passwords)
- SSH key authentication for deployments
- HTTPS for callbacks

---

## 🐛 Troubleshooting

### Issue: "GitHub API request failed"
**Solution:** Check your `GITHUB_API_TOKEN` is valid and has correct permissions

### Issue: "OAuth callback not working"
**Solution:** Verify `GITHUB_CALLBACK_URL` matches exactly in GitHub app settings

### Issue: "GitHub Actions not triggering"
**Solution:** Ensure `workflow_id` matches the workflow filename (e.g., `tests.yml`)

### Issue: "Deployment fails silently"
**Solution:** Check GitHub repository settings → Add deploy secrets correctly

---

## 📚 Resources

- [GitHub OAuth Documentation](https://docs.github.com/en/developers/apps/building-oauth-apps)
- [GitHub API Documentation](https://docs.github.com/en/rest)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Socialite Documentation](https://laravel.com/docs/socialite)

---

**Last Updated:** March 18, 2026  
**Status:** ✅ Ready for deployment
