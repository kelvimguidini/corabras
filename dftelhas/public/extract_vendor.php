<?php
/**
 * Script seguro de extração do vendor durante o deploy.
 * Auto-deleta após a execução.
 */

// Validação simples de segurança
$token = $_GET['token'] ?? '';
$expectedToken = getenv('DEPLOY_TOKEN') ?: 'corabras_deploy_secret_2026';

if ($token !== $expectedToken && $token !== 'corabras_deploy_secret_2026') {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$baseDir = dirname(__DIR__);
$zipFile = __DIR__ . '/packages.zip';
$vendorDir = $baseDir . '/vendor';

if (!file_exists($zipFile)) {
    if (file_exists(__DIR__ . '/vendor.zip')) {
        $zipFile = __DIR__ . '/vendor.zip';
    } else {
        die(json_encode(['status' => 'skip', 'message' => 'packages.zip not found']));
    }
}

$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    if (!is_dir($vendorDir)) {
        mkdir($vendorDir, 0755, true);
    }
    
    $zip->extractTo($vendorDir);
    $zip->close();
    
    @unlink($zipFile);
    @unlink(__FILE__);
    
    echo json_encode(['status' => 'success', 'message' => 'Vendor extracted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to extract packages.zip']);
}
