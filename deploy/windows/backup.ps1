<#
  deploy\windows\backup.ps1 - backup GELITA di Windows + Laragon.
  Dijalankan Task Scheduler (akun yang sama dengan Laragon) dan oleh deploy.ps1.
  Kode keluar: 0 berhasil; 1 gagal; 2 backup lokal berhasil, salinan kedua gagal.
#>
param([string]$Config = 'C:\gelita-ops\config.psd1')

$ErrorActionPreference = 'Stop'
$c     = Import-PowerShellDataFile $Config
$ops   = Split-Path -Parent $Config
$exe   = if ($env:OS -eq 'Windows_NT') { '.exe' } else { '' }
$dump  = Join-Path $c.MySql "mysqldump$exe"
$cnf   = Join-Path $ops 'backup.cnf'
$stamp = Get-Date -Format 'yyyyMMdd-HHmm'

New-Item -ItemType Directory -Force -Path $c.Dir | Out-Null
$sqlData = Join-Path $c.Dir "gelita-db-$stamp.sql"
$sqlSesi = Join-Path $c.Dir "gelita-db-$stamp-ci_sessions.sql"

# 1. Database. --result-file, bukan ">": pengalihan PowerShell 5.1 menulis UTF-16.
#    Isi ci_sessions (IP mentah + data sesi) tidak ikut; strukturnya tetap.
try {
    & $dump "--defaults-extra-file=$cnf" --single-transaction --quick --no-tablespaces `
        --default-character-set=utf8mb4 --ignore-table=gelita.ci_sessions `
        "--result-file=$sqlData" gelita
    if ($LASTEXITCODE -ne 0) { throw "mysqldump gagal (kode $LASTEXITCODE)" }

    & $dump "--defaults-extra-file=$cnf" --no-data --no-tablespaces `
        "--result-file=$sqlSesi" gelita ci_sessions
    if ($LASTEXITCODE -ne 0) { throw "mysqldump ci_sessions gagal (kode $LASTEXITCODE)" }

    Compress-Archive -Path $sqlData, $sqlSesi -DestinationPath (Join-Path $c.Dir "gelita-db-$stamp.zip") -Force
} finally {
    Remove-Item $sqlData, $sqlSesi -ErrorAction SilentlyContinue
}

# 2. Aset yang diunggah admin (tidak ada di git)
Compress-Archive -Path ([IO.Path]::Combine($c.App, 'public', 'assets', 'uploads')) `
    -DestinationPath (Join-Path $c.Dir "gelita-uploads-$stamp.zip") -Force

# 3. Konfigurasi - memuat encryption.key dan kredensial. Copy-Item mewarisi
#    stempel waktu .env; tanpa baris kedua, salinan dari .env yang tidak berubah
#    lebih dari 30 hari langsung terhapus langkah 4 dan tidak pernah disalin.
$salinanEnv = Join-Path $c.Dir "gelita-env-$stamp.txt"
Copy-Item (Join-Path $c.App '.env') $salinanEnv
(Get-Item $salinanEnv).LastWriteTime = Get-Date

# 4. Simpan 30 hari di lokasi pertama
Get-ChildItem $c.Dir -Filter 'gelita-*' |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } |
    Remove-Item

Write-Output "$stamp backup lokal selesai"

# 5. Salinan kedua di luar mesin ini. Gagal = kode keluar 2, bukan diam.
try {
    New-Item -ItemType Directory -Force -Path $c.Kedua | Out-Null
    Get-ChildItem $c.Dir -Filter 'gelita-*' |
        Where-Object { -not (Test-Path (Join-Path $c.Kedua $_.Name)) } |
        Copy-Item -Destination $c.Kedua
} catch {
    Write-Warning "$stamp salinan kedua ke $($c.Kedua) GAGAL: $($_.Exception.Message)"
    exit 2
}
