<?php

/**
 * PHARMCARE VENDOR LICENSE GENERATOR TOOL
 * 
 * NOTE: This tool is strictly for vendor deployment/licensing use.
 * It uses RSA 2048-bit asymmetric cryptography to sign licenses offline.
 * The private key must NEVER be included in customer releases.
 */

$vendorKeyDir = __DIR__ . '/keys';
$appPublicKeyDir = dirname(__DIR__) . '/storage/keys';

if (!file_exists($vendorKeyDir)) {
    mkdir($vendorKeyDir, 0755, true);
}
if (!file_exists($appPublicKeyDir)) {
    mkdir($appPublicKeyDir, 0755, true);
}

$privateKeyFile = $vendorKeyDir . '/private.key';
$publicKeyFile = $appPublicKeyDir . '/public.key';

// Locate openssl.cnf on Windows/XAMPP if needed
function getOpenSslConfigPath(): ?string {
    $possiblePaths = [
        getenv('OPENSSL_CONF'),
        'C:/xampp/php/extras/ssl/openssl.cnf',
        'C:/xampp/apache/bin/openssl.cnf',
        '/etc/ssl/openssl.cnf',
        '/etc/pki/tls/openssl.cnf',
    ];
    foreach ($possiblePaths as $path) {
        if ($path && file_exists($path)) {
            return $path;
        }
    }
    return null;
}

// 1. Generate Key Pair if missing
if (!file_exists($privateKeyFile) || !file_exists($publicKeyFile)) {
    echo "Generating new vendor RSA 2048 keypair...\n";
    
    $configPath = getOpenSslConfigPath();
    $options = [
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    if ($configPath) {
        $options["config"] = $configPath;
    }

    $res = openssl_pkey_new($options);
    
    if ($res) {
        $exportOpts = $configPath ? ["config" => $configPath] : [];
        openssl_pkey_export($res, $privateKeyPem, null, $exportOpts);
        $keyDetails = openssl_pkey_get_details($res);
        $publicKeyPem = $keyDetails['key'];
    } else {
        // Fallback using OpenSSL CLI if extension fails finding openssl.cnf
        exec("openssl genrsa -out " . escapeshellarg($privateKeyFile) . " 2048");
        exec("openssl rsa -in " . escapeshellarg($privateKeyFile) . " -pubout -out " . escapeshellarg($publicKeyFile));
        $privateKeyPem = file_get_contents($privateKeyFile);
        $publicKeyPem = file_get_contents($publicKeyFile);
    }

    if ($privateKeyPem && $publicKeyPem) {
        file_put_contents($privateKeyFile, $privateKeyPem);
        file_put_contents($publicKeyFile, $publicKeyPem);
        echo "Keys generated successfully!\n";
        echo "Private key saved to: {$privateKeyFile}\n";
        echo "Public key saved to:  {$publicKeyFile}\n\n";
    } else {
        die("Fatal: Failed to generate RSA key pair.\n");
    }
} else {
    $privateKeyPem = file_get_contents($privateKeyFile);
    $publicKeyPem = file_get_contents($publicKeyFile);
}

// Helper to canonicalize data for signature calculation
function canonicalPayload(array $payload): string {
    unset($payload['signature']);
    ksort($payload);
    return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function createSignedLicense(array $details, string $privateKeyPem, string $outputFile): array {
    $payload = [
        'license_id' => $details['license_id'] ?? ('PC-UG-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT)),
        'business_name' => $details['business_name'] ?? 'ABC Drug Shop',
        'business_id' => $details['business_id'] ?? ('BUS-' . mt_rand(1000, 9999)),
        'edition' => strtoupper($details['edition'] ?? 'PREMIUM'), // DEFAULT or PREMIUM
        'activated_modules' => $details['activated_modules'] ?? [
            'unit_packaging',
            'stock_ledger',
            'medicine_images',
            'advanced_inventory',
            'fefo',
            'stock_ageing',
            'slow_moving',
            'advanced_purchasing',
            'supplier_analysis',
            'advanced_credit',
            'prescription',
            'cashier_sessions',
            'advanced_reports',
            'approval_workflows',
            'advanced_backup'
        ],
        'issue_date' => $details['issue_date'] ?? date('Y-m-d'),
        'expiry_date' => $details['expiry_date'] ?? null,
        'license_type' => strtoupper($details['license_type'] ?? 'PERPETUAL'),
        'installation_limit' => $details['installation_limit'] ?? 1,
        'status' => 'ACTIVE',
    ];

    $canonicalStr = canonicalPayload($payload);
    $configPath = getOpenSslConfigPath();
    $keyRes = openssl_pkey_get_private($privateKeyPem);
    openssl_sign($canonicalStr, $signature, $keyRes, OPENSSL_ALGO_SHA256);
    $payload['signature'] = base64_encode($signature);

    $jsonOutput = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($outputFile) {
        file_put_contents($outputFile, $jsonOutput);
    }
    return $payload;
}

// Command Line execution or script invocation
$edition = $argv[1] ?? 'PREMIUM';
$business = $argv[2] ?? 'ABC Drug Shop';
$outPath = $argv[3] ?? (__DIR__ . '/sample_license.json');

$license = createSignedLicense([
    'edition' => $edition,
    'business_name' => $business,
], $privateKeyPem, $outPath);

echo "Successfully generated signed license for {$business} ({$edition} EDITION).\n";
echo "License File saved to: {$outPath}\n";
