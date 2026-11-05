@echo off
echo Running database migration: add_filters_column.sql
echo.

REM Check if MySQL is accessible
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: MySQL command not found. Please ensure MySQL is installed and in PATH.
    echo.
    echo Alternative: Run this SQL manually in phpMyAdmin or MySQL Workbench:
    echo   sql/add_filters_column.sql
    pause
    exit /b 1
)

REM Run the migration
mysql -u root -p rtgs_analytics < sql\add_filters_column.sql

if %errorlevel% equ 0 (
    echo.
    echo SUCCESS: Migration completed successfully!
    echo Column 'filters_json' has been added to 'audit_sessions' table.
) else (
    echo.
    echo ERROR: Migration failed. Please check the error message above.
    echo You may need to run the SQL manually.
)

echo.
pause
