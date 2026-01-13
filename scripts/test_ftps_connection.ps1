# Test FTPS connection
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor [System.Net.SecurityProtocolType]::Tls12

$FtpHost = "ftp.agcolimited.uk"
$FtpUser = "agco@agcolimited.uk"
$FtpPassword = "R@kib16546682"
$FtpPort = 21

Write-Host "Testing FTPS connection to $FtpHost..."

try {
    $uri = "ftp://$FtpHost/agcolimited.uk/"
    $ftpRequest = [System.Net.FtpWebRequest]::Create($uri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
    $ftpRequest.UsePassive = $true
    $ftpRequest.UseBinary = $true
    
    Write-Host "Connecting..."
    $response = $ftpRequest.GetResponse()
    
    Write-Host "SUCCESS - Connected to FTPS server"
    Write-Host "Server response: $($response.StatusDescription)"
    $response.Close()
} catch {
    Write-Host "FAILED - $($_.Exception.Message)"
    exit 1
}
