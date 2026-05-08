# FERRO831 Future Roadmap

## 1. Roadmap Goals
Improve stability, deployment portability, and commerce depth while staying manageable for a solo developer.

## 2. Phase Plan

## Phase 1: Production Readiness (Immediate)
- Convert hardcoded `/ferro831/` redirects to environment-based base URL handling.
- Complete Hostinger compatibility pass for `mysqli_stmt_get_result` risk.
- Add centralized environment config strategy (local vs production).
- Add deployment smoke-test script/checklist execution routine.

## Phase 2: Core Commerce Improvements
- Add coupon/discount support.
- Add shipping charge rules by location/order value.
- Add COD/prepaid payment status fields and flow.
- Add inventory movement log (stock in/out audit).

## Phase 3: Customer Experience
- Improve product search relevance and filtering UX.
- Add recently viewed products.
- Add better order tracking timeline view.
- Add email notifications for order status updates.

## Phase 4: Admin Operations
- Bulk product/variant update tools.
- CSV import/export for catalog.
- Advanced order filters and export.
- Dashboard trend charts with date range filters.

## Phase 5: Reliability & Security
- Add CSRF protection across forms.
- Add stronger server-side validation rules.
- Add error logging and alerting strategy.
- Add backup automation for DB and uploads.

## 3. Technical Debt Backlog
- Standardize all redirects to use helper-generated URLs.
- Reduce duplicate wrapper and router handling where practical.
- Improve separation of controller and data validation concerns.
- Add consistent unit/integration test approach.

## 4. Suggested Milestones (Solo-Friendly)
1. `M1` (1-2 weeks): Deployment hardening + path/config cleanup.
2. `M2` (2-3 weeks): Checkout/order reliability and payment-ready data model prep.
3. `M3` (2-4 weeks): Admin productivity improvements (bulk ops/export).
4. `M4` (ongoing): Security + monitoring + automated backups.
