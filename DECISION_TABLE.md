# Decision Tables

Date: 2026-02-02

Scope: Major server-side decisions and validations identified in the project.

---

## 1) Registration Flow (public/register.php)

### 1.1 Core Registration Validations (all roles)

| Condition | R1 | R2 | R3 | R4 | R5 | R6 | R7 | R8 | R9 | R10 |
|---|---|---|---|---|---|---|---|---|---|---|
| Name provided | N | Y | Y | Y | Y | Y | Y | Y | Y | Y |
| Email provided | Y | N | Y | Y | Y | Y | Y | Y | Y | Y |
| Email unique | Y | Y | N | Y | Y | Y | Y | Y | Y | Y |
| Password provided | Y | Y | Y | N | Y | Y | Y | Y | Y | Y |
| Password length ≥ 6 | Y | Y | Y | Y | N | Y | Y | Y | Y | Y |
| Confirm password provided | Y | Y | Y | Y | Y | N | Y | Y | Y | Y |
| Passwords match | Y | Y | Y | Y | Y | Y | N | Y | Y | Y |
| DOB provided | Y | Y | Y | Y | Y | Y | Y | N | Y | Y |
| Role provided | Y | Y | Y | Y | Y | Y | Y | Y | N | Y |
| **Register allowed** | N | N | N | N | N | N | N | N | N | Y |
| **Show errors** | Y | Y | Y | Y | Y | Y | Y | Y | Y | N |

Notes: Any single failure in required checks blocks registration. Email uniqueness is enforced via DB lookup.

### 1.2 Role-Specific: Student

| Condition | S1 | S2 | S3 | S4 |
|---|---|---|---|---|
| Role = student | Y | Y | Y | Y |
| Division provided | N | Y | Y | Y |
| Roll No provided | Y | N | Y | Y |
| Course provided | Y | Y | N | Y |
| **Create student profile** | N | N | N | Y |
| **Show errors** | Y | Y | Y | N |

### 1.3 Role-Specific: Teacher/Admin Access Code

| Condition | T1 | T2 | T3 | T4 | T5 | T6 |
|---|---|---|---|---|---|---|
| Role = teacher/admin | Y | Y | Y | Y | Y | Y |
| Access code provided | N | Y | Y | Y | Y | Y |
| Access code exists for role | Y | N | Y | Y | Y | Y |
| Access code active | Y | Y | N | Y | Y | Y |
| Access code not expired | Y | Y | Y | N | Y | Y |
| Access code below max uses | Y | Y | Y | Y | N | Y |
| **Allow registration** | N | N | N | N | N | Y |
| **Show access code error** | Y | Y | Y | Y | Y | N |

### 1.4 Role-Specific: Teacher Profile

| Condition | TP1 | TP2 | TP3 |
|---|---|---|---|
| Role = teacher | Y | Y | Y |
| Department provided | N | Y | Y |
| Subjects provided | Y | N | Y |
| **Create teacher profile** | N | N | Y |
| **Show errors** | Y | Y | N |

---

##Condition | L1 | L2 | L3 | L4 | L5 |
|---|---|---|---|---|---|
| Email provided | N | Y | Y | Y | Y |
| Password provided | Y | N | Y | Y | Y |
| Role provided | Y | Y | N | Y | Y |
| Credentials valid | Y | Y | Y | N | Y |
| **Login success + redirect** | N | N | N | N | Y |
| **Show error** | Y | N | Y | N | Y |
| L4 | Y | Y | Y | N | N | Y |
| L5 | Y | Y | Y | Y | Y | N |

---

## 3) Student Profile Update (app/student/profile.php)
Condition | SP1 | SP2 | SP3 | SP4 | SP5 | SP6 |
|---|---|---|---|---|---|---|
| Name provided | N | Y | Y | Y | Y | Y |
| DOB provided | Y | N | Y | Y | Y | Y |
| Division provided | Y | Y | N | Y | Y | Y |
| Roll No provided | Y | Y | Y | N | Y | Y |
| Course provided | Y | Y | Y | Y | N | Y |
| **Update profile** | N | N | N | N | N | Y |
| **Show errors** | Y | Y | Y | N | N | Y |
| SP6 | Y | Y | Y | Y | Y | Y | N |

