#!/usr/bin/env pwsh
# Student Survey System - Server Shutdown Script
# This script stops both backend and frontend servers

Write-Host "`n========================================" -ForegroundColor Red
Write-Host "  Student Survey System - Shutdown" -ForegroundColor White
Write-Host "========================================`n" -ForegroundColor Red

# Function to kill processes on a port
function Kill-PortProcess {
    param([int]$Port, [string]$Name)
    
    $process = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | 
               Select-Object -ExpandProperty OwningProcess -First 1
    
    if ($process) {
        Stop-Process -Id $process -Force -ErrorAction SilentlyContinue
        Write-Host "✓ Stopped $Name (Port $Port)" -ForegroundColor Green
    } else {
        Write-Host "- $Name was not running" -ForegroundColor Gray
    }
}

Write-Host "Stopping servers..." -ForegroundColor Yellow

Kill-PortProcess -Port 5000 -Name "Backend Server"
Kill-PortProcess -Port 5173 -Name "Frontend Server"
Kill-PortProcess -Port 5174 -Name "Frontend Server (Alt)"
Kill-PortProcess -Port 5175 -Name "Frontend Server (Alt)"

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "  ✓ All Servers Stopped" -ForegroundColor White
Write-Host "========================================`n" -ForegroundColor Green

Start-Sleep -Seconds 2
