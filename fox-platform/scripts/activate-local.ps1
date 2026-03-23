param(
    [switch]$SkipSmoke
)

$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot

Set-Location $root

Write-Host "[fox-platform] Rodando migrations..."
php ".\apps\api\scripts\migrate.php"

Write-Host "[fox-platform] Rodando seeds..."
php ".\apps\api\scripts\seed.php"

if (-not $SkipSmoke) {
    Write-Host "[fox-platform] Executando smoke test..."
    $job = Start-Job -ScriptBlock {
        Set-Location 'C:\Users\roney\Documents\GitHub\Usuario\teste\fox-platform\apps\api'
        php -S 127.0.0.1:8099 -t public public/router.php
    }

    Start-Sleep -Seconds 3

    try {
        php ".\scripts\smoke-test.php"
    }
    finally {
        Stop-Job $job -ErrorAction SilentlyContinue | Out-Null
        Receive-Job $job -Keep -ErrorAction SilentlyContinue | Out-Null
        Remove-Job $job -Force -ErrorAction SilentlyContinue
    }
}

Write-Host ""
Write-Host "[fox-platform] Subindo stack local..."
node ".\scripts\dev-stack.js"
