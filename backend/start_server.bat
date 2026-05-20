@echo off
REM Запуск FastAPI сервера
cd /d %~dp0
echo Запуск сервера на порту 8000...
python -m uvicorn main:app --reload --host 0.0.0.0 --port 8000
pause
