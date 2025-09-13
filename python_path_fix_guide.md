# PowerShell PATH Fix Instructions

## Problem
Python is available in Command Prompt but not in PowerShell due to Windows App Execution Aliases.

## Solution Options

### Option 1: Use Python Launcher (Recommended - Already Working!)
The `py` command works in both PowerShell and Command Prompt. Our AI system now auto-detects this.

### Option 2: Disable Microsoft Store Python Aliases
1. Press Windows + I to open Settings
2. Go to Apps > Advanced app settings > App execution aliases  
3. Turn OFF the toggles for:
   - python.exe
   - python3.exe

### Option 3: Add Python to PATH manually
1. Find your actual Python installation:
   - Check: `py -c "import sys; print(sys.executable)"`
2. Add that directory to your PATH environment variable

### Option 4: Use Python in PowerShell Profile
Add this to your PowerShell profile:
```powershell
# Add alias for Python
New-Alias -Name python -Value py -Force
```

## Current Status ✅
- AI system now auto-detects Python using 'py' command
- Python 3.13.7 is working correctly  
- No manual PATH changes needed for the AI system
