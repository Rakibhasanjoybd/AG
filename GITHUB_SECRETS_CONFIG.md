# GitHub Secrets Configuration for Auto-Deploy

## Your Server Credentials (from attachments)

Based on the credentials you provided, here's what to add to GitHub Secrets:

### 📋 Required GitHub Secrets

Add these to: **GitHub Repo → Settings → Secrets and Variables → Actions**

| Secret Name | Value | Notes |
|---|---|---|
| `DEPLOY_HOST` | `ftp.agcolimited.uk` | SSH/server hostname |
| `DEPLOY_USER` | `agco` | SSH username (from: agco@agcolimited.uk) |
| `DEPLOY_SSH_KEY` | *(full private key from id_rsa)* | Use the provided id_rsa (encrypted RSA key) |
| `DEPLOY_PORT` | `22` | Standard SSH port |
| `DEPLOY_PATH` | `/var/www/html` or `/home/agco/` | Server directory path (confirm with your host) |

---

## ⚠️ Important Notes

Your SSH key is encrypted (has passphrase protection). You have two options:

### Option A: Use GitHub SSH Passphrase (Recommended)
GitHub Actions supports encrypted SSH keys via `ssh-agent`. The workflow will handle this automatically.

### Option B: Remove Passphrase (Less Secure)
If you want to remove the passphrase from your key:

```bash
ssh-keygen -p -f id_rsa -N "" -P "your_current_passphrase"
```

Then use the unencrypted key in `DEPLOY_SSH_KEY`.

---

## 🔧 Setup Steps

### 1. Go to GitHub Secrets Page
- Open: **https://github.com/Rakibhasanjoybd/AG/settings/secrets/actions**

### 2. Add Each Secret

Click **"New repository secret"** for each:

**Secret 1: DEPLOY_HOST**
```
ftp.agcolimited.uk
```

**Secret 2: DEPLOY_USER**
```
agco
```

**Secret 3: DEPLOY_PORT**
```
22
```

**Secret 4: DEPLOY_PATH**
```
/var/www/html/agco
```
(confirm the actual path on your server)

**Secret 5: DEPLOY_SSH_KEY**
- Paste the full contents of your `id_rsa` private key file:
```
-----BEGIN OPENSSH PRIVATE KEY-----
(... full key content ...)
-----END OPENSSH PRIVATE KEY-----
```

---

## 🐛 Troubleshooting

If you get "Permission denied" errors:

1. **Verify public key on server:**
   ```bash
   ssh agco@ftp.agcolimited.uk
   cat ~/.ssh/authorized_keys  # Should contain your id_rsa.pub
   ```

2. **Check SSH service is running:**
   ```bash
   sudo systemctl status ssh
   ```

3. **Verify permissions:**
   ```bash
   chmod 700 ~/.ssh
   chmod 600 ~/.ssh/authorized_keys
   ```

---

## 📝 Next Steps

1. ✅ Add all 5 secrets above to GitHub
2. ⏳ Wait for `.github/workflows/deploy.yml` to be recognized
3. 🧪 Test with: `git push origin main` (once SSH auth is working)
4. 📊 Check **Actions** tab for workflow logs

---

## 💡 Testing GitHub Push

Before pushing, ensure SSH key access works locally:

```bash
ssh -i "C:\Users\LS WHOLESALE COMPANY\Downloads\id_rsa" agco@ftp.agcolimited.uk
```

Once confirmed, add it to your SSH agent or update git config:

```powershell
# Add SSH key to Windows SSH agent:
ssh-add "C:\Users\LS WHOLESALE COMPANY\Downloads\id_rsa"

# Then push:
git push -u origin main
```

Or use GitHub CLI (faster):
```powershell
gh auth login
git push -u origin main
```
