# 🚀 GitHub Actions Auto-Deploy - Complete Setup

**Setup time: ~5 minutes | Once configured, every push to `main` auto-deploys**

---

## 📋 Overview

This setup creates a fully automated deployment pipeline:

```
You: git push to main
    ↓
GitHub Actions: Archive repo + SCP to server + SSH unzip & run deploy.sh
    ↓
Your Server: Files updated + migrations run + permissions fixed + live! ✅
```

**No manual uploads. No WinSCP. Fully automated.**

---

## ⚡ Quick Start (3 Steps)

### Step 1: Generate SSH Key (Local Machine)

**Windows (PowerShell):**
```powershell
ssh-keygen -t ed25519 -f "$env:USERPROFILE\.ssh\deploy_key" -N ""
```

**Or use the batch script:**
```powershell
.\scripts\generate-deploy-key.bat
```

**Mac/Linux:**
```bash
ssh-keygen -t ed25519 -f ~/.ssh/deploy_key -N ""
```

✅ **Result:** Two files created:
- `deploy_key` (private - keep secret!)
- `deploy_key.pub` (public - goes on server)

---

### Step 2: Prepare Server (Run Once)

**On your server, run:**
```bash
bash /path/to/server-setup.sh
```

Or manually:
```bash
# 1. Create SSH directory if needed
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# 2. Add your public key
echo "PASTE_CONTENTS_OF_deploy_key.pub" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# 3. Verify unzip is installed
which unzip  # If missing: sudo apt install unzip
```

---

### Step 3: Add GitHub Secrets (GitHub Web UI or CLI)

**Via GitHub Web UI:**
1. Go: **GitHub → Your Repo → Settings → Secrets and Variables → Actions**
2. Click **"New repository secret"** and add these 5:

| Secret | Value |
|--------|-------|
| `DEPLOY_HOST` | Your server IP or hostname (e.g., `203.0.113.42` or `deploy.example.com`) |
| `DEPLOY_USER` | SSH user (e.g., `deploy` or `ubuntu`) |
| `DEPLOY_SSH_KEY` | **Full contents** of `deploy_key` (private key file) |
| `DEPLOY_PORT` | SSH port (usually `22`, or `2222` if custom) |
| `DEPLOY_PATH` | Absolute path on server (e.g., `/var/www/html/agco`) |

**Or via PowerShell CLI (faster):**
```powershell
.\scripts\setup-secrets.ps1
```
Then follow the prompts.

---

## ✅ Verify It Works

### 1. Test SSH Connection (from your machine)

```bash
ssh -i ~/.ssh/deploy_key deploy@your.server.com
# Or: ssh -i %USERPROFILE%\.ssh\deploy_key deploy@your.server.com
```

Should connect without password. If not, check:
- Public key is in `~/.ssh/authorized_keys` on server
- Permissions: `chmod 600 ~/.ssh/authorized_keys` and `chmod 700 ~/.ssh`
- SSH daemon is running: `sudo systemctl status ssh`

### 2. Test the Deploy

Make a small change and push:
```bash
git add .
git commit -m "test auto-deploy"
git push origin main
```

Watch the workflow:
1. Go to **GitHub → Actions tab**
2. Click **"Auto Deploy to Server"** → latest run
3. Watch logs in real-time
4. Should see: ✅ Checkout → ✅ Archive → ✅ SCP → ✅ SSH unzip → ✅ Deploy

### 3. Verify on Server

```bash
ssh deploy@your.server.com
cd /path/to/deploy
ls -la  # Should see latest files from your repo
```

---

## 📁 Files Created

| File | What It Does |
|------|-------------|
| `.github/workflows/deploy.yml` | GitHub Actions workflow (auto-triggered on push) |
| `deploy.sh` | Server-side script (migrations, perms, cache, etc.) |
| `GITHUB_ACTIONS_SETUP.md` | Full guide with troubleshooting |
| `AUTO_DEPLOY_CHECKLIST.md` | Quick checklist |
| `scripts/setup-secrets.ps1` | PowerShell helper to add secrets |
| `scripts/generate-deploy-key.bat` | Generate SSH key (Windows) |
| `scripts/server-setup.sh` | One-time server setup script |

---

## 🔧 Customization

### Run Custom Commands After Deploy

Edit `deploy.sh` on your server to add:
- Database migrations
- Artisan commands
- Cache clearing
- Asset compilation
- Service restarts
- Etc.

Example:
```bash
echo "Running custom tasks..."
cd /var/www/html/agco
php artisan migrate --force
php artisan cache:clear
chown -R www-data:www-data .
```

### Exclude Files from Deployment

Edit `.github/workflows/deploy.yml`, find the `zip` line, and add more exclusions:
```yaml
zip -r deploy.zip . -x "*.git*" ".github/*" "node_modules/*" ".env*" "config/*"
```

### Deploy to Different Branch

Edit `.github/workflows/deploy.yml`:
```yaml
on:
  push:
    branches:
      - main        # Change to: production, develop, etc.
```

---

## 🆘 Troubleshooting

| Issue | Solution |
|-------|----------|
| **"Permission denied (publickey)"** | Public key not in server's `~/.ssh/authorized_keys` or permissions wrong (`600` for file, `700` for dir) |
| **"Connection refused"** | Check DEPLOY_HOST is correct IP/hostname, SSH is running on server, DEPLOY_PORT is open |
| **"unzip: command not found"** | Install: `sudo apt install unzip` or `sudo yum install unzip` |
| **Workflow stuck at "Deploy"** | Check GitHub Actions logs; may be waiting or SSH timeout. SSH timeout usually means server isn't responding. |
| **Files not updating on server** | Check zip was created; verify DEPLOY_PATH is writable by DEPLOY_USER; check SSH logs: `sudo tail -f /var/log/auth.log` |
| **deploy.sh not found** | Copy `deploy.sh` to server in DEPLOY_PATH and make executable: `chmod +x deploy.sh` |

---

## 📞 Full Documentation

See [GITHUB_ACTIONS_SETUP.md](GITHUB_ACTIONS_SETUP.md) for complete guide, advanced options, and detailed troubleshooting.

---

## 🎯 Done!

After the initial 5-minute setup:

✅ Push to `main` → Automatically deploys to server  
✅ No manual steps  
✅ Logs visible in GitHub Actions  
✅ Rollback: just revert commit and push  

**Every push = instant production update.**
