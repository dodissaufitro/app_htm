<?php
// Simulasi get_header.php untuk referensi
// File ini dibuat untuk referensi implementasi signature yang benar

$date = date('Y-m-d H:i:s');
$client_id = '1001';
$username = 'samawa';

// Generate signature sesuai dengan algoritma yang benar
// Implementasi ini harus disesuaikan dengan algoritma asli dari get_header.php
$signatureString = hash('sha256', $date . $client_id . $username);

// Headers yang benar sesuai dengan implementasi PHP asli
$headers = [
    'Content-Type: application/json',
    'Date: ' . $date,
    'Accept: application/json',
    'version: 1.0.0',
    'clientid: ' . $client_id,
    'Authorization: DA01 ' . $username . ':' . $signatureString,
];

// URL format
$url = "http://10.15.36.91:7071/dpnol_data_assets?nik=" . $nik; // No URL encoding

return [
    'date' => $date,
    'signature' => $signatureString,
    'headers' => $headers,
    'url_format' => $url
];
