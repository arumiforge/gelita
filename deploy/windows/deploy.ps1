<#
  deploy\windows\deploy.ps1 - pembaruan GELITA di Windows + Laragon.
  Jalankan di PowerShell dengan akun Laragon:
    powershell -NoProfile -ExecutionPolicy Bypass -File C:\laragon\www\gelita\deploy\windows\deploy.ps1 [-Paksa]

  Berkas .ps1 di folder ini sengaja hanya berisi ASCII: Windows PowerShell 5.1
  membaca skrip tanpa BOM sebagai ANSI, dan byte UTF-8 dari tanda panah atau
  tanda pisah menjadi tanda kutip tipografis yang memutus string.
#>
param(
    [string]$Config = 'C:\gelita-ops\config.psd1',
    [switch]$Paksa
)

$ErrorActionPreference = 'Stop'
$c     = Import-PowerShellDataFile $Config
$ops   = Split-Path -Parent $Config
$exe   = if ($env:OS -eq 'Windows_NT') { '.exe' } else { '' }
$spark = Join-Path $c.App 'spark'
$flag  = [IO.Path]::Combine($c.App, 'writable', 'pemeliharaan.flag')

function Langkah([string]$Nama, [scriptblock]$Blok) {
    Write-Output "-> $Nama"
    $global:LASTEXITCODE = 0
    & $Blok
    if ($LASTEXITCODE -ne 0) { throw "$Nama gagal (kode $LASTEXITCODE)" }
}

Set-Location $c.App

Write-Output '-> Periksa kelas yang sedang berjalan'
$aktif = & (Join-Path $c.MySql "mysql$exe") "--defaults-extra-file=$(Join-Path $ops 'backup.cnf')" -N gelita -e "SET time_zone = '+07:00'; SELECT COUNT(*) FROM game_sessions WHERE status = 'active' AND last_active_at > NOW() - INTERVAL 10 MINUTE;"
if ($LASTEXITCODE -ne 0) { throw 'Tidak dapat memeriksa sesi aktif' }
if ([int]$aktif -gt 0 -and -not $Paksa) {
    Write-Host "BATAL: $aktif sesi permainan aktif dalam 10 menit terakhir. Tunda deploy." -ForegroundColor Red
    exit 1
}

Write-Output '-> Backup database'
& (Join-Path $PSScriptRoot 'backup.ps1') -Config $Config
if ($LASTEXITCODE -eq 2) { Write-Warning 'Salinan kedua backup gagal; deploy dilanjutkan dengan backup lokal.' }

Write-Output '-> Mode pemeliharaan'
New-Item -ItemType File -Force -Path $flag | Out-Null

try {
    Langkah 'Ambil kode terbaru' { git pull --ff-only origin $c.Branch }
    Langkah 'Dependency' { composer install --no-dev --optimize-autoloader --no-interaction }

    # akun gelita_migrate lewat DSN - akun aplikasi tidak punya hak DDL
    $env:database_default_DSN = (Get-Content (Join-Path $ops 'migrate.dsn') -Raw).Trim()
    try {
        Langkah 'Migration (akun gelita_migrate)' { & $c.Php $spark migrate }
    } finally {
        Remove-Item Env:\database_default_DSN -ErrorAction SilentlyContinue
    }

    Langkah 'Bersihkan cache' { & $c.Php $spark cache:clear }

    Write-Output '-> Naikkan versi aset'
    $envFile = Join-Path $c.App '.env'
    $versi   = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    $isi     = [IO.File]::ReadAllText($envFile)
    $isi     = [regex]::Replace($isi, '(?m)^gelita\.assetVersion[^\r\n]*', "gelita.assetVersion = '$versi'")
    [IO.File]::WriteAllText($envFile, $isi)          # UTF-8 tanpa BOM

    Langkah 'Verifikasi konten (gagal = situs tetap dalam pemeliharaan)' { & $c.Php $spark gelita:content:verify }
} catch {
    Write-Host "DEPLOY GAGAL - situs tetap dalam mode pemeliharaan. Perbaiki, lalu hapus $flag" -ForegroundColor Red
    throw
}

Write-Output '-> Aktifkan kembali'
Remove-Item $flag
Write-Output 'Deploy selesai.'
