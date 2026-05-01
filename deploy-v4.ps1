$ErrorActionPreference = 'Stop'
$zipPath = 'C:\Users\Christian Hesselmann\Documents\Claude\Projects\wp-plugins\themeflex-v4.0.0.zip'
$wpBase = 'https://themeflex.de'
$adminUser = 'admin'
$adminPass = 'ThemeFlex2026!'

Write-Host "ThemeFlex v4.0.0 — WP Admin Deploy" -ForegroundColor Cyan
Write-Host "ZIP: $zipPath"
if (-not (Test-Path $zipPath)) { Write-Host "ERROR: ZIP not found!" -ForegroundColor Red; exit 1 }
$zipSize = (Get-Item $zipPath).Length / 1MB
Write-Host "ZIP size: $([math]::Round($zipSize,1)) MB" -ForegroundColor Green

# Ignore SSL errors
Add-Type @"
    using System.Net;
    using System.Security.Cryptography.X509Certificates;
    public class TrustAllCerts : ICertificatePolicy {
        public bool CheckValidationResult(ServicePoint s, X509Certificate c, WebRequest r, int p) { return true; }
    }
"@
[System.Net.ServicePointManager]::CertificatePolicy = New-Object TrustAllCerts
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12

$cookieContainer = New-Object System.Net.CookieContainer

# Step 1: Get login page (test cookie)
Write-Host "`nStep 1: Login page..." -ForegroundColor Yellow
$r1 = [System.Net.HttpWebRequest]::Create("$wpBase/wp-login.php")
$r1.CookieContainer = $cookieContainer; $r1.Method = "GET"
$r1.GetResponse().Close()
$cookieContainer.Add([System.Uri]$wpBase, (New-Object System.Net.Cookie("wordpress_test_cookie","WP Cookie check","/","themeflex.de")))

# Step 2: Login
Write-Host "Step 2: Logging in as $adminUser..." -ForegroundColor Yellow
$encodedPass = [Uri]::EscapeDataString($adminPass)
$loginBody = "log=$adminUser&pwd=$encodedPass&wp-submit=Log+In&redirect_to=%2Fwp-admin%2F&testcookie=1"
$r2 = [System.Net.HttpWebRequest]::Create("$wpBase/wp-login.php")
$r2.CookieContainer = $cookieContainer; $r2.Method = "POST"
$r2.ContentType = "application/x-www-form-urlencoded"; $r2.AllowAutoRedirect = $false
$b2 = [System.Text.Encoding]::UTF8.GetBytes($loginBody)
$r2.ContentLength = $b2.Length
$s2 = $r2.GetRequestStream(); $s2.Write($b2,0,$b2.Length); $s2.Close()
$resp2 = $r2.GetResponse()
Write-Host "Login status: $($resp2.StatusCode)" -ForegroundColor $(if($resp2.StatusCode -eq 302){"Green"}else{"Red"})
$resp2.Close()
$cookies = $cookieContainer.GetCookies([System.Uri]$wpBase) | ForEach-Object {$_.Name}
Write-Host "Cookies: $($cookies -join ', ')"
if (-not ($cookies -match "wordpress_logged_in")) { Write-Host "ERROR: Login failed!" -ForegroundColor Red; exit 1 }

# Step 3: Get nonce
Write-Host "`nStep 3: Getting nonce..." -ForegroundColor Yellow
$r3 = [System.Net.HttpWebRequest]::Create("$wpBase/wp-admin/theme-install.php")
$r3.CookieContainer = $cookieContainer; $r3.Method = "GET"
$resp3 = $r3.GetResponse()
$html3 = (New-Object System.IO.StreamReader($resp3.GetResponseStream())).ReadToEnd()
$resp3.Close()
$nm = [regex]::Match($html3, '"_ajax_nonce":"([^"]+)"')
if (-not $nm.Success) { $nm = [regex]::Match($html3, 'name="_wpnonce"\s+value="([^"]+)"') }
if (-not $nm.Success) { $nm = [regex]::Match($html3, '"themeupload":"([^"]+)"') }
$nonce = $nm.Groups[1].Value
Write-Host "Nonce: $($nonce.Substring(0,[Math]::Min(12,$nonce.Length)))..." -ForegroundColor Green

