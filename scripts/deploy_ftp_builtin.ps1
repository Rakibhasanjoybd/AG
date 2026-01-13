# Deploy via FTP using built-in PowerShell (no external tools required)
# Uploads D:\xampp\htdocs\AGCO to /agcolimited.uk/agco on the server

param()

$LocalProjectPath = "D:\xampp\htdocs\AGCO"
$RemotePath = "agcolimited.uk/agco"
$FtpHost = "ftp://ftp.agcolimited.uk"
$FtpUser = "agco@agcolimited.uk"
$FtpPassword = "R@kib16546682"

# Folders/files to exclude
$Excludes = @('.env', '.git', 'node_modules', 'vendor', '.gitignore', '.env.example', 'scripts')

Write-Host "=== AGCO FTP Deploy (Built-in PowerShell) ==="
Write-Host "Local: $LocalProjectPath"
Write-Host "Remote: $FtpHost/$RemotePath"
Write-Host ""

function Upload-FileViaFtp($localFile, $remoteName) {
    try {
        $uri = "$FtpHost/$RemotePath/$remoteName"
        Write-Host "Uploading: $remoteName"
        
        $ftpRequest = [System.Net.FtpWebRequest]::Create($uri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true

        $fileContent = [System.IO.File]::ReadAllBytes($localFile)
        $ftpRequest.ContentLength = $fileContent.Length

        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()

        $response = $ftpRequest.GetResponse()
        Write-Host "  OK - $($response.StatusDescription)"
        $response.Close()
    } catch {
        Write-Error "Failed to upload $localFile : $_"
    }
}

function Create-RemoteDirectoryViaFtp($dirName) {
    try {
        $uri = "$FtpHost/$RemotePath/$dirName"
        Write-Host "Creating directory: $dirName"
        
        $ftpRequest = [System.Net.FtpWebRequest]::Create($uri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UsePassive = $true

        $response = $ftpRequest.GetResponse()
        Write-Host "  OK"
        $response.Close()
    } catch {
        # Directory may already exist, ignore
        Write-Host "  (skip)"
    }
}

# Get all files to upload
$files = Get-ChildItem -Path $LocalProjectPath -Recurse -Force | Where-Object { -not $_.PSIsContainer }
$dirs = Get-ChildItem -Path $LocalProjectPath -Recurse -Force -Directory | Where-Object { $Excludes -notcontains $_.Name }

Write-Host "Found $($files.Count) files and $($dirs.Count) directories"
Write-Host ""

# Filter out excluded paths
$filesToUpload = $files | Where-Object {
    $relativePath = $_.FullName.Replace($LocalProjectPath, '').TrimStart('\')
    $parts = $relativePath.Split('\')
    -not ($Excludes -contains $parts[0])
}

Write-Host "Uploading $($filesToUpload.Count) files..."
Write-Host ""

foreach ($file in $filesToUpload) {
    $relativePath = $file.FullName.Replace($LocalProjectPath, '').TrimStart('\').Replace('\', '/')
    
    # Ensure directory exists
    $dirPath = Split-Path $relativePath -Parent
    if ($dirPath -and $dirPath -ne '.') {
        Create-RemoteDirectoryViaFtp $dirPath
    }
    
    Upload-FileViaFtp $file.FullName $relativePath
}

Write-Host ""
Write-Host "=== Upload Complete ==="
Write-Host "Files deployed to: /$RemotePath"
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. In cPanel, set domain document root to: /home/amlfrogb/agcolimited.uk/agco"
Write-Host "2. Or manually create /public_html/index.php with redirect to /agco/"
Write-Host "3. Visit https://agcolimited.uk to verify the site loads"
Write-Host ""
Write-Host "If you see a blank page or errors, check:"
Write-Host "  - File permissions (should be 644 for files, 755 for directories)"
Write-Host "  - Remote .env file with correct DB_HOST, DB_DATABASE, etc."
Write-Host "  - PHP error logs in cPanel"
