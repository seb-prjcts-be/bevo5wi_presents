@echo off
cd /d "c:\server\htdocs\5BEVOwi_presents"
echo [%date% %time%] Sync gestart >> sync_log.txt
"C:\Users\seb_p\AppData\Local\Python\bin\python.exe" download_collections.py >> sync_log.txt 2>&1
echo [%date% %time%] Sync klaar >> sync_log.txt
