# Test FTP connection with server204
$FtpHost = "server204.web-hosting.com"
$FtpUser = "agco@agcolimited.uk"
$FtpPassword = "R@kib16546682"
$RemotePath = "agcolimited.uk/agco"

Write-Host "Testing FTP connection..."

try {
    $uri = "ftp://$FtpHost/$RemotePath/"
    Write-Host "URI: $uri"
    $ftpRequest = [System.Net.FtpWebRequest]::Create($uri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
    $ftpRequest.UsePassive = $true
    $ftpRequest.UseBinary = $true
    
    Write-Host "Connecting to $uri..."
    $response = $ftpRequest.GetResponse()
    Write-Host "SUCCESS - FTP Connection OK!"
    $response.Close()
    exit 0
}
catch {
    Write-Host "FAILED - Error: $($_.Exception.Message)"
    exit 1
}
