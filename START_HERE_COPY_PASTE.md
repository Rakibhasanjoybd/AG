# ⚡ YOUR EXACT NEXT STEPS - COPY/PASTE READY

## RIGHT NOW - 5 MINUTES TO LIVE

### 1️⃣ Go Here (Open in Browser)

```
https://github.com/Rakibhasanjoybd/AG/settings/secrets/actions
```

### 2️⃣ Add Secret #1

Click: **New repository secret**

- **Name:** `DEPLOY_HOST`
- **Value:** `ftp.agcolimited.uk`
- Click: **Add secret**

### 3️⃣ Add Secret #2

Click: **New repository secret**

- **Name:** `DEPLOY_USER`
- **Value:** `agco`
- Click: **Add secret**

### 4️⃣ Add Secret #3

Click: **New repository secret**

- **Name:** `DEPLOY_PORT`
- **Value:** `22`
- Click: **Add secret**

### 5️⃣ Add Secret #4

Click: **New repository secret**

- **Name:** `DEPLOY_PATH`
- **Value:** `/home/amlfrogb/agcolimited.uk/agco`
- Click: **Add secret**

### 6️⃣ Add Secret #5

Click: **New repository secret**

- **Name:** `DEPLOY_SSH_KEY`
- **Value:** Copy-paste the ENTIRE contents of your `id_rsa` file (including BEGIN and END lines)
- Click: **Add secret**

---

## THEN - SSH to Server (2 minutes)

### Open PowerShell and run:

```powershell
ssh agco@ftp.agcolimited.uk
```

(Enter your SSH key passphrase if prompted)

### Once on server, run this ONE LINE:

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh && echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDZ7Pyjro5ZIzkDs/Vr+G1ZBHJO1EBHUHXKNjNEFecfKWAn+pxtpeLJBhG9l+88lmeG7CMB8qPxTGRTrVjzQ85Owgg52I7oWwIsKO4EQ+IDfVN8uiT4zzYsZr2pk1M3jkQ96DEVfRMUkcpBxF1dQjXUp5dSgkvEpvgdqsSf8VQqRqCpAk5oRErMUAodUiszu7/9IaCsPk6GoKuoVr8hTzUp0/1jzDla2O+lqWoIBFRhQieX/ICPAeJzHDLCAgYcgixlAYnRGztqi594lxPLD9j2moxMzH2Vxc6Pi1IE7mwl16xB6/j8m3SYvRWaMzPRvqaXta3U91IXNImQFvZ4tz9J" >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && echo "Done!" && exit
```

### Back on your machine, run:

```powershell
scp deploy.sh agco@ftp.agcolimited.uk:/home/amlfrogb/agcolimited.uk/agco/deploy.sh
ssh agco@ftp.agcolimited.uk chmod +x /home/amlfrogb/agcolimited.uk/agco/deploy.sh
```

---

## FINALLY - Test Deploy (1 minute)

### In PowerShell:

```powershell
cd D:\xampp\htdocs\AGCO

echo "# Deploy test" >> README.md
git add .
git commit -m "test: trigger auto-deploy"
git push origin main
```

### WATCH IT DEPLOY

Open: https://github.com/Rakibhasanjoybd/AG/actions

Click the workflow, watch logs. Takes 2-3 minutes.

---

## ✅ DONE!

Every future `git push origin main` auto-deploys.

No more manual work. Ever.
