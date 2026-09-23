<#
  deploy\windows\retensi.ps1 - retensi harian + rotasi log. Dijalankan Task Scheduler.
#>
param([string]$Config = 'C:\gelita-ops\config.psd1')

$c    = Import-PowerShellDataFile $Config
$logs = [IO.Path]::Combine($c.App, 'writable', 'logs')

# Keluaran php.exe adalah UTF-8. Tanpa baris ini PowerShell membacanya dengan
# code page OEM, dan tanda panah di ringkasan retensi menjadi karakter rusak
# di cron.log. Dibungkus try: proses tanpa konsol menolak pengaturan ini.
try { [Console]::OutputEncoding = [Text.Encoding]::UTF8 } catch { }

# 'Continue': di PowerShell 5.1, stderr program yang dialihkan 2>&1 menjadi galat
# yang menghentikan skrip bila preferensinya 'Stop'.
$ErrorActionPreference = 'Continue'
& $c.Php ([IO.Path]::Combine($c.App, 'spark')) gelita:retention:run 2>&1 |
    Out-File -FilePath (Join-Path $logs 'cron.log') -Append -Encoding utf8
$kode = $LASTEXITCODE

Get-ChildItem $logs -Filter 'log-*.log' |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } |
    Remove-Item

exit $kode
