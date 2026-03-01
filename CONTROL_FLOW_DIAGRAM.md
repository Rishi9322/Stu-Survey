# Control Flow Diagram Analysis
## Student Satisfaction Survey System

**Document Version:** 1.0  
**Date:** February 2, 2026  
**Project:** Student Satisfaction Survey System  
**Analysis Type:** Control Flow Testing

---

## 1. Introduction to Control Flow Testing

### 1.1 Definition

Control Flow Testing is a structural testing technique that uses the program's control flow as a model. A **Control Flow Graph (CFG)** is a graphical representation of all paths that might be traversed through a program during its execution. The CFG consists of nodes representing basic blocks of code and edges representing control flow paths between them.

### 1.2 Components of Control Flow Graph

- **Entry Node**: Starting point of the program/function
- **Exit Node**: Termination point of the program/function
- **Decision Node**: Points where control flow can branch (if, switch, while conditions)
- **Process Node**: Straight-line code sequences (basic blocks)
- **Junction Node**: Points where multiple paths merge

### 1.3 Cyclomatic Complexity

#### 1.3.1 Definition and Purpose

**Cyclomatic Complexity**, denoted as **V(G)** or **M**, is a quantitative measure of the number of linearly independent paths through a program's source code. Developed by Thomas J. McCabe in 1976, it provides a numerical measure of the complexity of a program's control flow structure.

**Purpose:**
- Determines the minimum number of test cases required for basis path testing
- Identifies areas of code that may be difficult to test or maintain
- Provides a measure of program complexity for quality assessment
- Helps in estimating testing effort and maintenance costs
- Assists in risk assessment and code review prioritization

#### 1.3.2 Mathematical Foundation

Cyclomatic complexity is based on **Graph Theory**, where the control flow graph (CFG) is treated as a directed graph G with:
- **V** = Set of vertices (nodes representing program statements)
- **E** = Set of edges (arcs representing control flow)
- **P** = Number of connected components (usually 1 for a single program)

#### 1.3.3 Calculation Formulas

