# Konfigurasi Environment untuk Bapenda API

## Development Environment (.env)

```env
# Bapenda API Configuration
BAPENDA_API_URL=http://10.15.36.91:7071/dpnol_data_assets
BAPENDA_CLIENT_ID=1001
BAPENDA_USERNAME=samawa
BAPENDA_SECRET_KEY=
BAPENDA_TIMEOUT=15
BAPENDA_MOCK_MODE=true

# Logging
BAPENDA_LOG_REQUESTS=true
BAPENDA_RETRY_ATTEMPTS=3
BAPENDA_RETRY_DELAY=5
```

## Production Environment (.env.production)

```env
# Bapenda API Configuration
BAPENDA_API_URL=http://10.15.36.91:7071/dpnol_data_assets
BAPENDA_CLIENT_ID=1001
BAPENDA_USERNAME=samawa
BAPENDA_SECRET_KEY=your_secret_key_here
BAPENDA_TIMEOUT=30
BAPENDA_MOCK_MODE=false

# Logging
BAPENDA_LOG_REQUESTS=true
BAPENDA_RETRY_ATTEMPTS=3
BAPENDA_RETRY_DELAY=5
```

## Commands untuk Testing

### Development dengan Mock Mode

```bash
# Enable mock mode
php artisan bapenda:mock enable

# Test dengan data mock
php artisan bapenda:update --nik=1304081010940006

# Debug comprehensive
php artisan bapenda:debug
```

### Production Testing

```bash
# Disable mock mode
php artisan bapenda:mock disable

# Test koneksi production
php artisan bapenda:test-production

# Test dengan timeout custom
php artisan bapenda:test-production --timeout=10

# Test dengan URL custom (jika ada)
php artisan bapenda:test-production --url=https://api-external.bapenda.go.id/dpnol_data_assets
```

### API Testing

```bash
# Test koneksi dasar
php artisan bapenda:test-connection

# Debug konfigurasi
php artisan bapenda:debug --check-config

# Test dengan NIK spesifik
php artisan bapenda:debug --test-nik=1304081010940006
```

## Troubleshooting Network Issues

### 1. Internal Network (IP 10.15.36.91)

Jika API berada di internal network:

-   Pastikan berada di network yang sama
-   Gunakan VPN jika diperlukan
-   Minta endpoint external/public

### 2. Firewall/Port Issues

```bash
# Test koneksi manual
telnet 10.15.36.91 7071

# Test dengan curl
curl -v "http://10.15.36.91:7071/dpnol_data_assets?nik=1234567890123456"
```

### 3. Development Workaround

```bash
# Gunakan mock mode untuk development
php artisan bapenda:mock enable

# Test semua fungsi dengan data mock
php artisan bapenda:update --nik=1304081010940006
```

## Implementation Notes

### cURL vs HTTP Client

Service menggunakan cURL sebagai primary method (match original PHP), dengan HTTP client sebagai fallback:

1. **cURL Primary**: Exact match dengan implementasi PHP asli
2. **HTTP Client Fallback**: Jika cURL gagal
3. **Mock Mode**: Untuk development tanpa akses API

### Headers Format

Headers disesuaikan dengan format PHP asli:

```
Content-Type: application/json
Date: 2025-10-04 12:00:00
Accept: application/json
version: 1.0.0
clientid: 1001
Authorization: DA01 samawa:signature_hash
```

### Signature Generation

```php
$stringToSign = $date . $client_id . $username;
$signature = hash('sha256', $stringToSign);
// atau jika ada secret key:
$signature = hash_hmac('sha256', $stringToSign, $secret_key);
```
