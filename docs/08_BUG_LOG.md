# FERRO831 Bug Log

## How to Use
Use this file to track real bugs found during development, QA, or production.

## Priority Legend
- `P1` Critical: checkout/order/data loss/security issue.
- `P2` High: major feature broken, no simple workaround.
- `P3` Medium: partial issue, workaround exists.
- `P4` Low: cosmetic/minor UX issue.

## Status Legend
- `Open`
- `In Progress`
- `Blocked`
- `Ready for Retest`
- `Closed`

## Bug Register

| ID | Date | Area | Summary | Priority | Status | Repro Steps | Expected | Actual | Owner | Fix Ref |
|---|---|---|---|---|---|---|---|---|---|---|
| BUG-001 | 2026-05-08 | Deployment | Hostinger DB credentials not updated from local defaults | P1 | Open | Deploy code as-is and try any DB page | App should connect DB | DB connection failure | Solo Dev | `config/db.php` |
| BUG-002 | 2026-05-08 | Deployment | Potential `mysqli_stmt_get_result` incompatibility on Hostinger | P1 | Open | Run flows on host lacking mysqlnd | Queries should execute | Fatal/DB query failure possible | Solo Dev | Models using `mysqli_stmt_get_result` |
| BUG-003 | 2026-05-08 | Routing | Hardcoded `/ferro831/` paths can break in different subfolder/domain | P2 | Open | Deploy to root domain without `/ferro831` path | Redirects should work | Broken redirects/links possible | Solo Dev | helpers/controllers redirects |

## Triage Notes
- Validate P1 issues first before production launch.
- Each closed bug should include test evidence in commit notes.