**Primary Formula (McCabe's Original):**

$$V(G) = E - N + 2P$$

Where:
- **E** = Number of edges in the control flow graph
- **N** = Number of nodes in the control flow graph  
- **P** = Number of connected components (exit points)
  - For a single procedure/function: P = 1
  - For disconnected graphs: P = number of separate components

**Alternative Formula (Decision Point Method):**

$$V(G) = D + 1$$

Where:
- **D** = Number of decision nodes (predicate/branching nodes)
  - Decision nodes include: if, while, for, case, &&, ||, ?:, catch

**Region-Based Formula:**

$$V(G) = R$$

Where:
- **R** = Number of enclosed regions in the planar graph + 1
  - Count all bounded areas in the CFG plus the outer area

**Path-Based Formula:**

$$V(G) = π - s + 2$$

Where:
- **π** = Number of decision points (nodes with outdegree ≥ 2)
- **s** = Number of sink nodes (nodes with outdegree = 0)

#### 1.3.4 Calculation Examples

**Example 1: Simple If Statement**
```
if (x > 0)
    print("positive")
```

- **Nodes (N)**: 4 (entry, condition, print, exit)
- **Edges (E)**: 4 (entry→condition, condition→print, print→exit, condition→exit)
- **Decision nodes (D)**: 1 (if statement)

$$V(G) = E - N + 2 = 4 - 4 + 2 = 2$$
$$V(G) = D + 1 = 1 + 1 = 2$$

**Example 2: If-Else Statement**
```
if (x > 0)
    print("positive")
else
    print("negative")
```

- **Nodes (N)**: 5
- **Edges (E)**: 5
- **Decision nodes (D)**: 1

$$V(G) = E - N + 2 = 5 - 5 + 2 = 2$$
$$V(G) = D + 1 = 1 + 1 = 2$$

**Example 3: Nested If Statements**
```
if (x > 0)
    if (y > 0)
        print("both positive")
```

- **Decision nodes (D)**: 2 (two if statements)

$$V(G) = D + 1 = 2 + 1 = 3$$

#### 1.3.5 Properties and Characteristics

**Key Properties:**
1. **Lower Bound**: V(G) ≥ 1 (minimum for any program)
2. **Upper Bound**: V(G) is maximum when all decisions are independent
3. **Additivity**: V(G₁ + G₂) = V(G₁) + V(G₂) for sequential code
4. **Multiplicativity**: V(G₁ × G₂) = V(G₁) × V(G₂) for nested structures
5. **Independence**: V(G) represents the number of linearly independent paths

**Relationship to Testing:**
- V(G) provides the **upper bound** on the number of test cases needed
- Defines the minimum number of paths to test for basis path coverage
- Each independent path should be tested at least once

#### 1.3.6 Complexity Classification and Interpretation

| **V(G) Range** | **Complexity Level** | **Risk Assessment** | **Recommended Action** |
|----------------|----------------------|---------------------|------------------------|
| **1 - 10**     | Simple               | Low Risk            | Well-structured, easy to test and maintain |
| **11 - 20**    | Moderate             | Moderate Risk       | Consider simplification, increase testing |
| **21 - 50**    | Complex              | High Risk           | Refactor to reduce complexity, extensive testing |
| **> 50**       | Very Complex         | Very High Risk      | Immediate refactoring required, difficult to test |

**Detailed Interpretation:**

- **V(G) = 1-10**: 
  - Simple module with low complexity
  - Easy to understand, test, and maintain
  - Few independent paths
  - Low probability of defects

- **V(G) = 11-20**:
  - Moderately complex module
  - May require additional documentation
  - Increased testing effort required
  - Medium probability of defects

- **V(G) = 21-50**:
  - High complexity module
  - Difficult to understand and maintain
  - High testing effort required
  - High probability of defects
  - Strong candidate for refactoring

- **V(G) > 50**:
  - Very high complexity, likely unmaintainable
  - Error-prone code
  - Very difficult to test thoroughly
  - Should be decomposed into smaller modules
  - Critical refactoring priority

#### 1.3.7 Industry Standards and Best Practices

**SEI (Software Engineering Institute) Recommendations:**
- **V(G) ≤ 10**: Well-structured and stable code
- **V(G) > 10**: Code review and testing should be intensified
- **V(G) > 15**: Requires refactoring before further development

**NIST (National Institute of Standards and Technology):**
- Maximum recommended cyclomatic complexity: **10**
- Modules exceeding 10 should be split into smaller functions

**IEEE Standards:**
- IEEE 982.1-2005 suggests V(G) ≤ 10 for maintainable code

#### 1.3.8 Advantages and Limitations

**Advantages:**
✓ Objective, quantitative measure of complexity  
✓ Language-independent metric  
✓ Easy to calculate from control flow graph  
✓ Correlates well with defect density  
✓ Helps prioritize testing and code reviews  
✓ Useful for estimating testing effort  

**Limitations:**
✗ Does not consider data complexity or data flow  
✗ All decision nodes weighted equally (no consideration of complexity differences)  
✗ Does not measure functionality or size  
✗ May not reflect actual cognitive complexity  
✗ Can be artificially reduced by splitting functions without improving readability  
✗ Does not account for nested complexity depth  

#### 1.3.9 Practical Applications

**1. Test Case Estimation:**
- Minimum test cases required = V(G)
- Each independent path should be covered

**2. Code Review Prioritization:**
- Focus reviews on modules with V(G) > 10
- High V(G) indicates higher defect probability

**3. Refactoring Decisions:**
- Modules with V(G) > 15 are candidates for decomposition
- Extract methods to reduce complexity

**4. Quality Metrics:**
- Track V(G) trends over time
- Monitor complexity growth in new code

**5. Risk Assessment:**
- High V(G) = Higher probability of defects
- Used in software reliability prediction models

### 1.4 Basis Path Testing

Basis path testing uses the cyclomatic complexity to determine the minimum number of test cases required to execute every statement at least once. The basis set consists of independent paths that provide complete coverage.

---

## 2. Overall System Control Flow (End-to-End)

This diagram summarizes the end-to-end control flow across the entire Student Satisfaction Survey System, showing all major roles and their primary interactions.

**Control Flow Diagram (Project-Level):**

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                               SYSTEM ENTRY                                   │
└──────────────────────────────────────────────────────────────────────────────┘
                    │
                    ▼
             ┌─────────────────────────┐
             │ Public Pages            │
             │ (Home, About, Help)     │
             └───────────┬─────────────┘
                   │
                   ▼
             ┌─────────────────────────┐
             │ Authentication Gate     │
             │ (Login / Register)      │
             └───────────┬─────────────┘
                   │
             ┌───────────┼───────────┐
             │           │           │
             ▼           ▼           ▼
          ┌────────────┐ ┌────────────┐ ┌────────────┐
          │ Student    │ │ Teacher    │ │ Admin      │
          │ Role       │ │ Role       │ │ Role       │
          └─────┬──────┘ └─────┬──────┘ └─────┬──────┘
             │              │              │
             ▼              ▼              ▼
       ┌─────────────────┐ ┌─────────────────┐ ┌──────────────────────┐
       │ Student Module  │ │ Teacher Module  │ │ Admin Module         │
       │ (Profile,       │ │ (Profile,       │ │ (Survey Mgmt,        │
       │ Survey)         │ │ Feedback)       │ │ Activation)          │
       └─────┬───────────┘ └─────┬───────────┘ └─────────┬────────────┘
          │                  │                        │
          ▼                  ▼                        ▼
     ┌────────────────────┐ ┌────────────────────┐ ┌────────────────────┐
     │ Student Profile    │ │ Teacher Profile    │ │ Survey Creation /  │
     │ Update (DB write)  │ │ Update (DB write)  │ │ Edit / Activate     │
     └─────────┬──────────┘ └─────────┬──────────┘ └─────────┬──────────┘
         │                      │                      │
         ▼                      ▼                      ▼
     ┌────────────────────┐ ┌────────────────────┐ ┌────────────────────┐
     │ Student Survey     │ │ Teacher Feedback   │ │ Survey Availability│
     │ Submission         │ │ Submission         │ │ for Students       │
     │ (DB write)         │ │ (DB write)         │ │ (DB read)           │
     └─────────┬──────────┘ └─────────┬──────────┘ └─────────┬──────────┘
         │                      │                      │
         ▼                      ▼                      ▼
     ┌────────────────────┐ ┌────────────────────┐ ┌────────────────────┐
     │ Survey Results      │ │ Complaints /       │ │ Reporting /        │
     │ & Ratings Stored    │ │ Suggestions Stored │ │ Dashboards         │
     │ (DB)                │ │ (DB)               │ │ (DB read)          │
     └─────────┬──────────┘ └─────────┬──────────┘ └─────────┬──────────┘
         │                      │                      │
         └──────────────┬───────┴──────────────┬───────┘
               │                      │
               ▼                      ▼
          ┌────────────────────┐   ┌────────────────────┐
          │ AI / Training API  │   │ Analytics / Reports│
          │ (Import, Preview,  │   │ (Admin Views)      │
          │ Export Training    │   │                    │
          │ Data)              │   │                    │
          └─────────┬──────────┘   └─────────┬──────────┘
              │                        │
              ▼                        ▼
          ┌────────────────────┐   ┌────────────────────┐
          │ Training Data      │   │ System Outputs     │
          │ Stored (DB)        │   │ (JSON / UI Pages)  │
          └─────────┬──────────┘   └─────────┬──────────┘
              │                        │
              └──────────────┬─────────┘
                       ▼
               ┌─────────────────┐
               │     EXIT        │
               └─────────────────┘
```

---

## 3. Control Flow Graphs for Student Satisfaction Survey System

### 2.1 Registration Flow (public/register.php)

**Cyclomatic Complexity: V(G) = 8**  
**Decision Nodes:** 7 (empty checks, role check, access code validation, username/email uniqueness)  
**Independent Paths:** 8

**Control Flow Graph:**

```
                            ┌─────────────┐
                            │   START     │
                            │   (Entry)   │
                            └──────┬──────┘
                                   │
                                   ▼
                            ┌─────────────┐
                            │  N1: Check  │
                            │  $_POST     │
                            │  'register' │
                            │  isset?     │
                            └──────┬──────┘
                                   │
                         ┌─────────┴─────────┐
                         │                   │
                      YES│                   │NO
                         ▼                   ▼
                  ┌─────────────┐     ┌─────────────┐
                  │ N2: Check   │     │ N15: Show   │
                  │ All fields  │     │ Registration│
                  │ not empty?  │     │ Form        │
                  └──────┬──────┘     └──────┬──────┘
                         │                   │
                    ┌────┴────┐              │
                 YES│         │NO            │
                    ▼         ▼              │
             ┌─────────┐ ┌─────────────┐    │
             │ N3:     │ │ N14: Error  │    │
             │ Check   │ │ "Fill all"  │    │
             │ password│ └──────┬──────┘    │
             │ >= 6?   │        │           │
             └────┬────┘        │           │
                  │             │           │
             ┌────┴────┐        │           │
          YES│         │NO      │           │
             ▼         ▼        │           │
      ┌─────────┐ ┌─────────┐  │           │
      │ N4:     │ │ N14:    │  │           │
      │ Check   │ │ Error   │  │           │
      │ role    │ │ "Pass   │  │           │
      │ valid?  │ │ >=6"    │  │           │
      └────┬────┘ └────┬────┘  │           │
           │           │        │           │
      ┌────┴────┐      │        │           │
   YES│         │NO    │        │           │
      ▼         ▼      │        │           │
┌──────────┐ ┌────────┴──┐     │           │
│ N5: role │ │ N14: Error│     │           │
│ = teacher│ │ "Invalid  │     │           │
│ or admin?│ │ role"     │     │           │
└────┬─────┘ └───────────┘     │           │
     │                          │           │
  YES│    NO                    │           │
     ▼     ▼                    │           │
┌─────────┐ ┌────────────┐     │           │
│ N6:     │ │ N9: Check  │     │           │
│ Check   │ │ username   │     │           │
│ access  │ │ exists in  │     │           │
│ code    │ │ DB?        │     │           │
│ not     │ └─────┬──────┘     │           │
│ empty?  │       │            │           │
└────┬────┘    YES│    NO      │           │
     │            ▼     ▼      │           │
  YES│   NO  ┌────────┐ ┌──────────┐       │
     ▼    ▼  │ N14:   │ │ N10:     │       │
┌─────────┐  │ Error  │ │ Check    │       │
│ N7:     │  │ "User  │ │ email    │       │
│validate │  │ exists"│ │ exists?  │       │
│Access   │  └────┬───┘ └────┬─────┘       │
│Code()   │       │          │             │
└────┬────┘       │       YES│    NO       │
     │            │          ▼     ▼       │
  YES│   NO       │     ┌────────┐ ┌───────────┐
     ▼    ▼       │     │ N14:   │ │ N11:      │
┌─────────┐       │     │ Error  │ │ register  │
│ N8:     │       │     │ "Email"│ │ User()    │
│ Incr.   │       │     └────┬───┘ │ INSERT DB │
│ uses,   │       │          │     └─────┬─────┘
│ INSERT  │       │          │           │
│ users   │       │          │        SUCCESS
└────┬────┘       │          │           ▼
     │            │          │     ┌───────────┐
     │            │          │     │ N12: Set  │
     │            │          │     │ session,  │
     │            │          │     │ redirect  │
     │            │          │     └─────┬─────┘
     │            │          │           │
     │            └──────────┴───────────┼───────┘
     │                                   │
     └───────────────────────────────────┘
                                         │
                                         ▼
                                  ┌─────────────┐
                                  │    EXIT     │
                                  └─────────────┘
```

**Independent Paths (Basis Set):**

1. **Path 1:** START → N1(NO) → N15 → EXIT
2. **Path 2:** START → N1(YES) → N2(NO) → N14 → EXIT
3. **Path 3:** START → N1(YES) → N2(YES) → N3(NO) → N14 → EXIT
4. **Path 4:** START → N1(YES) → N2(YES) → N3(YES) → N4(NO) → N14 → EXIT
5. **Path 5:** START → N1(YES) → N2(YES) → N3(YES) → N4(YES) → N5(NO) → N9(YES) → N14 → EXIT
6. **Path 6:** START → N1(YES) → N2(YES) → N3(YES) → N4(YES) → N5(YES) → N6(NO) → N14 → EXIT
7. **Path 7:** START → N1(YES) → N2(YES) → N3(YES) → N4(YES) → N5(YES) → N6(YES) → N7(NO) → N14 → EXIT
8. **Path 8:** START → N1(YES) → N2(YES) → N3(YES) → N4(YES) → N5(NO) → N9(NO) → N10(NO) → N11 → N12 → EXIT

---

### 2.2 Login Flow (public/login.php)

**Cyclomatic Complexity: V(G) = 5**  
**Decision Nodes:** 4 (POST check, empty check, user exists, password verify)  
**Independent Paths:** 5

**Control Flow Graph:**

```
                        ┌─────────────┐
                        │   START     │
                        │   (Entry)   │
                        └──────┬──────┘
                               │
                               ▼
                        ┌─────────────┐
                        │  N1: Check  │
                        │  $_POST     │
                        │  'login'    │
                        │  isset?     │
                        └──────┬──────┘
                               │
                      ┌────────┴────────┐
                   YES│                 │NO
                      ▼                 ▼
               ┌─────────────┐   ┌─────────────┐
               │ N2: Check   │   │ N8: Show    │
               │ username &  │   │ Login Form  │
               │ password    │   └──────┬──────┘
               │ not empty?  │          │
               └──────┬──────┘          │
                      │                 │
                 ┌────┴────┐            │
              YES│         │NO          │
                 ▼         ▼            │
          ┌─────────┐ ┌─────────┐      │
          │ N3:     │ │ N7:     │      │
          │ SELECT  │ │ Error   │      │
          │ user    │ │ "Fill   │      │
          │ from DB │ │ all"    │      │
          └────┬────┘ └────┬────┘      │
               │           │            │
          ┌────┴────┐      │            │
       YES│         │NO    │            │
          ▼         ▼      │            │
    ┌─────────┐ ┌─────────┐│           │
    │ N4:     │ │ N7:     ││           │
    │ User    │ │ Error   ││           │
    │ found?  │ │ "Invalid││           │
    │         │ │ user"   ││           │
    └────┬────┘ └────┬────┘│           │
         │           │     │            │
    ┌────┴────┐      │     │            │
 YES│         │NO    │     │            │
    ▼         ▼      │     │            │
┌─────────┐ ┌───────┴─┐   │            │
│ N5:     │ │ N7:     │   │            │
│ password│ │ Error   │   │            │
│ _verify │ │ "Invalid│   │            │
│ match?  │ │ pass"   │   │            │
└────┬────┘ └────┬────┘   │            │
     │           │         │            │
  YES│      NO   │         │            │
     ▼           │         │            │
┌─────────┐      │         │            │
│ N6: Set │      │         │            │
│ session │      │         │            │
│ user_id │      │         │            │
│ role,   │      │         │            │
│ redirect│      │         │            │
└────┬────┘      │         │            │
     │           │         │            │
     └───────────┴─────────┴────────────┘
                 │
                 ▼
          ┌─────────────┐
          │    EXIT     │
          └─────────────┘
```

**Independent Paths (Basis Set):**

1. **Path 1:** START → N1(NO) → N8 → EXIT
2. **Path 2:** START → N1(YES) → N2(NO) → N7 → EXIT
3. **Path 3:** START → N1(YES) → N2(YES) → N3 → N4(NO) → N7 → EXIT
4. **Path 4:** START → N1(YES) → N2(YES) → N3 → N4(YES) → N5(NO) → N7 → EXIT
5. **Path 5:** START → N1(YES) → N2(YES) → N3 → N4(YES) → N5(YES) → N6 → EXIT

---

### 2.3 Student Profile Update (app/student/profile.php)

**Cyclomatic Complexity: V(G) = 4**  
**Decision Nodes:** 3 (POST check, authentication check, empty check)  
**Independent Paths:** 4

**Control Flow Graph:**

```
                        ┌─────────────┐
                        │   START     │
                        │   (Entry)   │
                        └──────┬──────┘
                               │
                               ▼
                        ┌─────────────┐
                        │ N1: Check   │
                        │ session     │
                        │ user_id &   │
                        │ role =      │
                        │ 'student'?  │
                        └──────┬──────┘
                               │
                      ┌────────┴────────┐
                   YES│                 │NO
                      ▼                 ▼
               ┌─────────────┐   ┌─────────────┐
               │ N2: Check   │   │ N7: Redirect│
               │ $_POST      │   │ to login    │
               │ 'update_    │   └──────┬──────┘
               │ profile'    │          │
               │ isset?      │          │
               └──────┬──────┘          │
                      │                 │
                 ┌────┴────┐            │
              YES│         │NO          │
                 ▼         ▼            │
          ┌─────────┐ ┌─────────┐      │
          │ N3:     │ │ N6:     │      │
          │ Check   │ │ Show    │      │
          │ all     │ │ profile │      │
          │ fields  │ │ form    │      │
          │ not     │ │         │      │
          │ empty?  │ └────┬────┘      │
          └────┬────┘      │            │
               │           │            │
          ┌────┴────┐      │            │
       YES│         │NO    │            │
          ▼         ▼      │            │
    ┌─────────┐ ┌─────────┐│           │
    │ N4:     │ │ N5:     ││           │
    │ UPDATE  │ │ Error   ││           │
    │ student_│ │ "Fill   ││           │
    │ profiles│ │ all"    ││           │
    │ WHERE   │ │         ││           │
    │ user_id │ └────┬────┘│           │
    └────┬────┘      │     │            │
         │           │     │            │
         ▼           │     │            │
    ┌─────────┐      │     │            │
    │ N5:     │      │     │            │
    │ Success │      │     │            │
    │ message │      │     │            │
    └────┬────┘      │     │            │
         │           │     │            │
         └───────────┴─────┴────────────┘
                     │
                     ▼
              ┌─────────────┐
              │    EXIT     │
              └─────────────┘
```

**Independent Paths (Basis Set):**

1. **Path 1:** START → N1(NO) → N7 → EXIT
2. **Path 2:** START → N1(YES) → N2(NO) → N6 → EXIT
3. **Path 3:** START → N1(YES) → N2(YES) → N3(NO) → N5 → EXIT
4. **Path 4:** START → N1(YES) → N2(YES) → N3(YES) → N4 → N5 → EXIT

---

### 2.4 Student Survey Submission (app/student/survey.php)

**Cyclomatic Complexity: V(G) = 6**  
**Decision Nodes:** 5 (auth check, POST check, survey active check, already submitted check, loop)  
**Independent Paths:** 6

**Control Flow Graph:**

```
                            ┌─────────────┐
                            │   START     │
                            │   (Entry)   │
                            └──────┬──────┘
                                   │
                                   ▼
                            ┌─────────────┐
                            │ N1: Check   │
                            │ session     │
                            │ user_id &   │
                            │ role =      │
                            │ 'student'?  │
                            └──────┬──────┘
                                   │
                         ┌─────────┴─────────┐
                      YES│                   │NO
                         ▼                   ▼
                  ┌─────────────┐     ┌─────────────┐
                  │ N2: Check   │     │ N9: Redirect│
                  │ $_POST      │     │ to login    │
                  │ 'submit_    │     └──────┬──────┘
                  │ survey'     │            │
                  │ isset?      │            │
                  └──────┬──────┘            │
                         │                   │
                    ┌────┴────┐              │
                 YES│         │NO            │
                    ▼         ▼              │
             ┌─────────┐ ┌─────────┐        │
             │ N3:     │ │ N8:     │        │
             │ SELECT  │ │ Display │        │
             │ survey  │ │ survey  │        │
             │ WHERE   │ │ form    │        │
             │ active  │ └────┬────┘        │
             │ = 1     │      │             │
             └────┬────┘      │             │
                  │           │             │
             ┌────┴────┐      │             │
          YES│         │NO    │             │
             ▼         ▼      │             │
       ┌─────────┐ ┌─────────┐│            │
       │ N4:     │ │ N7:     ││            │
       │ Survey  │ │ Error   ││            │
       │ active? │ │ "No     ││            │
       │         │ │ active" ││            │
       └────┬────┘ └────┬────┘│            │
            │           │     │             │
       ┌────┴────┐      │     │             │
    YES│         │NO    │     │             │
       ▼         ▼      │     │             │
 ┌─────────┐ ┌─────────┐│    │             │
 │ N5:     │ │ N7:     ││    │             │
 │ Check   │ │ Error   ││    │             │
 │ already │ │ "Already││    │             │
 │ submit- │ │ submit" ││    │             │
 │ ted?    │ └────┬────┘│    │             │
 └────┬────┘      │     │    │             │
      │           │     │    │             │
   NO │      YES  │     │    │             │
      ▼           │     │    │             │
 ┌─────────┐      │     │    │             │
 │ N6:     │◄─────┤     │    │             │
 │ LOOP:   │      │     │    │             │
 │ foreach │      │     │    │             │
 │ teacher │      │     │    │             │
 │ INSERT  │      │     │    │             │
 │ teacher_│      │     │    │             │
 │ ratings │      │     │    │             │
 │ survey_ │      │     │    │             │
 │ responses│     │     │    │             │
 └────┬────┘      │     │    │             │
      │           │     │    │             │
      ▼           │     │    │             │
 ┌─────────┐      │     │    │             │
 │ N7:     │      │     │    │             │
 │ Success │      │     │    │             │
 │ message │      │     │    │             │
 └────┬────┘      │     │    │             │
      │           │     │    │             │
      └───────────┴─────┴────┴─────────────┘
                  │
                  ▼
           ┌─────────────┐
           │    EXIT     │
           └─────────────┘
```

**Independent Paths (Basis Set):**

1. **Path 1:** START → N1(NO) → N9 → EXIT
2. **Path 2:** START → N1(YES) → N2(NO) → N8 → EXIT
3. **Path 3:** START → N1(YES) → N2(YES) → N3 → N4(NO) → N7 → EXIT
4. **Path 4:** START → N1(YES) → N2(YES) → N3 → N4(YES) → N5(YES) → N7 → EXIT
5. **Path 5:** START → N1(YES) → N2(YES) → N3 → N4(YES) → N5(NO) → N6(loop once) → N7 → EXIT
6. **Path 6:** START → N1(YES) → N2(YES) → N3 → N4(YES) → N5(NO) → N6(loop multiple) → N7 → EXIT

---

### 2.5 Teacher Survey Submission (app/teacher/survey.php)

**Cyclomatic Complexity: V(G) = 5**  
**Decision Nodes:** 4 (auth check, POST check, empty check, optional complaint)  
**Independent Paths:** 5

**Control Flow Graph:**

```
                            ┌─────────────┐
                            │   START     │
                            │   (Entry)   │
                            └──────┬──────┘
                                   │
                                   ▼
                            ┌─────────────┐
                            │ N1: Check   │
                            │ session     │
                            │ user_id &   │
                            │ role =      │
                            │ 'teacher'?  │
                            └──────┬──────┘
                                   │
                         ┌─────────┴─────────┐
                      YES│                   │NO
                         ▼                   ▼
                  ┌─────────────┐     ┌─────────────┐
                  │ N2: Check   │     │ N8: Redirect│
                  │ $_POST      │     │ to login    │
                  │ 'submit_    │     └──────┬──────┘
                  │ feedback'   │            │
                  │ isset?      │            │
                  └──────┬──────┘            │
                         │                   │
                    ┌────┴────┐              │
                 YES│         │NO            │
                    ▼         ▼              │
             ┌─────────┐ ┌─────────┐        │
             │ N3:     │ │ N7:     │        │
             │ Check   │ │ Display │        │
             │ feedback│ │ feedback│        │
             │ subject │ │ form    │        │
             │ & content│└────┬────┘        │
             │ not     │      │             │
             │ empty?  │      │             │
             └────┬────┘      │             │
                  │           │             │
             ┌────┴────┐      │             │
          YES│         │NO    │             │
             ▼         ▼      │             │
       ┌─────────┐ ┌─────────┐│            │
       │ N4:     │ │ N6:     ││            │
       │ INSERT  │ │ Error   ││            │
       │ teacher_│ │ "Fill   ││            │
       │ ratings │ │ required││            │
       └────┬────┘ └────┬────┘│            │
            │           │     │             │
            ▼           │     │             │
       ┌─────────┐      │     │             │
       │ N5:     │      │     │             │
       │ Check   │      │     │             │
       │ complaint│     │     │             │
       │ not     │      │     │             │
       │ empty?  │      │     │             │
       └────┬────┘      │     │             │
            │           │     │             │
       ┌────┴────┐      │     │             │
    YES│         │NO    │     │             │
       ▼         ▼      │     │             │
 ┌─────────┐ ┌─────────┐│    │             │
 │ N6:     │ │ N6:     ││    │             │
 │ INSERT  │ │ Success ││    │             │
 │ suggest-│ │ message ││    │             │
 │ ions_   │ │         ││    │             │
 │ compla- │ │         ││    │             │
 │ ints    │ │         ││    │             │
 └────┬────┘ └────┬────┘│    │             │
      │           │     │    │             │
      ▼           │     │    │             │
 ┌─────────┐      │     │    │             │
 │ N7:     │      │     │    │             │
 │ Success │      │     │    │             │
 │ message │      │     │    │             │
 └────┬────┘      │     │    │             │
      │           │     │    │             │
      └───────────┴─────┴────┴─────────────┘
                  │
                  ▼
           ┌─────────────┐
           │    EXIT     │
           └─────────────┘
```

**Independent Paths (Basis Set):**

1. **Path 1:** START → N1(NO) → N8 → EXIT
2. **Path 2:** START → N1(YES) → N2(NO) → N7 → EXIT
3. **Path 3:** START → N1(YES) → N2(YES) → N3(NO) → N6 → EXIT
4. **Path 4:** START → N1(YES) → N2(YES) → N3(YES) → N4 → N5(NO) → N6 → EXIT
5. **Path 5:** START → N1(YES) → N2(YES) → N3(YES) → N4 → N5(YES) → N6 → N7 → EXIT

---

### 2.6 Admin Survey Management (app/admin/survey_management.php)

**Cyclomatic Complexity: V(G) = 9**  
**Decision Nodes:** 8 (auth, action routing: create/edit/delete/activate, validation checks)  
**Independent Paths:** 9

**Control Flow Graph:**

```
                                    ┌─────────────┐
                                    │   START     │
                                    │   (Entry)   │
                                    └──────┬──────┘
                                           │
                                           ▼
                                    ┌─────────────┐
                                    │ N1: Check   │
                                    │ session &   │
                                    │ role =      │
                                    │ 'admin'?    │
                                    └──────┬──────┘
                                           │
                                  ┌────────┴────────┐
                               YES│                 │NO
                                  ▼                 ▼
                           ┌─────────────┐   ┌─────────────┐
                           │ N2: Check   │   │ N12: Redir- │
                           │ action      │   │ ect login   │
                           │ parameter   │   └──────┬──────┘
                           └──────┬──────┘          │
                                  │                 │
        ┌─────────────────────────┼─────────────────┼──────────┐
        │           │             │             │   │          │
        ▼           ▼             ▼             ▼   │          │
   ┌────────┐  ┌────────┐   ┌────────┐   ┌────────┐│          │
   │ N3:    │  │ N5:    │   │ N7:    │   │ N9:    ││          │
   │ action │  │ action │   │ action │   │ action ││          │
   │ =      │  │ =      │   │ =      │   │ =      ││          │
   │ create?│  │ edit?  │   │ delete?│   │activate││          │
   └───┬────┘  └───┬────┘   └───┬────┘   └───┬────┘│          │
       │           │            │            │     │          │
       ▼           ▼            ▼            ▼     │          │
   ┌────────┐  ┌────────┐   ┌────────┐   ┌────────┐│          │
   │ N4:    │  │ N6:    │   │ N8:    │   │ N10:   ││          │
   │ Check  │  │ Check  │   │ DELETE │   │ Check  ││          │
   │ title, │  │ fields │   │ surveys│   │ survey ││          │
   │ desc   │  │ not    │   │ WHERE  │   │ has    ││          │
   │ not    │  │ empty, │   │ id=?   │   │ >=10   ││          │
   │ empty? │  │ INSERT │   └───┬────┘   │ quest? ││          │
   └───┬────┘  └───┬────┘       │        └───┬────┘│          │
       │           │            │            │     │          │
    YES│      NO   │         SUCCESS     YES│  NO │          │
       ▼           ▼            │            ▼     ▼          │
   ┌────────┐  ┌────────┐      │        ┌────────┐ ┌────────┐│
   │ INSERT │  │ Error  │      │        │ N11:   │ │ N11:   ││
   │ surveys│  │ "Fill" │      │        │ UPDATE │ │ Error  ││
   └───┬────┘  └───┬────┘      │        │ active │ │ "Need  ││
       │           │            │        │ = 1    │ │ 10 Q"  ││
       ▼           │            │        └───┬────┘ └───┬────┘│
   ┌────────┐      │            │            │          │     │
   │ Success│      │            │            ▼          │     │
   └───┬────┘      │            │        ┌────────┐     │     │
       │           │            │        │Success │     │     │
       └───────────┴────────────┴────────┴───┬────┴─────┴─────┘
                                             │
                                             ▼
                                      ┌─────────────┐
                                      │    EXIT     │
                                      └─────────────┘
```

**Independent Paths (Basis Set):**

1. **Path 1:** START → N1(NO) → N12 → EXIT
2. **Path 2:** START → N1(YES) → N2 → N3 → N4(NO) → Error → EXIT
3. **Path 3:** START → N1(YES) → N2 → N3 → N4(YES) → INSERT → Success → EXIT
4. **Path 4:** START → N1(YES) → N2 → N5 → N6(NO) → Error → EXIT
5. **Path 5:** START → N1(YES) → N2 → N5 → N6(YES) → UPDATE → Success → EXIT
6. **Path 6:** START → N1(YES) → N2 → N7 → N8 → Success → EXIT
7. **Path 7:** START → N1(YES) → N2 → N9 → N10(NO) → N11(Error) → EXIT
8. **Path 8:** START → N1(YES) → N2 → N9 → N10(YES) → N11(UPDATE) → Success → EXIT
9. **Path 9:** START → N1(YES) → N2 → (no action match) → Display list → EXIT

---

### 2.7 Training Data API (app/api/training_endpoint.php)

**Cyclomatic Complexity: V(G) = 7**  
**Decision Nodes:** 6 (action routing: upload/import/stats/preview/export + validation)  
**Independent Paths:** 7

**Control Flow Graph:**

```
                                ┌─────────────┐
                                │   START     │
                                │   (Entry)   │
                                └──────┬──────┘
                                       │
                                       ▼
                                ┌─────────────┐
                                │ N1: Check   │
                                │ $_POST      │
                                │ 'action'    │
                                │ isset?      │
                                └──────┬──────┘
                                       │
                              ┌────────┴────────┐
                           YES│                 │NO
                              ▼                 ▼
                       ┌─────────────┐   ┌─────────────┐
                       │ N2: Route   │   │ N9: Return  │
                       │ by action   │   │ 400 error   │
                       └──────┬──────┘   │ "No action" │
                              │          └──────┬──────┘
    ┌─────────────────────────┼──────────┐      │
    │         │        │      │      │   │      │
    ▼         ▼        ▼      ▼      ▼   │      │
┌────────┐┌────────┐┌────────┐┌──────┐┌──────┐ │
│ N3:    ││ N4:    ││ N5:    ││ N6:  ││ N7:  │ │
│ action ││ action ││ action ││action││action│ │
│ =      ││ =      ││ =      ││ =    ││ =    │ │
│ upload_││ import_││ get_   ││prev- ││expor-│ │
│ file?  ││ sheets?││ stats? ││iew?  ││t?    │ │
└───┬────┘└───┬────┘└───┬────┘└──┬───┘└──┬───┘ │
    │         │         │        │      │      │
    ▼         ▼         │        │      │      │
┌────────┐┌────────┐    │        │      │      │
│ N3a:   ││ N4a:   │    │        │      │      │
│ Check  ││ Check  │    │        │      │      │
│ $_FILES││ sheets_│    │        │      │      │
│ upload ││ url not│    │        │      │      │
│ exists?││ empty? │    │        │      │      │
└───┬────┘└───┬────┘    │        │      │      │
    │         │         │        │      │      │
 YES│    NO YES│    NO   │        │      │      │
    ▼    ▼    ▼    ▼    │        │      │      │
┌────────┐ ┌────────┐   │        │      │      │
│ N8:    │ │ N9:    │   │        │      │      │
│ process│ │ Error  │   │        │      │      │
│ Upload │ │ 400    │   │        │      │      │
│ File() │ └───┬────┘   │        │      │      │
└───┬────┘     │        │        │      │      │
    │          │        │        │      │      │
    ▼          │        ▼        ▼      ▼      │
┌────────┐     │    ┌───────┐┌──────┐┌──────┐ │
│ parseT-│     │    │SELECT ││SELECT││SELECT│ │
│ raining│     │    │COUNT, ││*     ││*     │ │
│ Record,│     │    │catego-││LIMIT ││WHERE │ │
│ INSERT │     │    │ry from││20    ││categ-│ │
│ training│    │    │train- ││DESC  ││ory=? │ │
│ _data  │     │    │ing_d  │└──┬───┘└──┬───┘ │
└───┬────┘     │    └───┬───┘   │       │     │
    │          │        │       │       │     │
    ▼          │        ▼       ▼       ▼     │
┌────────┐     │    ┌──────────────────────┐  │
│ Success│     │    │ Return JSON response │  │
│ JSON   │     │    │ {success: true,      │  │
└───┬────┘     │    │  data: [...]}        │  │
    │          │    └──────────┬───────────┘  │
    └──────────┴───────────────┴──────────────┘
               │
               ▼
        ┌─────────────┐
        │    EXIT     │
        └─────────────┘
```

**Independent Paths (Basis Set):**

1. **Path 1:** START → N1(NO) → N9 → EXIT
2. **Path 2:** START → N1(YES) → N2 → N3 → N3a(NO) → N9 → EXIT
3. **Path 3:** START → N1(YES) → N2 → N3 → N3a(YES) → N8 → Success → EXIT
4. **Path 4:** START → N1(YES) → N2 → N4 → N4a(YES) → processGoogleSheetsUrl → Success → EXIT
5. **Path 5:** START → N1(YES) → N2 → N5 → SELECT stats → JSON → EXIT
6. **Path 6:** START → N1(YES) → N2 → N6 → SELECT preview → JSON → EXIT
7. **Path 7:** START → N1(YES) → N2 → N7 → SELECT export → JSON → EXIT

---

## 3. Complexity Analysis Summary

| **Module**                    | **Cyclomatic Complexity V(G)** | **Risk Level** | **Independent Paths** |
|-------------------------------|---------------------------------|----------------|-----------------------|
| Registration Flow             | 8                               | Low            | 8                     |
| Login Flow                    | 5                               | Low            | 5                     |
| Student Profile Update        | 4                               | Low            | 4                     |
| Teacher Profile Update        | 4                               | Low            | 4                     |
| Student Survey Submission     | 6                               | Low            | 6                     |
| Teacher Survey Submission     | 5                               | Low            | 5                     |
| Admin Survey Management       | 9                               | Low            | 9                     |
| Training Data API             | 7                               | Low            | 7                     |

**Overall System Assessment:**
- **Maximum Complexity:** 9 (Admin Survey Management)
- **Average Complexity:** 6
- **Risk Level:** Low to Moderate
- **Maintainability:** Good (all modules V(G) < 10)

---

## 4. Testing Recommendations

### 4.1 Basis Path Coverage

To achieve complete path coverage, test all independent paths identified in each module. For the **Registration Flow** (highest complexity = 8), ensure:
- Test with no POST data
- Test with incomplete fields
- Test with weak password
- Test with invalid role
- Test student/teacher/admin registration separately
- Test with invalid access code
- Test with duplicate username/email
- Test successful registration

### 4.2 Statement Coverage

Ensure every statement in the code is executed at least once. This requires executing all nodes in the CFG.

### 4.3 Branch Coverage

Test both TRUE and FALSE outcomes of every decision node. For a system with 42 total decision nodes (sum of all modules), at least 84 test cases are needed for complete branch coverage.

### 4.4 Path Coverage

While complete path coverage is impractical (exponential growth with loops), the basis path testing ensures reasonable coverage with minimal test cases (48 total paths across all modules).

---

## 5. Conclusion

The control flow analysis reveals that the Student Satisfaction Survey System has **low to moderate complexity** with good structural design. The cyclomatic complexity values (4-9) indicate maintainable code that is not overly complex. The identified independent paths provide a roadmap for systematic testing to ensure all control flow paths are validated.

**Key Findings:**
1. All modules have V(G) ≤ 10 (low risk)
2. Total of 48 independent paths across all modules
3. No excessive branching or deeply nested conditions
4. Clear separation of concerns between modules

**Recommendations:**
- Maintain current complexity levels in future development
- Implement basis path testing for all modules
- Consider refactoring Admin Survey Management (V(G)=9) if new features increase complexity
- Add exception handling paths to control flow graphs in future analysis

---

**End of Control Flow Diagram Analysis**
