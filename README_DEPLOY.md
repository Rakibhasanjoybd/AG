# 🎯 AUTO-DEPLOY SETUP - READY TO GO LIVE

## Current Status

✅ GitHub repository created: https://github.com/Rakibhasanjoybd/AG  
✅ Git initialized with all files locally  
✅ `.github/workflows/deploy.yml` ready (auto-deploy workflow)  
✅ `deploy.sh` ready (server-side deploy script)  
✅ All documentation created  

---

## What Happens When You Deploy

```
git push origin main
       ↓
GitHub Actions auto-triggers
       ↓
Zips entire repo (excludes .git, node_modules, etc.)
       ↓
SCPs zip to your server: ftp.agcolimited.uk:/var/www/html/agco/
       ↓
SSH into server and extracts files
       ↓
Runs deploy.sh (migrations, composer install, permissions, cache clear)
       ↓
Your site is LIVE with latest code
```

**Time: ~2-3 minutes per deployment**

---

## 🔥 GO LIVE IN 2 STEPS

### Step 1: Add GitHub Secrets (3 minutes)

**Link:** https://github.com/Rakibhasanjoybd/AG/settings/secrets/actions

Add these 5 secrets (click "New repository secret" each time):

1. `DEPLOY_HOST` = `ftp.agcolimited.uk`
2. `DEPLOY_USER` = `agco`
3. `DEPLOY_PORT` = `22`
4. `DEPLOY_PATH` = `/var/www/html/agco`
5. `DEPLOY_SSH_KEY` = (full id_rsa private key content)

### Step 2: Server SSH Setup (2 minutes)

```bash
# SSH to server
ssh agco@ftp.agcolimited.uk

# Add public key (one line)
echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDZ7Pyjro5ZIzkDs/Vr+G1ZBHJO1EBHUHXKNjNEFecfKWAn+pxtpeLJBhG9l+88lmeG7CMB8qPxTGRTrVjzQ85Owgg52I7oWwIsKO4EQ+IDfVN8uiT4zzYsZr2pk1M3jkQ96DEVfRMUkcpBxF1dQjXUp5dSgkvEpvgdqsSf8VQqRqCpAk5oRErMUAodUiszu7/9IaCsPk6GoKuoVr8hTzUp0/1jzDla2O+lqWoIBFRhQieX/ICPAeJzHDLCAgYcgixlAYnRGztqi594lxPLD9j2moxMzH2Vxc6Pi1IE7mwl16xB6/j8m3SYvRWaMzPRvqaXta3U91IXNImQFvZ4tz9J" >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && exit
```

Then from your machine:

```powershell
scp deploy.sh agco@ftp.agcolimited.uk:/var/www/html/agco/
ssh agco@ftp.agcolimited.uk chmod +x /var/www/html/agco/deploy.sh
```

---

## ✅ Test Deploy

```powershell
cd D:\xampp\htdocs\AGCO

# Any change will trigger deploy
echo "# Test" >> README.md

git add .
git commit -m "test deploy"
git push origin main
```

**Watch it live:** https://github.com/Rakibhasanjoybd/AG/actions

---

## 🎉 AFTER FIRST SUCCESSFUL DEPLOY

**You're done forever!**

Every `git push origin main` will:
- ✅ Automatically zip your code
- ✅ Copy to server
- ✅ Run migrations & setup
- ✅ Go live in 2-3 minutes

No more manual uploads. No more WinSCP. Fully automated.

---

## 📞 Questions?

See:
- `GO_LIVE_NOW.md` — Step-by-step with copy-paste commands
- `DEPLOY_YOUR_SERVER_NOW.md` — Setup details
- `AUTO_DEPLOY_START_HERE.md` — Full guide

---

## Next Action

→ **Read: [GO_LIVE_NOW.md](GO_LIVE_NOW.md)** and follow the 3 steps

**That's it. You'll be live in 5 minutes.**
