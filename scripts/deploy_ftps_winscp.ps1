# Deploy via FTPS using WinSCP .NET
# Uploads D:\xampp\htdocs\AGCO to /agcolimited.uk/agco on the server

$LocalProjectPath = "D:\xampp\htdocs\AGCO"
$RemotePath = "/agcolimited.uk/agco"
$FtpHost = "ftp.agcolimited.uk"
$FtpUser = "agco@agcolimited.uk"
$FtpPassword = "R@kib16546682"
$FtpPort = 21

# Folders to exclude
$Excludes = @('.env', '.git', 'node_modules', 'vendor', '.gitignore', '.env.example')

Write-Host "=== AGCO FTPS Deploy Script ==="
Write-Host "Local: $LocalProjectPath"
Write-Host "Remote: //$FtpHost$RemotePath"
Write-Host ""

# Check WinSCP .NET is installed
$WinScpPath = "C:\Program Files (x86)\WinSCP\WinSCPnet.dll"
if (-not (Test-Path $WinScpPath)) {
    Write-Error "WinSCP .NET not found at $WinScpPath. Please install WinSCP from https://winscp.net/"
    exit 1
}

Add-Type -Path $WinScpPath

# Create session options for FTPS
$sessionOptions = New-Object WinSCP.SessionOptions -Property @{
    Protocol = [WinSCP.Protocol]::Ftp
    FtpSecure = [WinSCP.FtpSecure]::Explicit
    HostName = $FtpHost
    PortNumber = $FtpPort
    UserName = $FtpUser
    Password = $FtpPassword
}

$session = New-Object WinSCP.Session

try {
    Write-Host "Connecting to FTPS server..."
    $session.Open($sessionOptions)
    Write-Host "Connected!"

    # Create remote directory if it doesn't exist
    Write-Host "Ensuring remote path exists: $RemotePath"
    $session.ExecuteCommand("mkdir -p `"$RemotePath`"") | Out-Null

    # Upload files recursively, excluding unwanted folders
    $transferOptions = New-Object WinSCP.TransferOptions
    $transferOptions.TransferMode = [WinSCP.TransferMode]::Binary
    $transferOptions.FilePermissions = $null

    Write-Host "Uploading files..."
    
    Get-ChildItem -Path $LocalProjectPath -Force | ForEach-Object {
        $name = $_.Name
        if ($Excludes -contains $name) {
            Write-Host "  (skip) $name"
        } else {
            $localItem = $_.FullName
            $remoteItem = "$RemotePath/$name"
            
            if ($_.PSIsContainer) {
                Write-Host "  (dir)  $name/"
                $transferResult = $session.PutFiles("$localItem\*", "$remoteItem/", $true, $transferOptions)
                $transferResult.Check()
            } else {
                Write-Host "  (file) $name"
                $transferResult = $session.PutFiles($localItem, "$remoteItem", $false, $transferOptions)
                $transferResult.Check()
            }
        }
    }

    Write-Host ""
    Write-Host "=== Upload Complete ==="
    Write-Host "Files deployed to: $RemotePath"
    Write-Host ""
    Write-Host "Next steps:"
    Write-Host "1. In cPanel, set domain document root to: /home/amlfrogb/agcolimited.uk/agco"
    Write-Host "2. Or upload public_html_index_redirect.php contents to public_html/index.php to redirect"
    Write-Host "3. Visit https://agcolimited.uk to verify"

} catch {
    Write-Host "Error: $($_.Exception.Message)"
    exit 1
} finally {
    $session.Dispose()
}
