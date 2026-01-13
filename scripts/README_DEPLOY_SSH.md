SSH/Git recommended deploy instructions — quick guide

1) Add your SSH public key to cPanel
   - Login to cPanel -> Security -> SSH Access -> Manage SSH Keys -> Import Key
   - Paste the contents of `id_rsa.pub` (you attached it). Save and authorize the key.

2) Test SSH locally (PowerShell)
   - Edit `scripts/ssh_test.ps1` if you need to change key path or host, then run:

```powershell
powershell -File "D:\\xampp\\htdocs\\AGCO\\scripts\\ssh_test.ps1"
```

3) Deploy (zip upload + extract) — one-step (PowerShell)
   - Edit variables at top of `scripts/ssh_deploy_zip_and_extract.ps1` (LocalPath, KeyPath, RemotePath)
   - Run:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
powershell -File "D:\\xampp\\htdocs\\AGCO\\scripts\\ssh_deploy_zip_and_extract.ps1"
```

This script zips your project (excluding `.env`, `.git`, `node_modules`, `vendor` by default), uploads via scp, then extracts on the server.

4) WinSCP alternative (GUI)
   - Use `scripts/winscp_ssh_put.txt` as a script or open WinSCP GUI and configure SFTP with private key.

5) Make the site root show `agco` folder
   - Best: In cPanel, Domains -> document root for the domain, point to `/home/amlfrogb/agcolimited.uk/agco` (or set addon domain document root).
   - Quick alternative: upload `public_html/index.php` with redirect contents from `public_html_index_redirect.php`.

6) Backups (manual, recommended before deploy)
   - Via cPanel File Manager: compress current webroot and download.
   - Via SSH (if allowed): create remote zip and DB dump. Example commands (run locally after key is authorized):

```powershell
ssh -i "C:\\path\\to\\id_rsa" amlfrogb@ftp.agcolimited.uk -p 22 "cd /home/amlfrogb/agcolimited.uk && zip -r backup_files_$(date +%F).zip public_html agco"
scp -i "C:\\path\\to\\id_rsa" -P 22 amlfrogb@ftp.agcolimited.uk:/home/amlfrogb/agcolimited.uk/backup_files_*.zip .
```

DB dump: if you have SSH + mysql client access:

```powershell
ssh -i "C:\\path\\to\\id_rsa" amlfrogb@ftp.agcolimited.uk -p 22 "mysqldump -u DBUSER -p'DBPASS' DBNAME > /home/amlfrogb/db_backup.sql"
scp -i "C:\\path\\to\\id_rsa" -P 22 amlfrogb@ftp.agcolimited.uk:/home/amlfrogb/db_backup.sql .
```

If you don't have DB credentials or SSH mysql, use phpMyAdmin in cPanel to export the database.

Security note: remove or rotate passwords and private keys you don't need after deployment. Keep your private key safe — do not share it.
