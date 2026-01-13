# 🚀 ACTIVATE AUTO-DEPLOY - FINAL STEPS

## Your Credentials (Ready to Use)

```
GitHub Repo: https://github.com/Rakibhasanjoybd/AG
FTP Server: ftp.agcolimited.uk
FTP User: agco@agcolimited.uk
Deploy Path: /home/amlfrogb/agcolimited.uk/agco
SSH Public Key: Already configured
Database: amlfrogb_agcoweb (localhost:3306)
DB User: amlfrogb_agcoweb
DB Pass: agcoweb
```

---

## STEP 1: Add GitHub Secrets (3 minutes)

**Go to:** https://github.com/Rakibhasanjoybd/AG/settings/secrets/actions

Click **"New repository secret"** for each (5 total):

### Secret 1
```
Name: DEPLOY_HOST
Value: ftp.agcolimited.uk
```
Click: Add secret

### Secret 2
```
Name: DEPLOY_USER
Value: agco
```
Click: Add secret

### Secret 3
```
Name: DEPLOY_PORT
Value: 22
```
Click: Add secret

### Secret 4
```
Name: DEPLOY_PATH
Value: /home/amlfrogb/agcolimited.uk/agco
```
Click: Add secret

### Secret 5
```
Name: DEPLOY_SSH_KEY
Value: [PASTE FULL id_rsa PRIVATE KEY FILE]
```
Click: Add secret

---

## STEP 2: SSH Setup on Server (2 minutes)

### Open PowerShell and run:

```powershell
ssh agco@ftp.agcolimited.uk
```

(You may need to enter your SSH passphrase)

### Once connected, paste this ONE LINE:

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh && echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDZ7Pyjro5ZIzkDs/Vr+G1ZBHJO1EBHUHXKNjNEFecfKWAn+pxtpeLJBhG9l+88lmeG7CMB8qPxTGRTrVjzQ85Owgg52I7oWwIsKO4EQ+IDfVN8uiT4zzYsZr2pk1M3jkQ96DEVfRMUkcpBxF1dQjXUp5dSgkvEpvgdqsSf8VQqRqCpAk5oRErMUAodUiszu7/9IaCsPk6GoKuoVr8hTzUp0/1jzDla2O+lqWoIBFRhQieX/ICPAeJzHDLCAgYcgixlAYnRGztqi594lxPLD9j2moxMzH2Vxc6Pi1IE7mwl16xB6/j8m3SYvRWaMzPRvqaXta3U91IXNImQFvZ4tz9J" >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && exit
```

### Back on your machine, run:

```powershell
scp deploy.sh agco@ftp.agcolimited.uk:/home/amlfrogb/agcolimited.uk/agco/deploy.sh
ssh agco@ftp.agcolimited.uk chmod +x /home/amlfrogb/agcolimited.uk/agco/deploy.sh
```

---

## STEP 3: Test Deploy (1 minute)

### In PowerShell:

```powershell
cd D:\xampp\htdocs\AGCO

# Make a test change
echo "# Test deploy" >> README.md

# Commit and push
git add .
git commit -m "test: trigger auto-deploy"
git push origin main
```

### Watch the Deploy

Open: https://github.com/Rakibhasanjoybd/AG/actions

- Click the workflow run
- Watch logs in real-time
- Takes 2-3 minutes

---

## Verify It's Live

Once workflow completes with ✅ checkmarks:

```bash
# Option 1: SSH and check files
ssh agco@ftp.agcolimited.uk ls -la /home/amlfrogb/agcolimited.uk/agco

# Option 2: Visit the website
https://agcolimited.uk/
```

---

## After Setup

**Every `git push origin main` auto-deploys in 2-3 minutes.**

No more manual uploads. Fully automated.

---

## ✅ Done!

You're live with fully automated deployments.
