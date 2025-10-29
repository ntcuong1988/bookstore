@echo off
setlocal

REM Thư mục chứa file .bat (có \ ở cuối)
set "SCRIPT_DIR=%~dp0"

REM Tìm php.exe (PATH -> H:\xampp -> C:\xampp)
set "PHP_EXE="
for /f "delims=" %%I in ('where php 2^>nul') do set "PHP_EXE=%%I"
if not defined PHP_EXE if exist "H:\xampp\php\php.exe" set "PHP_EXE=H:\xampp\php\php.exe"

if not defined PHP_EXE (
  echo Khong tim thay php.exe. Hay sua duong dan PHP_EXE trong file .bat.
  pause
  exit /b 1
)

REM Lệnh mặc định: up (có thể truyền status/down/seed khi gọi .bat)
set "CMD=%~1"
if "%CMD%"=="" set "CMD=up"

echo Using "%PHP_EXE%"
"%PHP_EXE%" "%SCRIPT_DIR%migrate.php" %CMD% %~2 %~3 %~4
pause
