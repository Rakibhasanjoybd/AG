# 🚀 GO LIVE NOW - Complete Setup

## Your Setup Status

✅ Git repository initialized locally  
✅ `.github/workflows/deploy.yml` created  
✅ `deploy.sh` ready on server  
✅ Documentation complete  

⏳ **NEXT: 2 Quick Manual Steps** (5 minutes)

---

## Step 1: Add GitHub Secrets (3 minutes)

**Go to:** https://github.com/Rakibhasanjoybd/AG/settings/secrets/actions

Click **"New repository secret"** and add these 5 one-by-one:

### Secret 1
```
Name: DEPLOY_HOST
Value: ftp.agcolimited.uk
```

### Secret 2
```
Name: DEPLOY_USER
Value: agco
```

### Secret 3
```
Name: DEPLOY_PORT
Value: 22
```

### Secret 4
```
Name: DEPLOY_PATH
Value: /var/www/html/agco
```

### Secret 5
```
Name: DEPLOY_SSH_KEY
Value: (paste full contents of id_rsa file)
```

---

## Step 2: SSH Setup on Server (2 minutes)

SSH to your server and add your public key:

```bash
ssh agco@ftp.agcolimited.uk

# Then on server:
mkdir -p ~/.ssh
chmod 700 ~/.ssh

echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDZ7Pyjro5ZIzkDs/Vr+G1ZBHJO1EBHUHXKNjNEFecfKWAn+pxtpeLJBhG9l+88lmeG7CMB8qPxTGRTrVjzQ85Owgg52I7oWwIsKO4EQ+IDfVN8uiT4zzYsZr2pk1M3jkQ96DEVfRMUkcpBxF1dQjXUp5dSgkvEpvgdqsSf8VQqRqCpAk5oRErMUAodUiszu7/9IaCsPk6GoKuoVr8hTzUp0/1jzDla2O+lqWoIBFRhQieX/ICPAeJzHDLCAgYcgixlAYnRGztqi594lxPLD9j2moxMzH2Vxc6Pi1IE7mwl16xB6/j8m3SYvRWaMzPRvqaXta3U91IXNImQFvZ4tz9J" >> ~/.ssh/authorized_keys

chmod 600 ~/.ssh/authorized_keys

# Verify unzip is installed
which unzip
# If not: sudo apt install unzip

# Copy deploy.sh to server
exit
```

Then from your local machine:

```powershell
scp deploy.sh agco@ftp.agcolimited.uk:/var/www/html/agco/deploy.sh
ssh agco@ftp.agcolimited.uk chmod +x /var/www/html/agco/deploy.sh
```

---

## Step 3: Go Live - Test Push

All set! Now test:

```powershell
cd D:\xampp\htdocs\AGCO

# Make a small change (e.g., add a comment)
"# Test deployment" >> README.md

# Commit and push
git add .
git commit -m "test: trigger auto-deploy"
git push origin main
```

**Watch it deploy:**
- Go to: https://github.com/Rakibhasanjoybd/AG/actions
- Click the workflow run
- Watch logs in real-time (takes ~2-3 minutes)
- Verify files on server: `ssh agco@ftp.agcolimited.uk ls /var/www/html/agco`

---

## ✅ LIVE!

Once the first push succeeds:
- ✅ Every future `git push origin main` auto-deploys
- ✅ No manual steps needed
- ✅ ~2-3 minutes per deployment
- ✅ Logs visible in GitHub Actions

---

## 🆘 Stuck?

| Problem | Solution |
|---------|----------|
| Git push asks for password | Use `gh auth login` or paste GitHub personal access token |
| "Permission denied (publickey)" | Verify public key in `~/.ssh/authorized_keys` on server; check permissions (600) |
| Workflow fails | Check GitHub Actions logs; usually SSH key or path issue |

---

## Done!

You're live with fully automated deployments. Every push = instant production update.
