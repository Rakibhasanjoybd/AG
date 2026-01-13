# 🚀 GITHUB ACTIONS AUTO-DEPLOY - COMPLETE

## What You Have

✅ **Fully automated GitHub Actions deployment system**
✅ **Every `git push origin main` deploys to your server**
✅ **Zero manual uploads needed**
✅ **Ready in 5 minutes**

---

## Your System

- **Repo:** https://github.com/Rakibhasanjoybd/AG
- **Server:** ftp.agcolimited.uk
- **User:** agco
- **Deploy Path:** /var/www/html/agco
- **Workflow:** `.github/workflows/deploy.yml` (auto-runs on push to main)

---

## How It Works

1. You push code to GitHub
2. GitHub Actions automatically:
   - Archives your entire repo
   - Copies to your server via SCP
   - SSH into server and extracts files
   - Runs `deploy.sh` (migrations, permissions, caching)
3. Site updates live (~2-3 min)

---

## 5-Minute Setup

### Step 1: Add GitHub Secrets (Copy-Paste)

Go to: **https://github.com/Rakibhasanjoybd/AG/settings/secrets/actions**

Add 5 secrets (click "New repository secret" for each):

```
Name: DEPLOY_HOST
Value: ftp.agcolimited.uk
```

```
Name: DEPLOY_USER
Value: agco
```

```
Name: DEPLOY_PORT
Value: 22
```

```
Name: DEPLOY_PATH
Value: /var/www/html/agco
```

```
Name: DEPLOY_SSH_KEY
Value: (paste full id_rsa private key)
```

### Step 2: Setup Server SSH

Run on your machine:

```bash
ssh agco@ftp.agcolimited.uk
```

Then on server, paste this one line:

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh && echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDZ7Pyjro5ZIzkDs/Vr+G1ZBHJO1EBHUHXKNjNEFecfKWAn+pxtpeLJBhG9l+88lmeG7CMB8qPxTGRTrVjzQ85Owgg52I7oWwIsKO4EQ+IDfVN8uiT4zzYsZr2pk1M3jkQ96DEVfRMUkcpBxF1dQjXUp5dSgkvEpvgdqsSf8VQqRqCpAk5oRErMUAodUiszu7/9IaCsPk6GoKuoVr8hTzUp0/1jzDla2O+lqWoIBFRhQieX/ICPAeJzHDLCAgYcgixlAYnRGztqi594lxPLD9j2moxMzH2Vxc6Pi1IE7mwl16xB6/j8m3SYvRWaMzPRvqaXta3U91IXNImQFvZ4tz9J" >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && exit
```

Back on your machine:

```powershell
scp deploy.sh agco@ftp.agcolimited.uk:/var/www/html/agco/deploy.sh
ssh agco@ftp.agcolimited.uk chmod +x /var/www/html/agco/deploy.sh
```

### Step 3: Test Deploy

```powershell
cd D:\xampp\htdocs\AGCO

# Make any change
echo "# Deploy test" >> README.md

git add .
git commit -m "test deploy"
git push origin main

# Watch: https://github.com/Rakibhasanjoybd/AG/actions
```

---

## After Setup

**Every push to main deploys automatically.**

No more manual steps. No more WinSCP. Fully hands-off.

---

## Files You Got

| File | Purpose |
|------|---------|
| `.github/workflows/deploy.yml` | Workflow (runs on push) |
| `deploy.sh` | Server script (migrations, perms, cache) |
| `START_HERE_COPY_PASTE.md` | Copy-paste instructions |
| `GO_LIVE_NOW.md` | Quick setup |
| `AUTO_DEPLOY_START_HERE.md` | Full guide |
| `DEPLOY_YOUR_SERVER_NOW.md` | Server setup details |
| Other docs | Reference & troubleshooting |

---

## Quick Troubleshooting

| Issue | Fix |
|-------|-----|
| "Permission denied" | Check public key in `~/.ssh/authorized_keys` on server |
| Workflow fails | Check GitHub Actions logs |
| SSH timeout | Verify server SSH is running; check firewall |
| Files not updating | Verify DEPLOY_PATH is correct |

---

## You're Ready

Start with: **START_HERE_COPY_PASTE.md**

5 minutes to fully automated deployments.
