# Start Both Servers Script
# This script starts both the backend and frontend servers for the Student Survey System

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  Student Survey System - Server Startup" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Function to kill process on port
function Kill-PortProcess {
    param([int]$Port)
    
    $process = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | 
               Select-Object -ExpandProperty OwningProcess -First 1
    
    if ($process) {
        Write-Host "Stopping existing process on port $Port..." -ForegroundColor Yellow
        Stop-Process -Id $process -Force -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 1
    }
}

# Clean up existing processes
Write-Host "Cleaning up existing processes..." -ForegroundColor Yellow
Kill-PortProcess -Port 5000
Kill-PortProcess -Port 5173
Kill-PortProcess -Port 5174
Kill-PortProcess -Port 5175

# Check if MySQL is running
Write-Host ""
Write-Host "Checking MySQL status..." -ForegroundColor Yellow
$mysql = Get-Process mysqld -ErrorAction SilentlyContinue
if (-not $mysql) {
    Write-Host "WARNING: MySQL is not running! Please start XAMPP first." -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
} else {
    Write-Host "MySQL is running" -ForegroundColor Green
}

Write-Host ""
Write-Host "Starting servers in new windows..." -ForegroundColor Cyan
Write-Host ""

# Start Backend Server in new window
Write-Host "Starting Backend Server..." -ForegroundColor Green
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd c:\xampp\htdocs\stu\stu_react\backend; Write-Host 'Backend Server Starting...' -ForegroundColor Green; node server.js"

Start-Sleep -Seconds 3

# Start Frontend Server in new window
Write-Host "Starting Frontend Server..." -ForegroundColor Green
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd c:\xampp\htdocs\stu\stu_react\frontend; Write-Host 'Frontend Server Starting...' -ForegroundColor Cyan; npm run dev"

Start-Sleep -Seconds 5

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "  Servers Started Successfully!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Backend:  http://localhost:5000" -ForegroundColor Cyan
Write-Host "Frontend: http://localhost:5173" -ForegroundColor Cyan
Write-Host ""
Write-Host "Test Credentials:" -ForegroundColor Yellow
Write-Host "  Student: student@test.com / password123"
Write-Host "  Teacher: teacher@test.com / password123"
Write-Host "  Admin:   admin@test.com / password123"
Write-Host ""
Write-Host "Press any key to exit this window..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
