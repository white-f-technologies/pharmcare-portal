# PharmCare - Complete System Architecture & Technical Documentation
**Version:** 2.2.0  
**Target Environment:** Windows Standalone Desktop (Offline-First) & Cloud Vendor Portal (Web/Docker)  
**Framework:** Laravel 11.x (PHP 8.2+)  
**Primary Database:** SQLite (Desktop standalone) / MySQL & PostgreSQL (Cloud / Networked Server)  
**Author / Publisher:** PharmCare Software Solutions  

---

## Table of Contents
1. [Executive Summary & System Overview](#1-executive-summary--system-overview)
2. [High-Level Architecture & Tech Stack](#2-high-level-architecture--tech-stack)
3. [User Roles & Access Control Matrix](#3-user-roles--access-control-matrix)
4. [Functional Modules & Business Logic](#4-functional-modules--business-logic)
   - [4.1 Point of Sale (POS) & Billing](#41-point-of-sale-pos--billing)
   - [4.2 Multi-Unit Packaging & Conversion Engine](#42-multi-unit-packaging--conversion-engine)
   - [4.3 Medicine Catalog & Batch Management](#43-medicine-catalog--batch-management)
   - [4.4 Stock Adjustments & Loss/Damage Tracking](#44-stock-adjustments--lossdamage-tracking)
   - [4.5 Sales Returns & Refund Processing](#45-sales-returns--refund-processing)
   - [4.6 Stock Ledger & Full Audit Trail](#46-stock-ledger--full-audit-trail)
   - [4.7 Prescription Management & Doctor Records](#47-prescription-management--doctor-records)
   - [4.8 Procurement & Supplier Management](#48-procurement--supplier-management)
   - [4.9 Expense Tracking & Financial Reconciliation](#49-expense-tracking--financial-reconciliation)
   - [4.10 Reports & Business Intelligence](#410-reports--business-intelligence)
   - [4.11 Cryptographic Licensing & Editions](#411-cryptographic-licensing--editions)
   - [4.12 Vendor Control Center & Management API](#412-vendor-control-center--management-api)
   - [4.13 Database Backup & Disaster Recovery](#413-database-backup--disaster-recovery)
   - [4.14 First-Run Setup Wizard & System Diagnostics](#414-first-run-setup-wizard--system-diagnostics)
5. [Database Schema & Data Dictionary](#5-database-schema--data-dictionary)
6. [API & Routing Reference](#6-api--routing-reference)
7. [Cryptographic Security & Licensing Engine](#7-cryptographic-security--licensing-engine)
8. [Installation, Packaging & Deployment Guide](#8-installation-packaging--deployment-guide)
   - [8.1 Windows Standalone Desktop Installation](#81-windows-standalone-desktop-installation)
   - [8.2 Development Setup (XAMPP / Composer)](#82-development-setup-xampp--composer)
   - [8.3 Cloud & Docker Deployment (Render / Linux)](#83-cloud--docker-deployment-render--linux)
9. [Disaster Recovery, Maintenance & Troubleshooting](#9-disaster-recovery-maintenance--troubleshooting)

---

## 1. Executive Summary & System Overview

**PharmCare** is an offline-first, dual-architecture Pharmacy Information and Point-of-Sale Management System designed specifically for retail pharmacies, drug shops, and wholesale pharmaceutical distribution centers. 

The software resolves the core operational challenges faced by pharmacies:
- **Offline Reliability:** Operates 100% locally on Windows desktop workstations without requiring active internet connectivity.
- **Pharmaceutical Packaging Flexibility:** Tracks medications across fractional and multi-tier units (e.g., Box → Strip → Tablet / Ampoule / Vial / Bottle / Suspensions) with automated mathematical stock deductions.
- **Batch & Expiry Governance (FEFO):** Enforces First-Expiring-First-Out inventory rules, mitigating medication expiration losses and ensuring compliance with National Drug Authority (NDA) statutory regulations.
- **Accurate Financial Reconciliation:** Real-time calculation of Daily Gross Sales, Refunds, Cost of Goods Sold (COGS), Operational Expenses, and Net Realized Profits.
- **RSA-2048 Asymmetric Licensing:** Protects commercial software distribution using cryptographic signatures, offline key generation, grace periods, and edition gating (`DEFAULT` vs. `PREMIUM`).
- **Cloud Vendor Control Center:** A centralized vendor dashboard and REST API for tracking software distribution, client installations, and managing version updates.

---

## 2. High-Level Architecture & Tech Stack

```
+-------------------------------------------------------------------------------+
|                             PHARMCARE ARCHITECTURE                            |
+-------------------------------------------------------------------------------+
|                                                                               |
|  [ STANDALONE CLIENT (Desktop Workstation) ]                                  |
|  +-------------------------------------------------------------------------+  |
|  |  Presentation Layer: Blade Templates, Tailwind CSS, Alpine.js, Chart.js|  |
|  +-------------------------------------------------------------------------+  |
|  |  Application Layer: Laravel 11 HTTP Controllers, Services & Middleware  |  |
|  +-------------------------------------------------------------------------+  |
|  |  Services: LicenseService, SetupService, InstallationService, Feature   |  |
|  +-------------------------------------------------------------------------+  |
|  |  Data Layer: Eloquent ORM + SQLite Embedded Engine (%APPDATA%\PharmCare)|  |
|  +-------------------------------------------------------------------------+  |
|                                     |                                         |
|                  HTTPS Heartbeat / Activation (Optional)                      |
|                                     v                                         |
|  [ CLOUD VENDOR CONTROL CENTER (Central Portal) ]                             |
|  +-------------------------------------------------------------------------+  |
|  |  Vendor Portal Web UI: Client Directory, Release Manager, Key Gen     |  |
|  |  Public REST API: /api/v1/license/activate, /verify, /releases/latest   |  |
|  |  Private Key RSA Storage: storage/keys/private.key (Vendor Only)        |  |
|  +-------------------------------------------------------------------------+  |
+-------------------------------------------------------------------------------+
```

### Core Technologies
- **Backend Language:** PHP 8.2 / 8.3 with extensions (`pdo_sqlite`, `openssl`, `mbstring`, `zip`, `gd`, `bcmath`, `fileinfo`).
- **Web Framework:** Laravel 11.x (MVC, Routing, Eloquent ORM, Blade, Migrations, Artisan CLI).
- **Embedded Database:** SQLite 3 (stored at `%APPDATA%\PharmCare\database\database.sqlite`).
- **Frontend / UI:** Tailwind CSS, Alpine.js, Chart.js, Feather / Heroicons SVG iconography.
- **Packaging & Desktop Distribution:**
  - **Inno Setup 6:** Builds self-contained Windows executable installer (`PharmCare_Setup_v2.2.0.exe`).
  - **Runtime Orchestrator:** `start_pharmcare.bat` + `launch_server.vbs` for silent background execution and port collision handling.
- **Containerization:** Docker (`php:8.2-apache`), multi-stage entrypoint script for cloud deployments on platforms such as Render, AWS, or DigitalOcean.

---

## 3. User Roles & Access Control Matrix

PharmCare enforces strict Role-Based Access Control (RBAC) via the `CheckRole` middleware (`app/Http/Middleware/CheckRole.php`).

| System Feature / Route Area | Admin (`admin`) | Pharmacist (`pharmacist`) | Cashier (`cashier`) |
| :--- | :---: | :---: | :---: |
| **Point of Sale (POS) & Checkout** | Yes | Yes | Yes |
| **Sales Invoices & Customer Lookup** | Yes | Yes | Yes |
| **Catalog & Medicine Read-Only Search** | Yes | Yes | Yes |
| **Prescription Records (Read / Dispense)** | Yes | Yes | Yes |
| **Medicine & Unit Create/Edit/Delete** | Yes | Yes | No |
| **Batch Expiry & Stock Adjustments** | Yes | Yes | No |
| **Purchases & Supplier Records** | Yes | Yes | No |
| **Customer Returns & Refund Execution**| Yes | Yes | No |
| **Operational Expenses Tracking** | Yes | Yes | No |
| **Standard & Financial Reports** | Yes | Yes | No |
| **Stock Ledger Audit Trail (Premium)** | Yes | Yes | No |
| **User Account Management** | Yes | No | No |
| **System Settings, Logo & Currency** | Yes | No | No |
| **Database Backup & System Restore** | Yes | No | No |
| **License Activation & Diagnostics** | Yes | No | No |
| **Vendor Control Center (If Vendor Key)**| Yes | No | No |

---

## 4. Functional Modules & Business Logic

### 4.1 Point of Sale (POS) & Billing
- **Path:** `app/Http/Controllers/SaleController.php` | **Views:** `resources/views/sales/`
- **Concurrency Control & Atomic Transactions:** Executes within `DB::transaction()` with pessimistic batch locking (`lockForUpdate()`).
- **FEFO Batch Allocation:** Validates against active batches and automatically deducts stock from non-expired inventory.
- **Invoice Numbering:** Automatic generation format `INV-YYYYMMDD-XXXX` with duplicate prevention loop.
- **Multi-Payment Modes:** Cash, Mobile Money (MTN MoMo / Airtel Money), Bank Card, Cheque, Credit/Account.
- **Invoice Rendering:** Provides both standard desktop A4 layouts and 80mm thermal receipt formats for thermal POS printers.

### 4.2 Multi-Unit Packaging & Conversion Engine
- **Models:** `Medicine.php`, `MedicineUnit.php` | **Migrations:** `2026_08_11_100000_create_medicine_units_table.php`
- **Concept:** Every medicine defines a `base_unit` (e.g., *Tablet*, *Capsule*, *Ampoule*, *Piece*).
- **Secondary Units:** Additional units define a `conversion_factor` and optional custom `selling_price`.
  - *Example:* Amoxicillin 500mg
    - Base Unit: `Tablet` (Conversion Factor: 1.0, Base Price: UGX 500)
    - Unit 1: `Strip` (Conversion Factor: 10.0, Selling Price: UGX 4,500)
    - Unit 2: `Box` (Conversion Factor: 100.0, Selling Price: UGX 40,000)
- **Mathematical Deduction Formula:**
  $$\text{Base Units Deducted} = \lceil \text{Sold Quantity} \times \text{Conversion Factor} \rceil$$
  If a customer buys 2 Strips of Amoxicillin, the inventory batch is automatically decremented by $2 \times 10 = 20$ Tablets.

### 4.3 Medicine Catalog & Batch Management
- **Controllers:** `MedicineController.php`, `BatchController.php`
- **Categorization:** Pre-seeded pharmaceutical therapeutic categories (Antibiotics, Analgesics, Antimalarials, Cardiovascular, Dermatological, Pediatric, etc.).
- **Batch Tracking:** Each batch records `batch_number`, `purchase_price`, `selling_price`, `expiry_date`, `quantity`, and `supplier_id`.
- **Reorder Level Monitoring:** Automatically highlights low-stock items when aggregate active batch quantity falls below `reorder_level`.

### 4.4 Stock Adjustments & Loss/Damage Tracking
- **Controller:** `StockAdjustmentController.php` | **Views:** `resources/views/stock/adjustments/`
- **Adjustment Types:**
  - `damage`: Records physical breakage, spills, or contamination.
  - `return`: Records items returned to wholesale supplier.
  - `adjustment`: Corrects physical inventory variance discovered during stock count.
- **Unit Conversion in Adjustments:** Staff can select any registered packaging unit; the system converts to base units before decrementing/incrementing batch quantities.

### 4.5 Sales Returns & Refund Processing
- **Controller:** `SaleReturnController.php` | **Views:** `resources/views/sales/returns/`
- **Validation Engine:** Prevents returning more units than originally purchased by tracking `already_returned_base` quantities per `SaleItem`.
- **Stock Reintegration:** Returned items are immediately re-added to their original inventory batch.
- **Financial Adjustment:** Generates an official Refund Record; updates the daily net sales and profit figures.

### 4.6 Stock Ledger & Full Audit Trail
- **Model:** `StockLedger.php` | **Controller:** `StockLedgerController.php`
- **Feature Flag:** `feature_enabled('stock_ledger')` (Available in **PREMIUM** edition).
- **Double-Entry Style Tracking:** Every movement captures `quantity_before`, `quantity_change`, `quantity_after`, `movement_type`, `user_id`, and polymorphic reference (`reference_type`, `reference_id`).

### 4.7 Prescription Management & Doctor Records
- **Controller:** `PrescriptionController.php` | **Views:** `resources/views/prescriptions/`
- **Workflow:** Records Prescribing Doctor details, Patient/Customer ID, Date, Medication items, Dosage, Frequency, and Duration. Enables one-click transition from verified prescription directly to POS checkout.

### 4.8 Procurement & Supplier Management
- **Controllers:** `PurchaseController.php`, `SupplierController.php`
- **Wholesale Receiving:** When a purchase order invoice is recorded, the system automatically creates or updates the associated medication batches, recording exact supplier acquisition costs.
- **Quick Supplier Creation:** Inline AJAX modal for registering new pharmaceutical distributors during invoice entry.

### 4.9 Expense Tracking & Financial Reconciliation
- **Controllers:** `ExpenseController.php`, `ExpenseCategoryController.php`
- **Categories:** Rent, Utilities, Staff Salaries, NDA & Statutory Licensing, Cold-Chain Maintenance, Logistics, Medical Waste Sanitation.
- **Comprehensive Daily Profit Formula:**
  $$\text{Net Sales} = \text{Gross Sales} - \text{Refunds}$$
  $$\text{Net COGS} = \text{Realized COGS} - \text{Refunded COGS}$$
  $$\text{Net Profit} = \text{Net Sales} - \text{Net COGS} - \text{Daily Operational Expenses}$$

### 4.10 Reports & Business Intelligence
- **Controller:** `ReportController.php` | **Views:** `resources/views/reports/`
- **Report Types:**
  1. **Sales Report:** Filterable by date range, displaying transaction volumes, gross sales, refunds, net revenue, and average transaction size.
  2. **Inventory Valuation Report:** Breakdown of current stock levels, low-stock alerts, purchase valuation (cost), and estimated retail valuation.
  3. **Expiry Risk Report:** Highlights batches expiring within 30, 60, and 90 days.
  4. **Stock Movement Ledger:** Complete chronological timeline of all inventory changes.
- **Export Engine:** Native Excel/XLS report export gated by `advanced_reports` (Premium Edition).

### 4.11 Cryptographic Licensing & Editions
- **Service:** `app/Services/LicenseService.php` | **Config:** `config/editions.php`, `config/license.php`
- **Edition Tiers:**
  - `DEFAULT`: Standard offline point-of-sale, medicine catalog, batches, purchases, basic reports, expenses, backups, multi-unit conversions.
  - `PREMIUM`: All DEFAULT features + Excel/XLS report exports, advanced inventory valuation columns, and the Full Stock Ledger Audit Trail.
- **Cryptographic Signing:** RSA-2048 with SHA-256 digest (`OPENSSL_ALGO_SHA256`).
- **License Types:** `PERPETUAL` (No expiry) or `SUBSCRIPTION` (Time-bound with configurable 7-day grace period).

### 4.12 Vendor Control Center & Management API
- **Controllers:** `VendorPortalController.php`, `PortalApiController.php`
- **Vendor Portal UI:** Accessible strictly on machines holding `storage/keys/private.key` or running in `VENDOR_MODE=true`.
  - Client registry (`PortalClient` with client IDs e.g. `PHC-UG-00001`).
  - Installation fleet tracking (`PortalInstallation`).
  - Release management (`PortalRelease`) for publishing desktop updates.
  - Integrated RSA License Key Generator.
- **Public REST API:**
  - `POST /api/v1/license/activate`: Online activation for client applications.
  - `POST /api/v1/license/verify`: Periodic heartbeat verification check.
  - `GET /api/v1/releases/latest`: Client update checker.

### 4.13 Database Backup & Disaster Recovery
- **Controller:** `BackupController.php` | **Views:** `resources/views/backups/`
- **Dual Formats:**
  - **SQL Dump:** Clean schema + table data statements.
  - **ZIP Archive (DB + Media):** Packages database SQL dump together with `storage/app/public` media assets (custom logos, medicine pictures, expense receipts).
- **Integrity Verification:** Computes SHA-256 checksums (`.sha256` files) for every backup created.
- **Safety Mechanisms:** Automated database snapshot before any restore operation is executed. Custom export path support for USB flash drives and external hard disks.

### 4.14 First-Run Setup Wizard & System Diagnostics
- **Setup Flow:** `SetupController.php`, `SetupService.php`, `app:bootstrap` Artisan command.
  - Generates encryption key, creates local SQLite database, runs migrations, seeds pharmaceutical reference data, and establishes default administrator account (`admin@pharmcare.local`).
- **Diagnostics Screen (`/admin/diagnostics`):**
  - Evaluates SQLite database integrity (`PRAGMA integrity_check`).
  - Reports disk space, database size, permissions, PHP version, installation UUID, and generates an encrypted support token for technical troubleshooting.

---

## 5. Database Schema & Data Dictionary

The PharmCare database comprises 23 relational tables designed for integrity, speed, and auditability.

```
                           +-------------------+
                           |       USERS       |
                           +-------------------+
                                     | 1
                                     |
               +---------------------+---------------------+
               | n                   | n                   | n
      +-----------------+   +-----------------+   +-----------------+
      |      SALES      |   |    PURCHASES    |   |    EXPENSES     |
      +-----------------+   +-----------------+   +-----------------+
         | 1          | 1      | 1          | 1      | n
         |            |        |            |        |
         | n          | n      | n          | n      | 1
+----------------+ +------+ +-----------+ +-------+ +---------------+
|   SALE_ITEMS   | |RETURNS| |PURCH_ITEMS| |SUPPLIER| |EXPENSE_CATEGOR|
+----------------+ +------+ +-----------+ +-------+ +---------------+
         | n          | n      | n
         |            |        |
         | 1          | 1      | 1
+-------------------------------------------------+
|                    BATCHES                      |
+-------------------------------------------------+
                         | n
                         | 1
+-------------------------------------------------+
|                   MEDICINES                     |
+-------------------------------------------------+
      | 1                  | 1               | 1
      | n                  | n               | n
+----------------+ +----------------+ +-------------------+
| MEDICINE_UNITS | |  STOCK_LEDGERS | |PRESCRIPTION_ITEMS |
+----------------+ +----------------+ +-------------------+
```

### Key Relational Entities Summary

1. **`users`**: Authentication credentials, roles (`admin`, `pharmacist`, `cashier`), phone numbers, active status.
2. **`categories`**: Medication therapeutic classifications (name, slug, description).
3. **`suppliers`**: Wholesale drug distributors and manufacturers (name, contact person, phone, email, address).
4. **`medicines`**: Master medication registry (name, generic name, category ID, manufacturer, base unit, prescription requirement, image path, reorder level).
5. **`medicine_units`**: Secondary packaging units linked to a medicine (unit name, conversion factor relative to base unit, custom selling price).
6. **`batches`**: Physical inventory batches (batch number, medicine ID, supplier ID, quantity, purchase price, selling price, expiry date, active flag).
7. **`customers`**: Patient and customer profiles (name, phone, email, address).
8. **`sales`**: Sales invoices (invoice number, customer ID, user ID, subtotal, tax, discount, total amount, payment method, payment status).
9. **`sale_items`**: Line items of a sale (sale ID, medicine ID, batch ID, base quantity, unit name, unit quantity, unit price, total).
10. **`sale_returns`**: Refund records (sale ID, sale item ID, medicine ID, batch ID, user ID, return unit name, return unit quantity, base quantity returned, refund amount, reason).
11. **`purchases`**: Procurement purchase orders (invoice number, supplier ID, user ID, purchase date, total amount, status).
12. **`purchase_items`**: Line items of a purchase order (purchase ID, medicine ID, batch ID, quantity, unit price, total).
13. **`prescriptions`**: Doctor prescription records (customer ID, doctor name, doctor phone, prescription date, status, notes).
14. **`prescription_items`**: Items on a prescription (prescription ID, medicine ID, dosage, frequency, duration, instructions).
15. **`expenses`**: Operational expenditures (expense category ID, user ID, title, amount, expense date, payment method, receipt image path, notes).
16. **`expense_categories`**: Classification for operational expenses (name, description).
17. **`stock_ledgers`**: Double-entry inventory audit log (medicine ID, batch ID, movement type, quantity change, quantity before, quantity after, packaging unit details, polymorphic reference type and ID, user ID, notes).
18. **`settings`**: Dynamic key-value system configuration (pharmacy name, currency symbol, logo, phone, address, tax rate).
19. **`licenses`**: Cryptographic license registry (license key, business name, edition, activated modules JSON, issue date, expiry date, signature, raw payload).
20. **`portal_clients`**: Vendor portal client registry (client ID `PHC-UG-XXXXX`, pharmacy name, owner name, contact, location).
21. **`portal_installations`**: Registered desktop workstation instances (installation UUID, client ID, license key, app version, hostname, OS info, last verified heartbeat).
22. **`portal_releases`**: Published desktop updates (version, release date, download URL, release notes, migration flags).
23. **`activity_logs`**: System audit log recording user actions, models, timestamps, IP addresses, and descriptions.

---

## 6. API & Routing Reference

### Web Application Routes (Staff Interface)

| HTTP Method | URI | Controller Action | Middleware / Guard | Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | Closure | None | Redirects to Setup or Login |
| `GET` | `/setup` | `SetupController@index` | `guest` | First-run setup wizard UI |
| `POST` | `/setup` | `SetupController@store` | `guest` | Process first-run configuration |
| `GET` | `/dashboard` | `DashboardController@index` | `auth, verified, setup` | Main Analytics Dashboard |
| `GET` | `/dashboard/live` | `DashboardController@liveData`| `auth, verified, setup` | Real-time JSON metrics for polling |
| `GET` | `/sales` | `SaleController@index` | `auth, verified, setup` | Sales transaction list |
| `GET` | `/sales/create` | `SaleController@create` | `auth, verified, setup` | Point of Sale (POS) checkout interface |
| `POST` | `/sales` | `SaleController@store` | `auth, verified, setup` | Submit sale (supports AJAX / JSON) |
| `GET` | `/sales/{sale}` | `SaleController@show` | `auth, verified, setup` | View invoice details |
| `GET` | `/sales/{sale}/invoice` | `SaleController@invoice` | `auth, verified, setup` | Printable receipt / invoice |
| `GET` | `/sales/returns` | `SaleReturnController@index` | `role:admin,pharmacist` | List customer returns |
| `GET` | `/sales/{sale}/return` | `SaleReturnController@create` | `role:admin,pharmacist` | Create return for invoice |
| `POST` | `/sales/returns` | `SaleReturnController@store` | `role:admin,pharmacist` | Process and credit return |
| `RESOURCE` | `/customers` | `CustomerController` | `auth, verified, setup` | Customer management CRUD |
| `GET` | `/medicines` | `MedicineController@index` | `auth, verified, setup` | Medicine catalog |
| `GET` | `/medicines/create` | `MedicineController@create` | `role:admin,pharmacist` | Add new medicine |
| `POST` | `/medicines` | `MedicineController@store` | `role:admin,pharmacist` | Store medicine with units |
| `RESOURCE` | `/medicines.batches` | `BatchController` | `role:admin,pharmacist` | Nested batch management |
| `GET` | `/stock/adjustments` | `StockAdjustmentController@index`| `role:admin,pharmacist` | Stock adjustments list |
| `POST` | `/stock/adjustments` | `StockAdjustmentController@store`| `role:admin,pharmacist` | Record damage/loss adjustment |
| `RESOURCE` | `/purchases` | `PurchaseController` | `role:admin,pharmacist` | Wholesale purchases |
| `RESOURCE` | `/expenses` | `ExpenseController` | `role:admin,pharmacist` | Operational expenses |
| `GET` | `/reports/sales` | `ReportController@sales` | `role:admin,pharmacist` | Sales reporting & Excel export |
| `GET` | `/reports/inventory`| `ReportController@inventory` | `role:admin,pharmacist` | Inventory valuation report |
| `GET` | `/reports/expiry` | `ReportController@expiry` | `role:admin,pharmacist` | Expiry tracking report |
| `GET` | `/reports/ledger` | `StockLedgerController@index`| `role:admin,pharmacist` | Stock Ledger audit trail |
| `RESOURCE` | `/users` | `UserController` | `role:admin` | Staff account management |
| `GET/POST`| `/settings` | `SettingController` | `role:admin` | System preferences & branding |
| `GET/POST`| `/settings/license`| `LicenseController` | `role:admin` | License view & activation |
| `GET` | `/admin/diagnostics`| `DiagnosticsController@index`| `role:admin` | System health diagnostics |
| `RESOURCE` | `/backups` | `BackupController` | `role:admin` | Backup & restore management |

### Public REST API (Vendor Management Endpoints)

| HTTP Method | URI | Description | Payload Parameters |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/license/activate` | Remote license activation | `license_key`, `installation_id`, `hostname`, `os` |
| `POST` | `/api/v1/license/verify` | Periodic heartbeat check | `license_key`, `installation_id` |
| `GET` | `/api/v1/releases/latest` | Retrieve latest release info | Query headers: `X-App-Version` |

---

## 7. Cryptographic Security & Licensing Engine

### Key Structure & Storage
- **Public Key (`storage/keys/public.key`):** Shipped with the desktop application installer. Used to verify that any supplied license JSON payload was generated and signed by the authorized vendor.
- **Private Key (`storage/keys/private.key`):** Exclusively retained on the vendor build workstation / vendor portal. **Never shipped in the client distribution package.**

### License Payload Schema
A valid PharmCare license JSON file contains:
```json
{
  "license_id": "PHC-2026-PREM-78421",
  "business_name": "Kampala Care Pharmacy",
  "business_id": "PHC-UG-00001",
  "edition": "PREMIUM",
  "license_type": "PERPETUAL",
  "issue_date": "2026-08-19",
  "expiry_date": null,
  "max_terminals": 5,
  "grace_days": 7,
  "activated_modules": [
    "pos", "medicines", "unit_packaging", "inventory", 
    "batches", "sales", "purchases", "expenses", 
    "reports", "advanced_reports", "advanced_inventory", 
    "stock_ledger", "backup", "settings"
  ],
  "installation_identity": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
  "signature": "dGVzdC1zaWduYXR1cmUtYmFzZTY0LWVuY29kZWQtZGF0YQ=="
}
```

### Verification Algorithm
```
1. Client extracts raw JSON payload and isolates "signature" field.
2. Canonical data string is reconstructed from normalized payload keys.
3. openssl_verify($canonicalData, base64_decode($signature), $publicKeyPem, OPENSSL_ALGO_SHA256) is executed.
4. If signature is valid:
     - Check installation_identity against machine installation UUID.
     - Check expiry_date against system clock.
     - If expired within grace period, flag GRACE mode.
     - If valid, activate edition entitlements in memory & SQLite database.
5. If invalid: Reject activation with cryptographic tampering alert.
```

---

## 8. Installation, Packaging & Deployment Guide

### 8.1 Windows Standalone Desktop Installation
1. **Prerequisites:** Windows 10 or Windows 11 (64-bit).
2. **Installation:**
   - Execute `PharmCare_Setup_v2.2.0.exe`.
   - The installer unpacks files to `C:\PharmCare` and places a shortcut on the Desktop.
3. **Application Startup:**
   - Launch via the Desktop shortcut or `start_pharmcare.bat`.
   - The orchestrator checks `%APPDATA%\PharmCare`, verifies port 8000 availability, runs SQLite migrations, starts the PHP background server silently via VBScript, and opens the default browser to `http://127.0.0.1:8000`.
4. **First-Time Wizard:**
   - Default login: `admin@pharmcare.local` / `admin123`.
   - Enter your pharmacy business profile, address, phone number, and establish your permanent administrator credentials.

### 8.2 Development Setup (XAMPP / Composer)
```bash
# 1. Clone repository into local webroot
cd c:\xampp\htdocs\pham-care

# 2. Install PHP dependencies
composer install

# 3. Configure environment
copy .env.example .env
php artisan key:generate

# 4. Run migrations and comprehensive seeders
php artisan migrate --seed

# 5. Compile frontend assets
npm install
npm run build

# 6. Start local development server
php artisan serve
```

### 8.3 Cloud & Docker Deployment (Render / Linux VPS)
The project includes a production-ready `Dockerfile` and `docker-entrypoint.sh`:
```bash
# Build Docker image
docker build -t pharmcare:latest .

# Run container with persistent storage
docker run -d \
  -p 10000:10000 \
  -e PORT=10000 \
  -e VENDOR_MODE=true \
  -e ADMIN_EMAIL=admin@pharmcare.ug \
  -e ADMIN_PASSWORD=StrongAdminPassword \
  -v /var/data/pharmcare:/var/www/html/storage/app_data \
  --name pharmcare-app \
  pharmcare:latest
```

---

## 9. Disaster Recovery, Maintenance & Troubleshooting

### Database Backup Protocol
- Backups should be generated daily via **Settings → Database Backups**.
- Choosing **ZIP (DB + Media)** includes all uploaded logos, medicine pictures, and expense receipts.
- Backups can be exported directly to an external USB flash drive by specifying the external directory in the custom export path field.

### System Recovery Procedure
1. Navigate to **Settings → Database Backups**.
2. Click **Restore** next to any existing backup, or click **Upload & Restore Backup** to upload a previously exported `.sql` or `.zip` file.
3. The system creates a safety snapshot of the active database before replacing the current SQLite file with the backup contents.
4. If an unexpected error occurs during restore, the safety snapshot is automatically restored.

### Common Diagnostics & Troubleshooting

| Symptom | Probable Cause | Resolution |
| :--- | :--- | :--- |
| **Port 8000 already in use** | Stale background PHP process | `start_pharmcare.bat` automatically terminates lingering port 8000 processes on startup. Alternatively, run `taskkill /F /IM php.exe` in Command Prompt. |
| **Database locked error (`database is locked`)** | Simultaneous long write operations in SQLite | Concurrency is managed via WAL mode and transactional locking. Ensure transactions commit promptly. |
| **Images or Logo not loading in standalone mode** | Missing symbolic link in Windows | PharmCare includes a dynamic media streaming fallback route (`/media/{path}`) that serves assets directly from storage without requiring symlinks. |
| **License shows EXPIRED or GRACE** | Subscription validity date passed | Renew subscription license via **Settings → License & Edition** by importing a newly signed vendor license JSON payload. |
| **Corrupted SQLite database file** | Sudden workstation power loss | Open **Admin → System Diagnostics** to inspect `PRAGMA integrity_check`. Restore from the latest automated `.sql` or `.zip` backup. |

---
*PharmCare Software Solutions © 2026. All rights reserved.*
