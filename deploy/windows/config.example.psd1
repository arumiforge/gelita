# deploy\windows\config.example.psd1 - salin ke C:\gelita-ops\config.psd1, lalu
# sesuaikan. Berkas ini dibaca deploy.ps1, backup.ps1, dan retensi.ps1.
# Jangan mengedit salinan di repositori: nilai khusus server hanya di C:\gelita-ops.
@{
    App    = 'C:\laragon\www\gelita'
    Php    = 'C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe'   # sesuaikan versi
    MySql  = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin'            # sesuaikan versi
    Dir    = 'D:\gelita-backup'                                        # backup lokal
    Kedua  = 'E:\gelita-backup'                                        # drive eksternal / \\nas\backup\gelita
    Branch = 'main'
}
