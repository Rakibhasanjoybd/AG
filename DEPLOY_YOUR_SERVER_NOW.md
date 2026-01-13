# 🚀 Complete Auto-Deploy Setup - Your Server

## Status

✅ GitHub Actions workflow created (.github/workflows/deploy.yml)
✅ Server deploy script created (deploy.sh)
⏳ **Next: Add GitHub Secrets** (2 minutes)

---

## Your Server Details

From your credentials:

- **Host:** `ftp.agcolimited.uk`
- **User:** `agco` (from agco@agcolimited.uk)
- **SSH Key:** Provided (id_rsa - encrypted)
- **Public Key:** Provided (id_rsa.pub)

---

## 🔑 Step 1: Add GitHub Secrets

### Go to GitHub Secrets Page

1. Open your repo: **https://github.com/Rakibhasanjoybd/AG**
2. Click **Settings** → **Secrets and Variables** → **Actions**
3. Click **"New repository secret"** for each:

### Add These 5 Secrets

**Secret #1:**
- **Name:** `DEPLOY_HOST`
- **Value:** `ftp.agcolimited.uk`

**Secret #2:**
- **Name:** `DEPLOY_USER`
- **Value:** `agco`

**Secret #3:**
- **Name:** `DEPLOY_PORT`
- **Value:** `22`

**Secret #4:**
- **Name:** `DEPLOY_PATH`
- **Value:** `/var/www/html/agco` *(confirm with your host)*

**Secret #5:**
- **Name:** `DEPLOY_SSH_KEY`
- **Value:** *(paste full contents of your id_rsa file starting with "-----BEGIN OPENSSH PRIVATE KEY-----")*

---

## 📤 Step 2: Add Public Key to Server

SSH into your server and add the public key:

```bash
ssh agco@ftp.agcolimited.uk

# On server, add your public key:
mkdir -p ~/.ssh
echo "PASTE_CONTENTS_OF_id_rsa.pub" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

The `id_rsa.pub` contents from your attachment:

```
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDZ7Pyjro5ZIzkDs/Vr+G1ZBHJO1EBHUHXKNjNEFecfKWAn+pxtpeLJBhG9l+88lmeG7CMB8qPxTGRTrVjzQ85Owgg52I7oWwIsKO4EQ+IDfVN8uiT4zzYsZr2pk1M3jkQ96DEVfRMUkcpBxF1dQjXUp5dSgkvEpvgdqsSf8VQqRqCpAk5oRErMUAodUiszu7/9IaCsPk6GoKuoVr8hTzUp0/1jzDla2O+lqWoIBFRhQieX/ICPAeJzHDLCAgYcgixlAYnRGztqi594lxPLD9j2moxMzH2Vxc6Pi1IE7mwl16xB6/j8m3SYvRWaMzPRvqaXta3U91IXNImQFvZ4tz9J
```

---

## 🧪 Step 3: Test SSH Connection

From your local machine:

```bash
ssh -i "C:\Users\LS WHOLESALE COMPANY\Downloads\id_rsa" agco@ftp.agcolimited.uk
```

If it asks for a passphrase, enter it. If it connects without asking, great!

---

## 🔗 Step 4: Push to GitHub

Once secrets are added, push your code:

```bash
cd D:\xampp\htdocs\AGCO
git push -u origin main
```

If you get SSH key errors, you can also use GitHub CLI:

```bash
gh auth login
git push -u origin main
```

---

## 📊 Step 5: Watch the Workflow

1. Go to: **https://github.com/Rakibhasanjoybd/AG/actions**
2. Click the "Auto Deploy to Server" workflow
3. Watch the logs in real-time
4. Check your server for files after it completes

---

## ✅ What Happens Next

Once deployed:
- Files are uploaded to server: `/var/www/html/agco`
- `deploy.sh` runs automatically
- Composer installs dependencies
- Permissions are fixed
- Cache is cleared
- Site is live

---

## 🆘 Troubleshooting

| Issue | Solution |
|-------|----------|
| "Permission denied (publickey)" | Add id_rsa.pub to `~/.ssh/authorized_keys` on server; fix permissions (600/700) |
| "unzip: command not found" | Install: `sudo apt install unzip` on server |
| "deploy.sh not found" | Copy deploy.sh to /var/www/html/agco/deploy.sh on server |
| Workflow stuck | Check GitHub Actions logs; may need to confirm SSH hostkey |

---

## 📞 Done?

Once the first `git push` deploys successfully, you're all set. Every future push to `main` will automatically deploy.

**See** [AUTO_DEPLOY_START_HERE.md](AUTO_DEPLOY_START_HERE.md) for more details.
