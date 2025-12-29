# API Mobile App - Lubung Data SAE

API REST untuk aplikasi mobile yang memungkinkan akses ke data panen dan pengiriman dalam database Lubung Data SAE.

## 🚀 Fitur Utama

- **Authentication JWT** - Login aman dengan token yang expire otomatis
- **Data Panen** - Akses lengkap data panen dengan filtering dan sorting
- **Data Pengiriman** - Akses lengkap data pengiriman dengan filtering dan sorting
- **Statistik & Analytics** - Ringkasan dan statistik data
- **Pagination** - Dukungan pagination untuk performa optimal
- **CORS Support** - Siap untuk aplikasi mobile dan web

## 📁 Struktur API

```
api/
├── BaseAPI.php          # Base class dengan fungsi umum
├── auth.php             # Endpoint authentication
├── panen.php            # Endpoint data panen
├── pengiriman.php       # Endpoint data pengiriman
├── .htaccess            # URL rewriting rules
└── README.md            # Documentation ini
```

## 🛠️ Setup dan Instalasi

### 1. Requirements
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache dengan mod_rewrite enabled
- Database `lubung_data_sae` sudah ter-setup

### 2. Konfigurasi Apache
Pastikan mod_rewrite sudah enabled di Apache. File `.htaccess` sudah disediakan untuk URL rewriting.

### 3. Konfigurasi Database
API menggunakan konfigurasi database yang sama dari file `config/database.php`. Pastikan database sudah ter-setup dengan menjalankan `database_setup.sql`.

### 4. Security Setup
⚠️ **PENTING**: Ganti secret key JWT di file `BaseAPI.php` pada baris:
```php
$secret = "your-secret-key-here-change-this-in-production";
```

Gunakan secret key yang kuat dan unik untuk production.

## 📊 Endpoint API

### Base URL
```
http://your-domain.com/api/
```

### Authentication
```
POST /api/auth/login          - Login dan dapatkan token
GET  /api/auth/verify         - Verifikasi token
POST /api/auth/refresh        - Refresh token
```

### Data Panen
```
GET  /api/panen               - List data panen (dengan filtering)
GET  /api/panen/{id}          - Data panen berdasarkan ID
GET  /api/panen/statistics    - Statistik data panen
GET  /api/panen/summary       - Ringkasan data panen
```

### Data Pengiriman
```
GET  /api/pengiriman          - List data pengiriman (dengan filtering)
GET  /api/pengiriman/{id}     - Data pengiriman berdasarkan ID
GET  /api/pengiriman/statistics - Statistik data pengiriman
GET  /api/pengiriman/summary  - Ringkasan data pengiriman
```

## 🔐 Authentication

### Login
```bash
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

### Menggunakan Token
Setelah login, gunakan token di header Authorization:
```bash
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://your-domain.com/api/panen
```

## 📋 Filter dan Parameter

### Parameter Umum
- `page` - Nomor halaman (default: 1)
- `limit` - Jumlah item per halaman (default: 20, max: 100)
- `sort_by` - Field untuk sorting
- `sort_direction` - ASC atau DESC (default: DESC)
- `date_from` - Filter tanggal mulai (YYYY-MM-DD)
- `date_to` - Filter tanggal akhir (YYYY-MM-DD)
- `afdeling` - Filter afdeling (partial match)

### Filter Data Panen
- `blok` - Filter blok
- `pemanen` - Filter nama/NIK pemanen
- `kerani` - Filter nama kerani
- `min_janjang` - Minimum jumlah janjang
- `max_janjang` - Maximum jumlah janjang

### Filter Data Pengiriman
- `blok` - Filter blok
- `nopol` - Filter nomor polisi/kendaraan
- `kerani` - Filter nama/NIK kerani
- `min_janjang` - Minimum jumlah janjang
- `max_janjang` - Maximum jumlah janjang
- `min_kg` - Minimum berat (kg)
- `max_kg` - Maximum berat (kg)
- `tipe_aplikasi` - Filter tipe aplikasi

## 📱 Contoh Penggunaan Mobile App

### React Native / JavaScript
```javascript
// Login
const login = async (username, password) => {
  const response = await fetch('http://your-domain.com/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ username, password }),
  });
  const data = await response.json();
  
  if (data.success) {
    // Simpan token untuk request selanjutnya
    const token = data.data.token;
    return token;
  }
  throw new Error(data.message);
};

