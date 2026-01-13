# Auto-Deploy Quick Checklist

## ⚡ 5-Minute Setup

- [ ] **1. Generate SSH key** (local machine)
  ```powershell
  ssh-keygen -t ed25519 -f "$env:USERPROFILE\.ssh\deploy_key" -N ""
  ```

- [ ] **2. Copy public key to server**
  ```bash
  # On server:
  cat ~/.ssh/deploy_key.pub >> ~/.ssh/authorized_keys
  chmod 600 ~/.ssh/authorized_keys
  ```

- [ ] **3. Add GitHub secrets** (repo settings)
  - DEPLOY_HOST
  - DEPLOY_USER
  - DEPLOY_SSH_KEY (full private key)
  - DEPLOY_PORT (22)
  - DEPLOY_PATH (/var/www/html/agco)

- [ ] **4. Verify server requirements**
  ```bash
  which unzip
  which php
  which composer
  ```

- [ ] **5. Test with a push**
  ```bash
  git push origin main
  ```
  → Watch Actions tab in GitHub for logs

---

## 📁 What's New

| File | Purpose |
|------|---------|
| `.github/workflows/deploy.yml` | Workflow that runs on every push to `main` |
| `deploy.sh` | Server-side script (runs on remote, handles migrations, perms, cache) |
| `GITHUB_ACTIONS_SETUP.md` | Full setup guide with troubleshooting |
| `scripts/setup-secrets.ps1` | PowerShell helper to add GitHub secrets via CLI |

---

## 🔄 How It Works (After Setup)

```
You: git push → GitHub: zip + SCP + SSH → Server: unzip + ./deploy.sh → Live! ✅
```

Every push to `main` auto-deploys. No manual uploads, no WinSCP, fully automated.

---

## 📞 Support

See `GITHUB_ACTIONS_SETUP.md` for full guide, troubleshooting, and customization.
