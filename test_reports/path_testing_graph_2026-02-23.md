# Path Testing Graph and Cyclomatic Complexity

Date: 2026-02-23
Target Script: `path_verification_test.php`

## Control Flow Graph (CFG)

```mermaid
flowchart TD
    A([Start]) --> B[Init test_cases; print table header; all_good=true]
    B --> C{foreach test_cases}
    C -->|next case| D[Resolve full_path; file_exists]
    D --> E{exists?}
    E -->|Yes| F[status=EXISTS; color=green]
    E -->|No| G[status=MISSING; color=red; all_good=false]
    F --> H[Print table row]
    G --> H
    H --> C
    C -->|done| I{all_good?}
    I -->|Yes| J[Print ALL PATHS VERIFIED]
    I -->|No| K[Print SOME FILES MISSING]
    J --> L[Init test_paths]
    K --> L
    L --> M{foreach test_paths}
    M -->|next path| N{description contains app/api?}
    N -->|Yes| O[full_path=__DIR__/app/api + relative]
    N -->|No| P{description contains public?}
    P -->|Yes| Q[full_path=__DIR__/public + relative]
    P -->|No| R{description contains tests/manual?}
    R -->|Yes| S[full_path=__DIR__/tests/manual + relative]
    R -->|No| T[full_path unchanged/empty]
    O --> U[realpath; exists check]
    Q --> U
    S --> U
    T --> U
    U --> V[status/color; print include-path result]
    V --> M
    M -->|done| W([End])
```

## Cyclomatic Complexity

### Method Used
Basis-path style count using explicit control-flow decisions (`if`, `elseif`, `foreach`):

- `foreach ($test_cases ...)` → 1
- `if (!$exists)` → 1
- `if ($all_good)` → 1
- `foreach ($test_paths ...)` → 1
- `if (strpos(... 'app/api/'))` → 1
- `elseif (strpos(... 'public/'))` → 1
- `elseif (strpos(... 'tests/manual/'))` → 1

Total decision points: **D = 7**

Cyclomatic complexity:

$$V(G) = D + 1 = 7 + 1 = 8$$

### Result
**Cyclomatic Complexity = 8**

This means at least 8 independent basis paths are needed for full basis path coverage of this script.
