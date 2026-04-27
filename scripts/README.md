# Euro Taxi System - Deployment Scripts

## Quick Deployment Options

### Option 1: Batch File (Windows)
```bash
# Double-click or run from command line
scripts/deploy.bat
```

### Option 2: Node.js Deployer
```bash
# From project root
cd scripts
npm run deploy

# Or with custom commit message
npm run deploy -- "Fixed critical bugs and added new features"

# Or directly with Node
node deployer.js "Your custom commit message"
```

## Prerequisites

1. **Git must be installed** - Download from https://git-scm.com/download/win
2. **Git must be in your system PATH**
3. **Node.js** (for Node.js deployer) - Download from https://nodejs.org

## Features

### Batch File (deploy.bat)
- ✅ Simple double-click deployment
- ✅ Automatic Git status check
- ✅ Interactive commit messages
- ✅ Error handling with clear messages
- ✅ No additional dependencies

### Node.js Deployer (deployer.js)
- ✅ Better error handling
- ✅ Real-time command output
- ✅ Custom commit messages via command line
- ✅ Cross-platform compatibility
- ✅ Detailed logging

## Usage Examples

### Simple Deployment
```bash
# Batch file - just double-click and follow prompts
scripts/deploy.bat

# Node.js - with default message
node scripts/deployer.js
```

### Custom Commit Message
```bash
# Node.js - with custom message
node scripts/deployer.js "Fixed GROUP BY and ambiguous column SQL errors"

# Batch file - will prompt for message
scripts/deploy.bat
```

## What These Scripts Do

1. **Check Git repository** - Verify you're in the right directory
2. **Check Git installation** - Ensure Git is available
3. **Check current status** - Show what files will be committed
4. **Add all changes** - `git add .`
5. **Commit changes** - `git commit -m "message"`
6. **Push to GitHub** - `git push origin main`

## Troubleshooting

### "Git is not installed"
- Install Git from https://git-scm.com/download/win
- Restart your command prompt after installation

### "Not a Git repository"
- Make sure you're running from the project root directory
- Look for the `.git` folder in your project

### "Failed to push to GitHub"
- Check internet connection
- Verify GitHub credentials
- Check repository permissions

### "Authentication failed"
- Set up GitHub credentials:
  ```bash
  git config --global user.name "Your Name"
  git config --global user.email "your.email@example.com"
  ```

## Automation

### Create Desktop Shortcut
1. Right-click on desktop → New → Shortcut
2. Location: `C:\xampp\htdocs\eurotaxisystem\scripts\deploy.bat`
3. Name: "Deploy Euro Taxi System"
4. Double-click to deploy anytime!

### Add to PATH (optional)
Add `C:\xampp\htdocs\eurotaxisystem\scripts` to system PATH for easier access.

## Security Notes

- Scripts use your existing Git credentials
- No sensitive information is stored
- All operations are standard Git commands
- Scripts are safe to run from trusted repository
