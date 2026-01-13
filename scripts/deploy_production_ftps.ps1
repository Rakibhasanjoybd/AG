# Deploy Laravel AGCO app via FTPS to agcolimited.uk/agco
# Uploads the core folder and creates production .env on remote server

param()

$LocalAppPath = "D:\xampp\htdocs\AGCO\core"
$LocalEnvFile = "D:\xampp\htdocs\AGCO\core\.env.production"
$RemoteBasePath = "agcolimited.uk/agco"
$FtpHost = "server204.web-hosting.com"
$FtpUser = "agco@agcolimited.uk"
$FtpPassword = "R@kib16546682"
$FtpPort = 21

# Exclude these files/folders from upload
$Excludes = @('.env', '.env.example', '.git', 'node_modules', 'vendor', '.gitignore', 'storage/logs', 'storage/app/temp')

Write-Host "========================================"
Write-Host "AGCO Production Deploy via FTPS"
Write-Host "========================================"
Write-Host "Local app: $LocalAppPath"
Write-Host "Remote path: /$RemoteBasePath"
Write-Host "Database: amlfrogb_agcoweb @ localhost:3306"
Write-Host ""

if (-not (Test-Path $LocalAppPath)) {
    Write-Error "App folder not found: $LocalAppPath"
    exit 1
}

if (-not (Test-Path $LocalEnvFile)) {
    Write-Error "Production .env not found: $LocalEnvFile"
    exit 1
}

# Build FTP URI
function Build-FtpUri($path) {
    return "ftp://${FtpUser}:${FtpPassword}@${FtpHost}:${FtpPort}/$RemoteBasePath/$path"
}

# Helper: Upload single file via FTP
function Upload-FileViaFtp($localFile, $remoteRelativePath) {
    try {
        $uri = Build-FtpUri $remoteRelativePath
        
        $ftpRequest = [System.Net.FtpWebRequest]::Create($uri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.KeepAlive = $false

        $fileContent = [System.IO.File]::ReadAllBytes($localFile)
        $ftpRequest.ContentLength = $fileContent.Length

        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()

        $response = $ftpRequest.GetResponse()
        $response.Close()
        
        return $true
    } catch {
        Write-Error "Failed to upload $localFile : $_"
        return $false
    }
}

# Helper: Create remote directory
function Create-RemoteDirViaFtp($remoteDirPath) {
    try {
        $uri = Build-FtpUri $remoteDirPath
        
        $ftpRequest = [System.Net.FtpWebRequest]::Create($uri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UsePassive = $true
        $ftpRequest.KeepAlive = $false

        $response = $ftpRequest.GetResponse()
        $response.Close()
        
        return $true
    } catch {
        # Directory may already exist, suppress error
        return $false
    }
}

# Get all files and directories
Write-Host "Scanning files..."
$allFiles = Get-ChildItem -Path $LocalAppPath -Recurse -Force -File
$allDirs = Get-ChildItem -Path $LocalAppPath -Recurse -Force -Directory

# Filter out excluded items
$filesToUpload = @()
$dirsToCreate = @()

foreach ($file in $allFiles) {
    $relPath = $file.FullName.Replace($LocalAppPath, '').TrimStart('\', '/')
    $pathParts = $relPath -split '[\\\/]'
    
    # Skip if first part is in Excludes
    if ($Excludes -contains $pathParts[0]) {
        continue
    }
    
    $filesToUpload += @{ FullName = $file.FullName; RelativePath = $relPath }
}

foreach ($dir in $allDirs) {
    $relPath = $dir.FullName.Replace($LocalAppPath, '').TrimStart('\', '/')
    $pathParts = $relPath -split '[\\\/]'
    
    if ($Excludes -contains $pathParts[0]) {
        continue
    }
    
    $dirsToCreate += $relPath
}

Write-Host "Found: $($filesToUpload.Count) files, $($dirsToCreate.Count) directories"
Write-Host ""

# Create directories
Write-Host "Creating remote directories..."
foreach ($dir in $dirsToCreate) {
    $remotePath = ($dir.Replace('\', '/'))
    if (-not (Create-RemoteDirViaFtp $remotePath)) {
        # Ignore errors, directory may exist
    }
}

Write-Host ""
Write-Host "Uploading files (this may take a minute)..."
$uploadCount = 0

foreach ($file in $filesToUpload) {
    $remotePath = $file.RelativePath.Replace('\', '/')
    if (Upload-FileViaFtp $file.FullName $remotePath) {
        $uploadCount++
        if ($uploadCount % 10 -eq 0) {
            Write-Host "  [$uploadCount] files uploaded..."
        }
    }
}

Write-Host ""
Write-Host "Uploading production .env as remote .env..."
if (Upload-FileViaFtp $LocalEnvFile ".env") {
    Write-Host "  .env uploaded successfully"
}

Write-Host ""
Write-Host "========================================"
Write-Host "Deploy Complete!"
Write-Host "========================================"
Write-Host "Uploaded $uploadCount files to /$RemoteBasePath"
Write-Host ""
Write-Host "NEXT STEPS:"
Write-Host ""
Write-Host "1. In cPanel, set document root for agcolimited.uk domain to:"
Write-Host "   /home/amlfrogb/agcolimited.uk/agco/public"
Write-Host ""
Write-Host "2. Via SSH or cPanel terminal, run migrations (if needed):"
Write-Host "   cd /home/amlfrogb/agcolimited.uk/agco"
Write-Host "   composer install --no-dev --optimize-autoloader"
Write-Host "   php artisan migrate --force"
Write-Host "   php artisan config:cache"
Write-Host "   php artisan route:cache"
Write-Host "   php artisan view:cache"
Write-Host ""
Write-Host "3. Set permissions:"
Write-Host "   chmod 755 -R ."
Write-Host "   chmod 775 storage bootstrap/cache"
Write-Host "   chown -R nobody:nobody ."
Write-Host ""
Write-Host "4. Visit https://agcolimited.uk and verify the site loads"
Write-Host ""
Write-Host "If you see errors, check:"
Write-Host "  - cPanel error logs (usually /home/user/public_html/error_log)"
Write-Host "  - Laravel logs in storage/logs/"
Write-Host "  - Database connectivity (run: php artisan tinker -> DB::connection()->getPdo())"
