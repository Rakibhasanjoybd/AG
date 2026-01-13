Quick FTP/FTPS usage for ftp.agcolimited.uk

Credentials (as provided):
- Host: ftp.agcolimited.uk
- Port: 21 (explicit FTPS)
- Username: agco@agcolimited.uk
- Password: R@kib16546682
- Use Passive mode (PASV)

Files added in this folder:
- `winscp_upload.txt` — a WinSCP command script. Replace local/remote paths then run:
  winscp.com /script="D:\\xampp\\htdocs\\AGCO\\scripts\\winscp_upload.txt"

  Note: script contains URL-encoded credentials (username/password) so it safely parses.

- `winscp_deploy.ps1` — PowerShell script using WinSCP .NET assembly. Steps:
  1. Install WinSCP: https://winscp.net/
  2. Ensure `WinSCPnet.dll` is installed at `C:\\Program Files (x86)\\WinSCP\\WinSCPnet.dll` (adjust path in script if needed).
  3. Edit `$localPath` and `$remotePath` variables in the script.
  4. Run in PowerShell (as needed):

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
powershell -File "D:\\xampp\\htdocs\\AGCO\\scripts\\winscp_deploy.ps1"
```

Security notes:
- These scripts include your credentials for convenience. Delete or restrict access after use.
- For production automation, use key-based authentication or store credentials in a secure vault rather than plaintext.

If you want, I can:
- Create a scheduled deployment script that uploads the whole `public` folder.
- Help you configure VS Code remote editing (show sample `ftp-simple`/`ftp-kr` config).
