@echo off
echo Testing PHP installation...
php --version
if %errorlevel%==0 (
    echo PHP is working! Testing Composer...
    php composer.phar --version
    if %errorlevel%==0 (
        echo Composer is working!
        echo Now running composer install...
        php composer.phar install
    ) else (
        echo Composer test failed
    )
) else (
    echo PHP not found in PATH. Checking common locations...
    if exist "C:\PHP\php.exe" (
        echo Found PHP at C:\PHP\php.exe
        C:\PHP\php.exe --version
        C:\PHP\php.exe composer.phar --version
    )
    if exist "C:\Program Files\PHP\php.exe" (
        echo Found PHP at C:\Program Files\PHP\php.exe
        "C:\Program Files\PHP\php.exe" --version
        "C:\Program Files\PHP\php.exe" composer.phar --version
    )
)
pause