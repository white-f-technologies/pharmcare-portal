Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c php artisan serve --host=127.0.0.1 --port=8000", 0, False
Set WshShell = Nothing
