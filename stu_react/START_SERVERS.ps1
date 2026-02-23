#!/usr/bin/env pwsh
# Student Survey System - Server Startup Script
# This script starts both backend and frontend servers

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  Student Survey System - Startup" -ForegroundColor White
Write-Host "========================================`n" -ForegroundColor Cyan

# Function to kill processes on a port
function Kill-PortProcess {
    param([int]$Port)
    
    $process = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | 
               Select-Object -ExpandProperty OwningProcess -First 1
    
    if ($process) {
        Stop-Process -Id $process -Force -ErrorAction SilentlyContinue
        Write-Host "✓ Cleaned up port $Port" -ForegroundColor Green
        Start-Sleep -Seconds 1
    }
}

# Clean up existing processes
Write-Host "Cleaning up existing processes..." -ForegroundColor Yellow
Kill-PortProcess -Port 5000
Kill-PortProcess -Port 5173
Kill-PortProcess -Port 5174
Kill-PortProcess -Port 5175

Write-Host "`n"

# Check if MySQL is running
$mysqlRunning = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if (-not $mysqlRunning) {
    Write-Host "⚠ Warning: MySQL doesn't appear to be running!" -ForegroundColor Red
    Write-Host "  Please start XAMPP MySQL service first.`n" -ForegroundColor Yellow
    Read-Host "Press Enter to continue anyway or Ctrl+C to exit"
}

# Start Backend Server
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Starting Backend Server (Port 5000)" -ForegroundColor White
Write-Host "========================================`n" -ForegroundColor Cyan

Start-Process powershell -ArgumentList @(
    "-NoExit",
    "-Command",
    "cd '$PSScriptRoot\backend'; Write-Host '🚀 Backend Server Starting...' -ForegroundColor Green; node server.js"
)

Start-Sleep -Seconds 3

# Start Frontend Server
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  Starting Frontend Server (Port 5173)" -ForegroundColor White
Write-Host "========================================`n" -ForegroundColor Cyan

Start-Process powershell -ArgumentList @(
    "-NoExit",
    "-Command",
    "cd '$PSScriptRoot\frontend'; Write-Host '⚡ Frontend Server Starting...' -ForegroundColor Cyan; npm run dev"
)

Start-Sleep -Seconds 3

# Summary
Write-Host "`n========================================" -ForegroundColor Green
Write-Host "  ✓ Servers Started Successfully!" -ForegroundColor White
Write-Host "========================================`n" -ForegroundColor Green

Write-Host "📊 Backend API:  " -NoNewline -ForegroundColor Yellow
Write-Host "http://localhost:5000" -ForegroundColor White

Write-Host "🌐 Frontend App: " -NoNewline -ForegroundColor Yellow
Write-Host "http://localhost:5173" -ForegroundColor White

Write-Host "`nTest Credentials:" -ForegroundColor Cyan
Write-Host "  Student: student@test.com / password123" -ForegroundColor Gray
Write-Host "  Teacher: teacher@test.com / password123" -ForegroundColor Gray
Write-Host "  Admin:   admin@test.com / password123" -ForegroundColor Gray

Write-Host "`nPress any key to exit this window (servers will continue running)..." -ForegroundColor DarkGray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
