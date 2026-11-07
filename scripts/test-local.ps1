param(
    [ValidateSet('success','error')][string]$ExpectedMode = 'success',
    [ValidateRange(1024,65535)][int]$Port = 8080
)
$ErrorActionPreference = 'Stop'
# The host is intentionally fixed: never run these submissions against production.
$baseUrl = 'http://localhost:' + $Port
$checks = 0
function Assert-Local($condition, $message) {
    if (-not $condition) { throw $message }
    $script:checks++
    Write-Host ('PASS: ' + $message)
}

Add-Type -AssemblyName System.Net.Http
$client = New-Object System.Net.Http.HttpClient
$client.Timeout = [TimeSpan]::FromSeconds(20)
try {
    foreach ($path in @('/', '/contact-us/', '/terms-condition/', '/privacy-policy/')) {
        $response = $client.GetAsync($baseUrl + $path).GetAwaiter().GetResult()
        $html = $response.Content.ReadAsStringAsync().GetAwaiter().GetResult()
        Assert-Local ([int]$response.StatusCode -eq 200) ($path + ' returns HTTP 200')
        Assert-Local ($html.Contains('Local preview &mdash; enquiries simulate')) ($path + ' is explicitly a local simulator')
        Assert-Local ($html -notmatch 'const RM_SECRET') ($path + ' does not expose the old browser secret assignment')
        if ($path -eq '/contact-us/') {
            Assert-Local ($html.Contains('id="customForm"')) 'Contact form renders'
        }
    }

    foreach ($asset in @('assets/css/main.css','assets/css/vendor/swiper-bundle.min.css','assets/js/min/app.js','assets/img/renomacs-white.svg','assets/img/whatsapp-icon.svg','assets/fonts/Poppins-Bold.woff2')) {
        $response = $client.GetAsync($baseUrl + '/wp-content/themes/renomacs/' + $asset).GetAwaiter().GetResult()
        Assert-Local ([int]$response.StatusCode -eq 200) ($asset + ' loads')
    }
    $response = $client.GetAsync($baseUrl + '/renomacs-qa-nonexistent-page/').GetAwaiter().GetResult()
    Assert-Local ([int]$response.StatusCode -eq 404) 'Unknown page returns HTTP 404'

    $payload = '{"fullname":"Local QA","email":"qa@example.invalid","contact":"0000000000","consent":"Yes"}'
    $body = New-Object System.Net.Http.StringContent($payload, [Text.Encoding]::UTF8, 'application/json')
    $response = $client.PostAsync($baseUrl + '/wp-json/custom/v1/contact', $body).GetAwaiter().GetResult()
    $result = $response.Content.ReadAsStringAsync().GetAwaiter().GetResult() | ConvertFrom-Json
    $expectedStatus = if ($ExpectedMode -eq 'success') { 200 } else { 503 }
    Assert-Local ([int]$response.StatusCode -eq $expectedStatus -and $result.mock -eq $true -and $result.result -eq $ExpectedMode) 'Enquiry HTTP response matches the selected simulator mode'

    # Small sequential stability sample, not a load test or uptime guarantee.
    for ($i=0; $i -lt 20; $i++) {
        $response = $client.GetAsync($baseUrl + '/contact-us/').GetAwaiter().GetResult()
        if ([int]$response.StatusCode -ne 200) { throw ('Repeated request failed: ' + $i) }
    }
    Assert-Local $true '20 sequential contact-page requests succeeded'
    Write-Host ($checks.ToString() + ' local HTTP checks passed.')
} finally {
    $client.Dispose()
}
