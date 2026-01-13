<#
Quick SSH test using your private key.
Edit the variables below as needed then run in PowerShell.
Requires Windows OpenSSH client (Windows 10+ usually has it) or Git Bash.
#>

$KeyPath = 'C:\Users\LS WHOLESALE COMPANY\Downloads\id_rsa'
$User = 'amlfrogb'
$Host = 'ftp.agcolimited.uk'
$Port = 22

Write-Host "Testing SSH connection to $User@$Host (port $Port) using key $KeyPath"

$cmd = "ssh -i `"$KeyPath`" -p $Port $User@$Host echo 'SSH OK' && whoami && pwd && ls -la"
Write-Host "Running: $cmd"
Invoke-Expression $cmd
