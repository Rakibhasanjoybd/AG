<#
Atomic deploy via SSH: zips local project (excluding sensitive/large folders), uploads via scp, extracts on server.
Edit variables below and run in PowerShell. Requires OpenSSH (ssh/scp) and unzip on server.

WARNING: This script will upload and extract into the target directory. Please backup the remote site first.
#>

param()

$LocalPath = 'D:\xampp\htdocs\AGCO'    # local project folder
$KeyPath = 'C:\Users\LS WHOLESALE COMPANY\Downloads\id_rsa'  # private key
$User = 'amlfrogb'
$Host = 'ftp.agcolimited.uk'
$Port = 22
$RemotePath = '/home/amlfrogb/agcolimited.uk/agco'  # target remote folder

# Excludes (names only, top-level) — adjust as necessary
$Excludes = @('.env', '.git', 'node_modules', 'vendor')

if (-not (Test-Path $LocalPath)) {
    Write-Error "Local path not found: $LocalPath"
    exit 1
}

$Temp = Join-Path $env:TEMP "agco_deploy_$(Get-Date -Format yyyyMMddHHmmss)"
New-Item -ItemType Directory -Path $Temp | Out-Null

Write-Host "Copying files (excluding: $($Excludes -join ', ')) to temp dir: $Temp"

Get-ChildItem -Path $LocalPath -Force | ForEach-Object {
    $name = $_.Name
    if ($Excludes -contains $name) {
        Write-Host "Skipping $name"
    } else {
        $src = $_.FullName
        $dst = Join-Path $Temp $name
        Copy-Item -Path $src -Destination $dst -Recurse -Force
    }
}

$ZipPath = Join-Path $env:TEMP "agco_deploy.zip"
if (Test-Path $ZipPath) { Remove-Item $ZipPath -Force }

Write-Host "Creating archive $ZipPath"
Compress-Archive -Path (Join-Path $Temp '*') -DestinationPath $ZipPath -Force

Write-Host "Uploading archive to $User@$Host:$RemotePath"
$scpCmd = "scp -i `"$KeyPath`" -P $Port `"$ZipPath`" $User@$Host:`"$RemotePath/$(Split-Path $ZipPath -Leaf)`""
Write-Host $scpCmd
Invoke-Expression $scpCmd

Write-Host "Extracting on remote server and cleaning up"
$remoteZip = "$RemotePath/$(Split-Path $ZipPath -Leaf)"
$sshCmds = @(
    "mkdir -p `"$RemotePath`"",
    "unzip -o `"$remoteZip`" -d `"$RemotePath`"",
    "rm -f `"$remoteZip`""
)
$sshCmd = "ssh -i `"$KeyPath`" -p $Port $User@$Host `"$( $sshCmds -join ' && ' )`""
Write-Host $sshCmd
Invoke-Expression $sshCmd

Write-Host "Deploy finished. Please verify site and file permissions."

# Cleanup
Remove-Item -Recurse -Force $Temp
Remove-Item -Force $ZipPath
