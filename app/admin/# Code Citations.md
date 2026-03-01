# Code Citations

## License: Apache-2.0
https://github.com/gradle/gradle/blob/3d4c0e819123c79c2761b27f47dcd77e66bd92b8/platforms/jvm/code-quality/src/integTest/groovy/org/gradle/api/plugins/quality/PmdPluginIncrementalAnalysisIntegrationTest.groovy

```
PHPMD is already installed via Composer. Here are the complete setup and run steps for **this project specifically**:

---

## PHPMD — Setup & Run Steps

### Step 1 — Prerequisites (Already Met ✅)

| Requirement | Status |
|---|---|
| PHP 8.2.12 (XAMPP) | ✅ Installed |
| `phpmd/phpmd ^2.15` in `composer.json` | ✅ Declared |
| `vendor/bin/phpmd` binary | ✅ Present |

No installation needed — it came with `composer install`.

---

### Step 2 — If Starting Fresh (composer install)

If `vendor/` is missing, run this once:

```powershell
Set-Location c:\xampp\htdocs\stu
& "C:\xampp\php\php.exe" composer.phar install
```

---

### Step 3 — Basic Syntax

```powershell
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd <source> <format> <ruleset>
```

Or using the batch wrapper (simpler):

```powershell
vendor\bin\phpmd.bat <source> <format> <ruleset>
```

**Arguments:**

| Argument | Options |
|---|---|
| `<source>` | File or folder path, e.g. `app/`, `public/login.php` |
| `<format>` | `html`, `text`, `xml`, `json`, `ansi` |
| `<ruleset>` | `cleancode`, `codesize`, `controversial`, `design`, `naming`, `unusedcode` — comma-separated |

---

### Step 4 — Run Module-Wise (This Project)

```powershell
# app/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd app html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_app_report.html

# public/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd public html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_public_report.html

# core/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd core html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_core_report.html

# ai/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd ai html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_ai_report.html

# tests/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd tests html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_tests_report.html

# Full project (all at once)
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd app,public,core,ai,tests html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_full_report.html
```

---

### Step 5 — Run as Plain Text (Terminal Output)

```powershell
# Quick text scan of any folder
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd app text codesize,unusedcode
```

---

### Step 6 — Custom Threshold via XML Ruleset

Create `phpmd_ruleset.xml` in the project root:

```xml
<?xml version="1.0"?>
<ruleset name="Custom PHPMD Rules"
         xmlns="http://pmd.sf.net/ruleset/1.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0 http://pmd.sf.net/ruleset_xml_
```


## License: unknown
https://github.com/Rebolon/JenkinsSampleApp/blob/c5e842db8b7ee44b4f9421cc4d240923393e4fb9/build/codesize.xml.dist

```
PHPMD is already installed via Composer. Here are the complete setup and run steps for **this project specifically**:

---

## PHPMD — Setup & Run Steps

### Step 1 — Prerequisites (Already Met ✅)

| Requirement | Status |
|---|---|
| PHP 8.2.12 (XAMPP) | ✅ Installed |
| `phpmd/phpmd ^2.15` in `composer.json` | ✅ Declared |
| `vendor/bin/phpmd` binary | ✅ Present |

No installation needed — it came with `composer install`.

---

### Step 2 — If Starting Fresh (composer install)

If `vendor/` is missing, run this once:

```powershell
Set-Location c:\xampp\htdocs\stu
& "C:\xampp\php\php.exe" composer.phar install
```

---

### Step 3 — Basic Syntax

```powershell
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd <source> <format> <ruleset>
```

Or using the batch wrapper (simpler):

```powershell
vendor\bin\phpmd.bat <source> <format> <ruleset>
```

**Arguments:**

| Argument | Options |
|---|---|
| `<source>` | File or folder path, e.g. `app/`, `public/login.php` |
| `<format>` | `html`, `text`, `xml`, `json`, `ansi` |
| `<ruleset>` | `cleancode`, `codesize`, `controversial`, `design`, `naming`, `unusedcode` — comma-separated |

---

### Step 4 — Run Module-Wise (This Project)

```powershell
# app/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd app html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_app_report.html

# public/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd public html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_public_report.html

# core/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd core html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_core_report.html

# ai/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd ai html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_ai_report.html

# tests/ module
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd tests html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_tests_report.html

# Full project (all at once)
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd app,public,core,ai,tests html cleancode,codesize,design,naming,unusedcode --reportfile phpmd_reports\phpmd_full_report.html
```

---

### Step 5 — Run as Plain Text (Terminal Output)

```powershell
# Quick text scan of any folder
& "C:\xampp\php\php.exe" vendor\phpmd\phpmd\src\bin\phpmd app text codesize,unusedcode
```

---

### Step 6 — Custom Threshold via XML Ruleset

Create `phpmd_ruleset.xml` in the project root:

```xml
<?xml version="1.0"?>
<ruleset name="Custom PHPMD Rules"
         xmlns="http://pmd.sf.net/ruleset/1.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0 http://pmd.sf.net/ruleset_xml_
```

