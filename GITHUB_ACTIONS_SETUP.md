# GitHub Actions Auto-Deploy Setup Guide

## ✅ Complete Setup (5 minutes)

### Step 1: Generate SSH Keys (Run on your local machine)

#### Windows (PowerShell):
```powershell
# Generate ED25519 key (recommended)
ssh-keygen -t ed25519 -f "$env:USERPROFILE\.ssh\deploy_key" -N ""

# Display the private key for GitHub Secret
Get-Content "$env:USERPROFILE\.ssh\deploy_key"
```

#### Mac/Linux:
```bash
ssh-keygen -t ed25519 -f ~/.ssh/deploy_key -N ""
cat ~/.ssh/deploy_key
```

**Save the output — you'll paste it into GitHub Secrets as `DEPLOY_SSH_KEY`.**

---

### Step 2: Copy Public Key to Server

The public key (deploy_key.pub) must be added to your server:

```bash
# On your local machine, get the public key:
cat ~/.ssh/deploy_key.pub          # Mac/Linux
Get-Content "$env:USERPROFILE\.ssh\deploy_key.pub"  # Windows PowerShell
```

**Then on your server:**
```bash
# SSH into your server
ssh user@your.server.com

# Add the public key to authorized_keys
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "PASTE_PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

---

### Step 3: Add GitHub Repository Secrets

Go to: **GitHub → Your Repo → Settings → Secrets and Variables → Actions → New Repository Secret**

Add these 5 secrets:

| Secret Name | Value | Example |
|-------------|-------|---------|
| `DEPLOY_HOST` | Your server hostname or IP | `example.com` or `192.168.1.100` |
| `DEPLOY_USER` | SSH username | `deploy` or `www-data` |
| `DEPLOY_SSH_KEY` | Private SSH key (full contents of deploy_key file) | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `DEPLOY_PORT` | SSH port (optional, default 22) | `22` or `2222` |
| `DEPLOY_PATH` | Absolute path on server | `/var/www/html/agco` or `/home/deploy/agco` |

**Note:** Make sure `deploy.sh` exists in your `DEPLOY_PATH` on the server, or the workflow will still work but won't run custom tasks.

---

### Step 4: Verify Server Requirements

Make sure your server has:

```bash
# SSH daemon running (usually already there)
sudo systemctl status ssh

# unzip installed
which unzip
# If missing:
sudo apt-get install unzip  # Debian/Ubuntu
sudo yum install unzip      # CentOS/RHEL

# Optional: PHP & Composer (for Laravel/PHP projects)
php -v
composer --version

# Optional: MySQL client (if running migrations)
mysql --version
```

---

### Step 5: Test the Deploy

1. **Make a small change** to your repo and **push to main** (or configured branch):
   ```bash
   git add .
   git commit -m "test auto-deploy"
   git push origin main
   ```

2. **Watch the workflow** in GitHub:
   - Go to: **Repo → Actions → Auto Deploy to Server** → Latest run
   - Click the job to see logs in real-time

3. **Verify on server**:
   ```bash
   ssh user@your.server.com
   cd /path/to/deploy
   ls -la  # Should see latest files
   ```

---

## 🔧 Customizing the Deploy

### To run custom commands after deploy:

Edit `/deploy.sh` on your server to add any custom logic:
- Database migrations
- Asset compilation
- Cache clearing
- Restart services
- Etc.

### To exclude files from deployment:

Edit `.github/workflows/deploy.yml` zip command:
```yaml
zip -r deploy.zip . -x "*.git*" ".github/*" "node_modules/*" "config/*" ".env*"
```

---

## 🆘 Troubleshooting

| Issue | Solution |
|-------|----------|
| **"Connection refused"** | Check DEPLOY_HOST, DEPLOY_PORT, and that SSH is running on server |
| **"Permission denied (publickey)"** | Verify public key is in `~/.ssh/authorized_keys` on server; check file permissions (600 for authorized_keys, 700 for .ssh) |
| **"unzip: command not found"** | Install unzip: `sudo apt-get install unzip` |
| **Workflow stuck** | Check GitHub Actions logs; may be waiting on user input or SSH timeout |
| **Files not updating** | Check DEPLOY_PATH is correct; verify zip was created; SSH into server and check `/tmp` for error logs |

---

## 🎯 What Happens on Each Push

1. ✅ GitHub checks out your repo
2. ✅ Creates `deploy.zip` (excludes .git, node_modules, etc.)
3. ✅ Copies zip to server via SCP
4. ✅ SSH into server and unzip files
5. ✅ Runs `./deploy.sh` (migrations, permissions, caches, etc.)
6. ✅ Done! Site is live with latest code

---

## 🚀 Ready?

1. Generate SSH key ✓
2. Add public key to server ✓
3. Add 5 secrets to GitHub ✓
4. Test with a git push ✓

**That's it. Every push to main auto-deploys.**
