$ErrorActionPreference = 'Stop'
$repoPath = Split-Path -Parent $PSScriptRoot

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw 'Install and start Docker Desktop with WSL 2 and Linux containers, then reopen PowerShell. See README.md.'
}

Push-Location $repoPath
try {
    $dockerOsType = & docker info --format '{{.OSType}}'
    if ($LASTEXITCODE -ne 0) { throw 'Docker is not running. Start Docker Desktop and retry.' }
    if ($dockerOsType -ne 'linux') { throw 'Switch Docker Desktop to Linux containers and retry.' }

    if (-not (Test-Path -LiteralPath '.env')) {
        $settings = @(
            'RM_LOCAL_PORT=8080'
            ('RM_DB_PASSWORD=' + [guid]::NewGuid().ToString('N'))
            ('RM_DB_ROOT_PASSWORD=' + [guid]::NewGuid().ToString('N'))
            ('RM_LOCAL_ADMIN_PASSWORD=' + [guid]::NewGuid().ToString('N'))
            'RM_LOCAL_CONTACT_RESULT=success'
        )
        [System.IO.File]::WriteAllLines((Join-Path $repoPath '.env'), $settings)
        Write-Host 'Created ignored .env with development-only passwords.'
    }

    & docker compose config --quiet
    if ($LASTEXITCODE -ne 0) { throw 'Compose configuration is invalid. Check .env against .env.example.' }

    & docker compose up -d --wait db wordpress
    if ($LASTEXITCODE -ne 0) { throw 'Containers did not become ready. Check docker compose logs.' }

    & docker compose run --rm --no-deps --entrypoint sh cli /opt/renomacs/bootstrap.sh
    if ($LASTEXITCODE -ne 0) { throw 'Local WordPress setup failed. Check the output above.' }

    & docker compose run --rm --no-deps cli eval-file /opt/renomacs/tests/smoke.php
    if ($LASTEXITCODE -ne 0) { throw 'Local preview smoke checks failed.' }

    Write-Host 'Local preview is ready. The setup output above contains its URL.'
    Write-Host 'Admin user: renomacs-local. Password: RM_LOCAL_ADMIN_PASSWORD in your ignored .env.'
} finally {
    Pop-Location
}
