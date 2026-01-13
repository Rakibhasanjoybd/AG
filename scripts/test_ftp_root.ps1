$ftpHost = "server204.web-hosting.com"
$ftpUser = "agco@agcolimited.uk"
$ftpPassword = "R@kib16546682"

Write-Host "Testing FTP to root..."

try {
    $uri = "ftp://$ftpHost/"
    $ftpRequest = [System.Net.FtpWebRequest]::Create($uri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $ftpRequest.UsePassive = $true
    
    $response = $ftpRequest.GetResponse()
    Write-Host "Connected to root! Reading directory..."
    
    $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
    $dirs = $reader.ReadToEnd()
    Write-Host $dirs
    
    $reader.Close()
    $response.Close()
}
catch {
    Write-Host ("Error: " + $_.Exception.Message)
}
