# PowerShell script using WinSCP .NET assembly to upload a file via explicit FTPS
# Requirements: Install WinSCP (https://winscp.net/) and ensure WinSCPnet.dll path below is correct.
# Edit localPath and remotePath variables before running.

Add-Type -Path "C:\Program Files (x86)\WinSCP\WinSCPnet.dll"

$sessionOptions = New-Object WinSCP.SessionOptions -Property @{ 
    Protocol = [WinSCP.Protocol]::Ftp
    FtpSecure = [WinSCP.FtpSecure]::Explicit
    HostName = "ftp.agcolimited.uk"
    PortNumber = 21
    UserName = "agco@agcolimited.uk"
    Password = "R@kib16546682"
    # Optional: set TlsHostCertificateFingerprint for stricter security
    TlsHostCertificateFingerprint = ""
}

$localPath = "C:\path\to\local\file.txt"
$remotePath = "/remote/path/"

$session = New-Object WinSCP.Session
try {
    $session.Open($sessionOptions)
    $transferOptions = New-Object WinSCP.TransferOptions
    $transferOptions.TransferMode = [WinSCP.TransferMode]::Binary

    $transferResult = $session.PutFiles($localPath, $remotePath, $false, $transferOptions)
    $transferResult.Check()
    Write-Host "Upload successful: $localPath -> $remotePath"
} catch {
    Write-Host "Upload failed:`n$($_.Exception.Message)"
} finally {
    $session.Dispose()
}