---

## 4) Teacher Profile Update (app/teacher/profile.php)
Condition | TPu1 | TPu2 | TPu3 | TPu4 | TPu5 |
|---|---|---|---|---|---|
| Name provided | N | Y | Y | Y | Y |
| DOB provided | Y | N | Y | Y | Y |
| Department provided | Y | Y | N | Y | Y |
| Subjects provided | Y | Y | Y | N | Y |
| **Update profile** | N | N | N | N | Y |
| **Show errors** | Y | Y | N | N | Y |
| TPu5 | Y | Y | Y | Y | Y | N |

---

##Condition | SS1 | SS2 | SS3 |
|---|---|---|---|
| Survey already completed | Y | N | N |
| Required question ratings provided | Y | N | Y |
| **Accept submission** | N | N | Y |
| **Show completion state** | Y | N | N |
| **Show errors** | N | Y | N |
| SS2 | N | N | N | N | Y |
| SS3 | N | Y | Y | N | N |

---
Condition | TS1 | TS2 | TS3 |
|---|---|---|---|
| Survey already completed | Y | N | N |
| Required question ratings provided | Y | N | Y |
| **Accept submission** | N | N | Y |
| **Show completion state** | Y | N | N |
| **Show errors** | N | Y---|
| TS1 | Y | Y | N | Y | N |
| TS2 | N | N | N | N | Y |
| TS3 | N | Y | Y | N | N |
Condition | F1 | F2 | F3 | F4 |
|---|---|---|---|---|
| Subject provided | N | Y | Y | Y |
| Description provided | Y | N | Y | Y |
| Type provided | Y | Y | N | Y |
| **Submit feedback** | N | N | N | Y |
| **Show error** Subject provided | Description provided | Type provided | Submit feedback | Show error |
|---|---|---|---|---|---|
| F1 | N | Y | Y | N | Y |
| F2 | Y | N | Y | N | Y |
| F3 | Y | Y | N | N | Y |
| F4 | Y | Y | Y | Y | N |

Applies to:
- app/student/dashboard.php
- app/teacher/dashboard.php

---
Condition | A1 | A2 | A3 | A4 |
|---|---|---|---|---|
| Title provided | N | Y | Y | Y |
| Description provided | Y | N | Y | Y |
| Target role provided | Y | Y | N | Y |
| **Create survey** | N | N | N | Y |
| **Show errors** Title provided | Description provided | Target role provided | Create survey | Show errors |
|---|---|---|---|---|---|
| A1 | N | Y | Y | N | Y |
| Condition | AS1 | AS2 | AS3 |
|---|---|---|---|
| Status = active | Y | Y | N |
| Each set has ≥ 10 questions | N | Y | Y |
| **Activate** | N | Y | N |
| **Show error**ctivate Survey (min 10 questions per set)

| Rule | Status = active | Each set has ≥ 10 questions | Activate | Show error |
|---|---|---|---|---|
| AS1 | Y | N | N | Y |
| Condition | AQ1 | AQ2 |
|---|---|---|
| Question provided | N | Y |
| **Add question** | N | Y |
| **Show error**dd Question to Set

| Rule | Question provided | Add question | Show error |
|---|---|---|---|
| AQ1 | N | N | Y |
| AQ2 | Y | Y | N |

---

##Condition | TA1 | TA2 |
|---|---|---|
| Action provided and valid | N | Y |
| **Execute handler** | N | Y |
| **Return error** | Y | N |

### 9.2 Import Sheets

| Condition | IS1 | IS2 |
|---|---|---|
| Sheets URL provided | N | Y |
| **Import** | N | Y |
| **Return error** | Y | N |

### 9.3 Upload File

| Condition | UF1 | UF2 |
|---|---|---|
| File present | N | Y |
| **Process upload** | N | Y |
| **Return error**
| Rule | File present | Process upload | Return error |
|---|---|---|---|
| UF1 | N | N | Y |
| UF2 | Y | Y | N |

---

## Notes

- Tables reflect server-side checks and decisions as implemented.
- Some routes assume additional constraints (e.g., radio values) but do not validate them explicitly in PHP.
