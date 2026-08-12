<?php

/**
 * PHARMCARE VENDOR LICENSE GENERATOR WEB GUI INTERFACE
 * 
 * Access this script in your browser to generate, preview, sign, and download
 * offline RSA 2048-bit digitally signed license key files for buyers.
 */

require_once __DIR__ . '/generate_license.php';

// Handle POST Download Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'download') {
    $businessName = trim($_POST['business_name'] ?? 'Buyer Pharmacy');
    $businessId = trim($_POST['business_id'] ?? ('BUS-' . mt_rand(1000, 9999)));
    $edition = strtoupper(trim($_POST['edition'] ?? 'PREMIUM'));
    $licenseType = strtoupper(trim($_POST['license_type'] ?? 'PERPETUAL'));
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $selectedModules = $_POST['modules'] ?? [
        'unit_packaging', 'stock_ledger', 'medicine_images', 'advanced_inventory',
        'fefo', 'stock_ageing', 'slow_moving', 'advanced_purchasing', 'supplier_analysis',
        'advanced_credit', 'prescription', 'cashier_sessions', 'advanced_reports',
        'approval_workflows', 'advanced_backup'
    ];

    $licenseData = createSignedLicense([
        'business_name' => $businessName,
        'business_id' => $businessId,
        'edition' => $edition,
        'license_type' => $licenseType,
        'expiry_date' => $expiryDate,
        'activated_modules' => $selectedModules,
    ], $privateKeyPem, null);

    $json = json_encode($licenseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $filename = 'pharmcare_license_' . preg_replace('/[^a-z0-9]/i', '_', strtolower($businessName)) . '_' . strtolower($edition) . '.json';

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($json));
    echo $json;
    exit;
}

$allAvailableModules = [
    'unit_packaging' => 'Multi-Unit Packaging & Conversion',
    'stock_ledger' => 'Stock Audit Ledger & Movement Tracking',
    'medicine_images' => 'Medicine Image Support & Previews',
    'advanced_inventory' => 'Advanced Inventory Analytics',
    'fefo' => 'First-Expired First-Out (FEFO) Batches',
    'stock_ageing' => 'Stock Ageing Reports',
    'slow_moving' => 'Slow-Moving Stock Detection',
    'advanced_purchasing' => 'Advanced Supplier Purchasing',
    'supplier_analysis' => 'Supplier Performance Analysis',
    'advanced_credit' => 'Customer Credit Limits & Ledger',
    'prescription' => 'Prescription Management',
    'cashier_sessions' => 'Cashier POS Shift Sessions',
    'advanced_reports' => 'Advanced Analytical Reports',
    'approval_workflows' => 'Purchase & Credit Approval Workflows',
    'advanced_backup' => 'Enhanced Media & SQL ZIP Backups'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmCare — Vendor License Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen p-6 md:p-12 flex items-center justify-center">

    <div class="max-w-4xl w-full bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-8 flex items-center justify-between text-white">
            <div class="space-y-1">
                <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-widest text-emerald-200">
                    <span>🔐 Vendor Off-Line Tool</span>
                    <span>•</span>
                    <span>RSA 2048-Bit RSA Security</span>
                </div>
                <h1 class="text-3xl font-extrabold">PharmCare License Generator</h1>
                <p class="text-xs text-emerald-100 opacity-90">Issue digitally signed offline licenses for pharmacy buyers.</p>
            </div>
            <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center text-3xl shrink-0 backdrop-blur-sm">
                🛡️
            </div>
        </div>

        <form method="POST" class="p-8 space-y-8">
            <input type="hidden" name="action" value="download">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Business Name -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Buyer / Pharmacy Business Name</label>
                    <input type="text" name="business_name" required value="St. Luke Pharmacy & Drug Shop" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-white outline-none">
                </div>

                <!-- Business ID -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Business Registration ID</label>
                    <input type="text" name="business_id" required value="BUS-<?php echo mt_rand(1000, 9999); ?>" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-white outline-none font-mono">
                </div>

                <!-- Edition Selection -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block">PharmCare Software Edition</label>
                    <select name="edition" id="editionSelect" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-emerald-400 font-bold outline-none">
                        <option value="PREMIUM" selected>⭐ PREMIUM EDITION (All Features Unlocked)</option>
                        <option value="DEFAULT">📦 DEFAULT EDITION (Standard Core Features)</option>
                    </select>
                </div>

                <!-- License Type -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block">License Type</label>
                    <select name="license_type" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-white outline-none">
                        <option value="PERPETUAL" selected>♾️ PERPETUAL (Lifetime Access)</option>
                        <option value="ANNUAL">📅 ANNUAL (1 Year Subscription)</option>
                        <option value="TRIAL">⏳ TRIAL (30-Day Evaluation)</option>
                    </select>
                </div>

                <!-- Expiry Date -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Expiration Date (Optional for Perpetual)</label>
                    <input type="date" name="expiry_date" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-white outline-none">
                </div>
            </div>

            <!-- Activated Modules Grid -->
            <div class="space-y-3 pt-4 border-t border-slate-800">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Activated Feature Modules</label>
                    <button type="button" onclick="selectAllModules(true)" class="text-xs font-bold text-emerald-400 hover:underline">Select All</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($allAvailableModules as $key => $label): ?>
                        <label class="flex items-center gap-3 p-3 bg-slate-800/60 border border-slate-800 hover:border-slate-700 rounded-xl cursor-pointer transition">
                            <input type="checkbox" name="modules[]" value="<?php echo $key; ?>" checked class="w-4 h-4 text-emerald-500 rounded border-slate-700 bg-slate-900 focus:ring-emerald-500">
                            <span class="text-xs font-semibold text-slate-200"><?php echo $label; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Action Button -->
            <div class="pt-6 border-t border-slate-800 flex items-center justify-end gap-4">
                <button type="submit" class="w-full md:w-auto px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-extrabold text-sm rounded-2xl shadow-lg hover:shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                    <span>⚡ Generate & Download License File (.json)</span>
                </button>
            </div>
        </form>

        <div class="p-6 bg-slate-950 text-center text-xs text-slate-500 border-t border-slate-800">
            Internal Vendor Cryptographic Generator • Keep <code class="text-emerald-400">vendor-tools/keys/private.key</code> strictly secure.
        </div>

    </div>

    <script>
        function selectAllModules(checked) {
            document.querySelectorAll('input[name="modules[]"]').forEach(cb => cb.checked = checked);
        }
    </script>
</body>
</html>
