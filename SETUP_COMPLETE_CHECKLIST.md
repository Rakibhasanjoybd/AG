# ✅ Complete Auto-Deploy Checklist

## 🎯 What You Got

- [x] `.github/workflows/deploy.yml` — GitHub Actions workflow
- [x] `deploy.sh` — Server-side deploy script
- [x] Documentation and setup guides
- [x] Helper scripts

## 📋 What You Need To Do (In Order)

### Phase 1: GitHub Secrets Setup (2 minutes)

- [ ] Go to: https://github.com/Rakibhasanjoybd/AG/settings/secrets/actions
- [ ] Add Secret #1: `DEPLOY_HOST` = `ftp.agcolimited.uk`
- [ ] Add Secret #2: `DEPLOY_USER` = `agco`
- [ ] Add Secret #3: `DEPLOY_PORT` = `22`
- [ ] Add Secret #4: `DEPLOY_PATH` = `/var/www/html/agco` (confirm with host)
- [ ] Add Secret #5: `DEPLOY_SSH_KEY` = (paste full id_rsa contents)

### Phase 2: Server Setup (2 minutes)

- [ ] SSH to server: `ssh agco@ftp.agcolimited.uk`
- [ ] Add public key: `echo "SSH_KEY_CONTENTS" >> ~/.ssh/authorized_keys`
- [ ] Fix permissions:
  ```bash
  chmod 600 ~/.ssh/authorized_keys
  chmod 700 ~/.ssh
  ```
- [ ] Verify unzip is installed: `which unzip`
- [ ] Copy deploy.sh to server and make executable

### Phase 3: Test Push (1 minute)

- [ ] Make a small change locally
- [ ] Commit: `git commit -m "test deploy"`
- [ ] Push: `git push origin main`
- [ ] Watch: https://github.com/Rakibhasanjoybd/AG/actions
- [ ] Verify files appear on server

### Phase 4: Verify Deployment (1 minute)

- [ ] SSH to server: `ssh agco@ftp.agcolimited.uk`
- [ ] Check files: `ls -la /var/www/html/agco`
- [ ] Check deploy.sh ran: `tail deploy.sh.log` (if created)

---

## 🎯 After Setup (Ongoing)

Once all checks pass:
- ✅ Every `git push origin main` auto-deploys
- ✅ No manual steps needed
- ✅ Logs visible in GitHub Actions
- ✅ Deployments take ~2-3 minutes each

---

## 📖 Documentation

| File | Purpose |
|------|---------|
| `DEPLOY_YOUR_SERVER_NOW.md` | **START HERE** — step-by-step setup |
| `AUTO_DEPLOY_START_HERE.md` | Full guide with explanations |
| `AUTO_DEPLOY_NEXT_STEPS.md` | Quick reference |
| `AUTO_DEPLOY_CHECKLIST.md` | Quick checklist |
| `GITHUB_ACTIONS_SETUP.md` | Complete reference + troubleshooting |

---

## 🆘 Need Help?

1. **SSH key won't connect?** → Check public key is in `~/.ssh/authorized_keys`
2. **GitHub Actions fails?** → Check the workflow logs in Actions tab
3. **Files not updating?** → Verify DEPLOY_PATH is correct
4. **Permission errors?** → Run deploy.sh manually to debug

---

## 🚀 Ready?

Start with: [DEPLOY_YOUR_SERVER_NOW.md](DEPLOY_YOUR_SERVER_NOW.md)

It has copy-paste commands for every step.
