# PharmCare - Offline Pharmacy Management System & Vendor Portal
**Version:** 2.2.0 | **Platform:** Windows Standalone Desktop & Cloud Web | **Framework:** Laravel 11.x (PHP 8.2+)

---

## 📖 Overview
**PharmCare** is a production-grade, offline-first Pharmacy Management and Point-of-Sale (POS) system engineered for retail pharmacies, drug shops, and pharmaceutical distributors. It features dual-mode architecture:
1. **Offline Desktop Workstation:** Runs locally on Windows with an embedded SQLite database, zero external dependencies, background service launcher, and automated data directory management.
2. **Cloud Vendor Control Center:** Provides centralized license issuance, fleet installation telemetry, client registries, and remote software update delivery via REST API.

---

## 📚 Complete System Documentation
For in-depth architecture diagrams, database schemas (23 tables), API references, licensing mechanisms, role-based access control, and troubleshooting guides, please read:
👉 **[SYSTEM_DOCUMENTATION.md](file:///c:/xampp/htdocs/pham-care/SYSTEM_DOCUMENTATION.md)**

---

## ✨ Key Features & Capabilities

- **🚀 Point of Sale (POS) & Billing:** High-speed barcode/search checkout, multi-payment options (Cash, Mobile Money, Card), thermal 80mm & A4 receipt printing, pessimistic batch locking (`lockForUpdate`), and atomic transactions.
- **📦 Multi-Unit Packaging System:** Flexible packaging conversion (e.g., Box → Strip → Tablet / Ampoule / Vial) with automated base-unit deduction and pricing tiers.
- **⏳ Batch & FEFO Expiry Tracking:** Strict First-Expiring-First-Out (FEFO) batch allocation, 30/60/90-day expiry risk alerts, and supplier traceability.
- **🔄 Sales Returns & Refunds:** Item-level return limit enforcement, batch stock restocking, and automated revenue/profit reconciliation.
- **📉 Loss & Stock Adjustments:** Tracks damages, expired write-offs, and stock variances with full double-entry quantity auditing.
- **📋 Stock Ledger Audit Trail (Premium):** Real-time ledger recording every inventory movement with user attribution and polymorphic references.
- **💊 Prescriptions & Doctor Records:** Digital prescription capture with direct transfer into POS billing.
- **🚚 Procurement & Supplier Management:** Purchase orders, batch ingestion, cost tracking, and quick-supplier creation.
- **💰 Financials & Expenses:** Operational expense tracking with daily realized Cost of Goods Sold (COGS), refunds, and net profit calculations.
- **📊 Reports & Business Intelligence:** Sales reports, inventory valuation (cost vs. retail), expiry alerts, and Excel/XLS export capabilities.
- **🔐 RSA-2048 Cryptographic Licensing:** Offline license validation using OpenSSL asymmetric cryptography, machine fingerprinting, and feature gating (`DEFAULT` vs. `PREMIUM`).
- **🛡️ Database Backup & Disaster Recovery:** 1-click SQL/ZIP backups with SHA-256 checksums, automatic pre-restore safety snapshots, and external USB drive export.
- **🩺 Admin Diagnostics:** Database health (`PRAGMA integrity_check`), permissions analysis, disk metrics, and encrypted support tokens.

---

## 👥 User Roles & Access Control

| Role | Permissions Overview |
| :--- | :--- |
| **Admin (`admin`)** | Full system access: User management, system settings, database backups, diagnostics, license management, and vendor portal. |
| **Pharmacist (`pharmacist`)** | POS sales, medicine catalog, batch management, stock adjustments, purchases, prescriptions, expenses, and operational reports. |
| **Cashier (`cashier`)** | POS sales checkout, invoice viewing, customer registration, and read-only medication search. |

---

## 🚀 Quick Start Guide

### 1. Windows Standalone Desktop
- Run `PharmCare_Setup_v2.2.0.exe` (built via Inno Setup).
- Launch the application via the Desktop shortcut or `start_pharmcare.bat`.
- The system automatically bootstraps `%APPDATA%\PharmCare`, applies migrations, starts the background server, and opens your browser at `http://127.0.0.1:8000`.
- Initial Administrator Credentials:
  - **Email:** `admin@pharmcare.local`
  - **Password:** `admin123`

### 2. Development Setup
```bash
# Clone and enter directory
cd c:\xampp\htdocs\pham-care

# Install PHP dependencies
composer install

# Environment setup
copy .env.example .env
php artisan key:generate

# Migrate and seed Ugandan pharmaceutical data
php artisan migrate --seed

# Build frontend assets
npm install
npm run build

# Start local dev server
php artisan serve
```

### 3. Docker Deployment
```bash
docker build -t pharmcare:latest .
docker run -d -p 10000:10000 -e PORT=10000 --name pharmcare pharmcare:latest
```

---

## 🛠️ Testing & Quality Assurance
Run the automated test suite covering authentication, upgrades, expenses, sales returns, reports, and reference data:
```bash
php artisan test
```

---
*PharmCare Software Solutions © 2026. All rights reserved.*

