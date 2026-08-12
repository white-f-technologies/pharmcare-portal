<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pharm-care', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Users
$pdo->exec("INSERT Iadmin@pharmcare.testNTO users (name, email, password, role, phone, is_active, created_at, updated_at) VALUES
('Admin', '', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'admin', '09171234567', 1, NOW(), NOW()),
('Pharmacist', 'pharmacist@pharmcare.test', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'pharmacist', '09171234568', 1, NOW(), NOW()),
('Cashier', 'cashier@pharmcare.test', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'cashier', '09171234569', 1, NOW(), NOW())");

// Categories
$pdo->exec("INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES
('Painkillers', 'painkillers', 'Medications for pain relief', NOW(), NOW()),
('Antibiotics', 'antibiotics', 'Medications for bacterial infections', NOW(), NOW()),
('Vitamins & Supplements', 'vitamins-supplements', 'Nutritional supplements', NOW(), NOW()),
('Cough & Cold', 'cough-cold', 'Medications for cough and cold symptoms', NOW(), NOW()),
('Allergy', 'allergy', 'Antihistamines and allergy relief', NOW(), NOW()),
('Diabetes', 'diabetes', 'Diabetes management medications', NOW(), NOW())");

// Customers
$pdo->exec("INSERT INTO customers (name, email, phone, address, is_active, created_at, updated_at) VALUES
('Walk-in Customer', NULL, '00000000000', NULL, 1, NOW(), NOW()),
('Juan Dela Cruz', 'juan@example.com', '09175551234', '123 Rizal St., Manila', 1, NOW(), NOW())");

// Suppliers
$pdo->exec("INSERT INTO suppliers (name, email, phone, company, is_active, created_at, updated_at) VALUES
('PharmaMed Distributors', 'info@pharmamed.com', '0281234567', 'PharmaMed Corp', 1, NOW(), NOW()),
('HealthFirst Trading', NULL, '0287654321', 'HealthFirst Inc.', 1, NOW(), NOW()),
('MedSupply Co.', 'orders@medsupply.com', '0299998888', 'MedSupply Corp', 1, NOW(), NOW())");

// Medicines
$pdo->exec("INSERT INTO medicines (name, generic_name, category_id, manufacturer, reorder_level, requires_prescription, created_at, updated_at) VALUES
('Biogesic', 'Paracetamol', 1, 'United Laboratories', 20, 0, NOW(), NOW()),
('Amoxicillin', 'Amoxicillin', 2, 'United Laboratories', 15, 1, NOW(), NOW()),
('Neozep', 'Phenylephrine + Chlorphenamine', 4, 'United Laboratories', 30, 0, NOW(), NOW()),
('Enervon', 'Multivitamins + Minerals', 3, 'United Laboratories', 25, 0, NOW(), NOW()),
('Cetirizine', 'Cetirizine HCl', 5, 'Various', 20, 0, NOW(), NOW()),
('Metformin', 'Metformin HCl', 6, 'Various', 15, 1, NOW(), NOW()),
('Mefenamic Acid', 'Mefenamic Acid', 1, 'Various', 20, 0, NOW(), NOW()),
('Salbutamol', 'Salbutamol Sulfate', 4, 'Various', 10, 1, NOW(), NOW()),
('Vitamin C', 'Ascorbic Acid', 3, 'Various', 50, 0, NOW(), NOW()),
('Ibuprofen', 'Ibuprofen', 1, 'Various', 20, 0, NOW(), NOW())");

// Batches
$pdo->exec("INSERT INTO batches (medicine_id, batch_number, supplier_id, expiry_date, purchase_price, selling_price, quantity, created_at, updated_at) VALUES
(1, 'BG-2026-001', 1, '2027-06-01', 5.50, 8.00, 100, NOW(), NOW()),
(1, 'BG-2026-002', 1, '2027-08-01', 5.75, 8.50, 50, NOW(), NOW()),
(2, 'AMX-2026-001', 2, '2026-12-01', 12.00, 18.00, 75, NOW(), NOW()),
(3, 'NZ-2026-001', 1, '2027-03-01', 4.00, 6.50, 200, NOW(), NOW()),
(4, 'ENV-2026-001', 3, '2027-09-01', 15.00, 22.00, 60, NOW(), NOW()),
(5, 'CTZ-2026-001', 2, '2026-11-15', 3.50, 5.00, 120, NOW(), NOW()),
(9, 'VTC-2026-001', 3, '2027-12-01', 2.50, 4.00, 500, NOW(), NOW())");

echo "Database seeded successfully!\n";
echo "Admin login: admin@pharmcare.test / password\n";
