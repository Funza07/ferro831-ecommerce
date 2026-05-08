# FERRO831 Deployment Guide (Hostinger)

## 1. Deployment Scope
This checklist targets moving from local XAMPP to Hostinger shared hosting without changing business behavior.

## 2. Pre-Deployment Checklist
- [ ] Backup codebase and current database.
- [ ] Export fresh production-ready SQL.
- [ ] Verify no debug/test credentials remain in code.
- [ ] Confirm writable permissions needed for image upload path.

## 3. Hostinger Setup Checklist
- [ ] Create MySQL database in Hostinger panel.
- [ ] Create DB user and password.
- [ ] Import SQL dump into Hostinger database.
- [ ] Upload project files to hosting path (`public_html` or subfolder).

## 4. Mandatory Config Changes

### 4.1 Database credentials
Update `config/db.php` from local values:
- `localhost / root / empty password / ferro831_db`

to Hostinger-provided values:
- host, username, password, database from Hostinger panel.

### 4.2 Base path and hardcoded URLs
Project currently uses hardcoded `/ferro831/` in several redirects and route helpers.

- [ ] Review `app/helpers/functions.php` (`BASE_URL`).
- [ ] Review controllers using hardcoded redirects like `/ferro831/...`.
- [ ] Adjust to final domain/subfolder path.

### 4.3 `mysqli_stmt_get_result` compatibility
Some models rely on `mysqli_stmt_get_result`.

- [ ] Verify Hostinger PHP build supports mysqlnd.
- [ ] If not supported, replace affected calls with bind/fetch style query handling.

## 5. Upload/Filesystem Checklist
- [ ] Ensure `assets/images/products/` exists in production.
- [ ] Ensure write permission for PHP uploads to `assets/images/products/`.
- [ ] Validate image upload and display after deployment.

## 6. Runtime Checklist After Go-Live
- [ ] Homepage and product pages load.
- [ ] Register/login/logout work.
- [ ] Cart + checkout + order placement work.
- [ ] Admin login/dashboard/products/orders work.
- [ ] Order status updates write history.
- [ ] Variant stock sync behavior works.

## 7. Security Hardening (Recommended)
- [ ] Disable directory listing.
- [ ] Force HTTPS.
- [ ] Keep file permissions least-privilege.
- [ ] Rotate default admin password.
- [ ] Restrict direct access to sensitive files if possible.

## 8. Rollback Plan
- [ ] Keep pre-deployment code archive.
- [ ] Keep pre-deployment DB dump.
- [ ] If critical issue occurs: restore DB + restore code + clear any cached config.
