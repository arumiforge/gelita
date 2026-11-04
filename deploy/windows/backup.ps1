<#
  deploy\windows\backup.ps1 - backup GELITA di Windows + Laragon.
  Dijalankan Task Scheduler (akun yang sama dengan Laragon) dan oleh deploy.ps1.
  Kode keluar: 0 berhasil; 1 gagal; 2 backup lokal berhasil, salinan kedua gagal.
#>
param([string]$Config = 'C:\gelita-ops\config.psd1', [switch]$Ganti)
# Part 4 (30 §7.5): -Ganti replaces every older backup at both locations with the fresh one (K3);
# KeduaHari in config.psd1 (e.g. 30) limits the second location; empty = kept forever (as before).

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

# 2b. Asset packages from gelita:paket:bangun (docs/rencana-paket-aset.md 5.2),
#     only on the server. Two archives, because public\paket and writable\paket
#     would both end up as 'paket' at the root of one zip. Folders that do not
#     exist yet (no package built) are skipped.
$paketPublik = @('p', 'k') | ForEach-Object { [IO.Path]::Combine($c.App, 'public', 'assets', $_) }
$paketPublik += [IO.Path]::Combine($c.App, 'public', 'paket')
$paketPublik = @($paketPublik | Where-Object { Test-Path $_ })
if ($paketPublik.Count -gt 0) {
    Compress-Archive -Path $paketPublik -DestinationPath (Join-Path $c.Dir "gelita-paket-$stamp.zip") -Force
}
$paketPeta = [IO.Path]::Combine($c.App, 'writable', 'paket')
if (Test-Path $paketPeta) {
    Compress-Archive -Path $paketPeta -DestinationPath (Join-Path $c.Dir "gelita-paket-peta-$stamp.zip") -Force
}

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

# 6. Part 4 (30 7.5): second location kept KeduaHari days when set
$keduaHari = if ($c.ContainsKey('KeduaHari') -and $c.KeduaHari) { [int]$c.KeduaHari } else { 0 }
if ($keduaHari -gt 0) {
    Get-ChildItem $c.Kedua -Filter 'gelita-*' |
        Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$keduaHari) } |
        Remove-Item
}

# 7. -Ganti: test the fresh zip, then remove every older file at both locations
if ($Ganti) {
    $fresh = Join-Path $c.Dir "gelita-db-$stamp.zip"
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    try { [IO.Compression.ZipFile]::OpenRead($fresh).Dispose() } catch { Write-Warning "$stamp uji zip GAGAL; tidak ada yang dihapus"; exit 1 }
    $removed = 0
    foreach ($loc in @($c.Dir, $c.Kedua)) {
        Get-ChildItem $loc -Filter 'gelita-*' | Where-Object { $_.Name -notlike "*$stamp*" } |
            ForEach-Object { Remove-Item $_.FullName; $removed++ }
    }
    Write-Output "$stamp ganti: $removed berkas cadangan lama dihapus; tersisa stempel $stamp"
}

# 8. Status for the governance page warning (30 0.1)
@{ ran_at = (Get-Date).ToString('o'); kedua_hari = $keduaHari; ganti = [bool]$Ganti } | ConvertTo-Json |
    Set-Content -Path ([IO.Path]::Combine($c.App, 'writable', 'backup-status.json')) -Encoding ascii