# Step 4: Upload ZIP
Write-Host "`nStep 4: Uploading themeflex-v4.0.0.zip ($([math]::Round($zipSize,1)) MB)..." -ForegroundColor Yellow
$boundary = [System.Guid]::NewGuid().ToString("N")
$zipBytes = [System.IO.File]::ReadAllBytes($zipPath)
$ms = New-Object System.IO.MemoryStream
$w = New-Object System.IO.StreamWriter($ms)
foreach ($field in @{_wpnonce=$nonce; _wp_http_referer="/wp-admin/theme-install.php"; "install-theme-submit"="Install Now"}.GetEnumerator()) {
    $w.Write("--$boundary`r`nContent-Disposition: form-data; name=`"$($field.Key)`"`r`n`r`n$($field.Value)`r`n")
}
$w.Write("--$boundary`r`nContent-Disposition: form-data; name=`"themezip`"; filename=`"themeflex-v4.0.0.zip`"`r`nContent-Type: application/zip`r`n`r`n")
$w.Flush(); $ms.Write($zipBytes,0,$zipBytes.Length)
$w.Write("`r`n--$boundary--`r`n"); $w.Flush()
$body = $ms.ToArray(); $ms.Close()

$r4 = [System.Net.HttpWebRequest]::Create("$wpBase/wp-admin/update.php?action=upload-theme")
$r4.CookieContainer = $cookieContainer; $r4.Method = "POST"
$r4.ContentType = "multipart/form-data; boundary=$boundary"
$r4.ContentLength = $body.Length; $r4.Timeout = 120000; $r4.AllowAutoRedirect = $true
$s4 = $r4.GetRequestStream(); $s4.Write($body,0,$body.Length); $s4.Close()
$resp4 = $r4.GetResponse()
$html4 = (New-Object System.IO.StreamReader($resp4.GetResponseStream())).ReadToEnd()
$resp4.Close()

# Check result
if ($html4 -match "Theme installiert|Successfully installed|Aktivieren|activate") {
    Write-Host "`n✅ SUCCESS: ThemeFlex v4.0.0 uploaded to themeflex.de!" -ForegroundColor Green
} elseif ($html4 -match "already exists|bereits vorhanden") {
    Write-Host "`n⚠️  Theme exists — replace? Checking for replace link..." -ForegroundColor Yellow
    $replaceMatch = [regex]::Match($html4, 'href="([^"]*replace[^"]*)"')
    if ($replaceMatch.Success) {
        $replaceUrl = $replaceMatch.Groups[1].Value -replace '&amp;','&'
        if (-not $replaceUrl.StartsWith("http")) { $replaceUrl = "$wpBase$replaceUrl" }
        Write-Host "Following replace link: $replaceUrl"
        $r5 = [System.Net.HttpWebRequest]::Create($replaceUrl)
        $r5.CookieContainer = $cookieContainer; $r5.Method = "GET"
        $html5 = (New-Object System.IO.StreamReader($r5.GetResponse().GetResponseStream())).ReadToEnd()
        if ($html5 -match "Theme installiert|Successfully installed|aktualisiert|updated") {
            Write-Host "✅ Theme replaced successfully!" -ForegroundColor Green
        } else { Write-Host $html5.Substring(0,[Math]::Min(500,$html5.Length)) }
    }
} else {
    Write-Host "`n❌ Unexpected response:" -ForegroundColor Red
    Write-Host $html4.Substring(0,[Math]::Min(800,$html4.Length))
}
Write-Host "`nDone." -ForegroundColor Cyan