// Ambil data panen
const getPanenData = async (token, filters = {}) => {
  const url = new URL('http://your-domain.com/api/panen');
  Object.keys(filters).forEach(key => 
    filters[key] && url.searchParams.append(key, filters[key])
  );
  
  const response = await fetch(url, {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  const data = await response.json();
  
  if (data.success) {
    return data.data;
  }
  throw new Error(data.message);
};

// Usage
try {
  const token = await login('admin', 'admin123');
  const panenData = await getPanenData(token, {
    date_from: '2025-12-01',
    afdeling: 'Afd-1',
    page: 1,
    limit: 20
  });
  console.log(panenData.items);
} catch (error) {
  console.error('Error:', error.message);
}
```

### Flutter / Dart
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl = 'http://your-domain.com/api';
  String? _token;

  Future<String> login(String username, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'username': username,
        'password': password,
      }),
    );

    final data = jsonDecode(response.body);
    if (data['success']) {
      _token = data['data']['token'];
      return _token!;
    }
    throw Exception(data['message']);
  }

  Future<Map<String, dynamic>> getPanenData({
    int page = 1,
    int limit = 20,
    String? dateFrom,
    String? dateTo,
    String? afdeling,
  }) async {
    final queryParams = <String, String>{
      'page': page.toString(),
      'limit': limit.toString(),
    };
    
    if (dateFrom != null) queryParams['date_from'] = dateFrom;
    if (dateTo != null) queryParams['date_to'] = dateTo;
    if (afdeling != null) queryParams['afdeling'] = afdeling;

    final uri = Uri.parse('$baseUrl/panen').replace(queryParameters: queryParams);
    
    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $_token',
      },
    );

    final data = jsonDecode(response.body);
    if (data['success']) {
      return data['data'];
    }
    throw Exception(data['message']);
  }
}
```

## 🧪 Testing

### 1. Manual Testing
Gunakan file `docs/api-test.html` untuk testing manual melalui browser:
```
http://your-domain.com/docs/api-test.html
```

### 2. Command Line Testing
```bash
# Test login
curl -X POST http://localhost/MyApp/lubung-data-SAE/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Test get panen data (ganti TOKEN dengan token dari login)
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost/MyApp/lubung-data-SAE/api/panen?page=1&limit=5"

# Test dengan filter
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost/MyApp/lubung-data-SAE/api/panen?date_from=2025-12-01&afdeling=Afd-1"
```

## 📈 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { /* data object */ },
  "timestamp": "2025-12-04 10:30:45"
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    "items": [ /* array of items */ ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_items": 50,
      "items_per_page": 10,
      "has_next": true,
      "has_prev": false
    }
  },
  "timestamp": "2025-12-04 10:30:45"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "timestamp": "2025-12-04 10:30:45"
}
```

## 🔒 Security Notes

1. **Token Expiration**: Token berlaku 24 jam, gunakan refresh endpoint sebelum expired
2. **HTTPS**: Gunakan HTTPS di production untuk keamanan
3. **Secret Key**: Ganti secret key JWT dengan yang kuat di production
4. **Input Validation**: API sudah include basic validation, tapi pastikan validasi di mobile app juga
5. **Rate Limiting**: Pertimbangkan implementasi rate limiting untuk production

## 🐛 Troubleshooting

### Error: "Token not provided"
- Pastikan header Authorization dengan format: `Bearer YOUR_TOKEN`

### Error: "Connection error"
- Periksa konfigurasi database di `config/database.php`

### Error: "Method not allowed"
- Pastikan menggunakan HTTP method yang benar (GET, POST)

### Error: "Endpoint not found"
- Periksa URL dan pastikan mod_rewrite Apache sudah enabled

### CORS Issues
- Header CORS sudah di-set di `BaseAPI.php`, pastikan tidak ada konflik dengan server config

## 📚 Documentation

- **Full API Documentation**: `docs/api-documentation.md`
- **Interactive Testing**: `docs/api-test.html`

## 🤝 Support

Untuk bantuan atau pertanyaan, silakan hubungi tim development atau buat issue di repository project.