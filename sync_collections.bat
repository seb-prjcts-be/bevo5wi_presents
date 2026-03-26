@echo off
chcp 65001 >nul
cd /d "c:\server\htdocs\5BEVOwi_presents"

:: Lock check — skip if another sync is running (lock < 10 min old)
if not exist sync.lock goto nolock
for /f "usebackq" %%A in (`powershell -nologo -noprofile -command "[math]::Floor((New-TimeSpan -Start (Get-Item 'sync.lock').LastWriteTime).TotalMinutes)"`) do set LOCK_AGE=%%A
if %LOCK_AGE% LSS 10 (
    echo [%date% %time%] Sync overgeslagen >> sync_log.txt
    exit /b 0
)
del sync.lock 2>nul

:nolock
echo %date% %time% > sync.lock

:: Truncate log to last 200 lines
if exist sync_log.txt powershell -nologo -noprofile -command "$l=Get-Content 'sync_log.txt'; if($l.Count -gt 200){$l|Select-Object -Last 200|Set-Content 'sync_log.txt'}"

:: Run sync
echo [%date% %time%] Sync gestart >> sync_log.txt
"C:\Users\seb_p\AppData\Local\Python\bin\python.exe" download_collections.py >> sync_log.txt 2>&1
set SYNC_EXIT=%errorlevel%
echo [%date% %time%] Sync klaar (exit code: %SYNC_EXIT%) >> sync_log.txt

:: Write last_sync.json — determine status
set SYNC_STATUS=ok
if not %SYNC_EXIT% EQU 0 set SYNC_STATUS=error
powershell -nologo -noprofile -command "Set-Content -Encoding UTF8 -Path 'last_sync.json' -Value ('{\"last_sync\":\"' + (Get-Date -Format 'o') + '\",\"status\":\"%SYNC_STATUS%\"}')"

:: Remove lock
del sync.lock 2>nul
