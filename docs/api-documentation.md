# API Documentation - Lubung Data SAE Mobile App

## Base URL
```
http://your-domain.com/api/
```

## Authentication
Semua endpoint (kecuali login) memerlukan authentication menggunakan Bearer token di header:
```
Authorization: Bearer <your_jwt_token>
```

## Response Format
Semua response menggunakan format JSON yang konsisten:

### Success Response
```json
{
    "success": true,
    "message": "Success message",
    "data": {},
    "timestamp": "2025-12-04 10:30:45"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "timestamp": "2025-12-04 10:30:45",
    "details": "Optional error details"
}
```

---

## 1. Authentication Endpoints

### 1.1 Login
Mendapatkan access token untuk authentication.

**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
    "username": "admin",
    "password": "admin123"
}
```

**Response Success:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "username": "admin",
            "full_name": "Administrator",
            "nik": null,
            "phone": null,
            "role": "admin"
        },
        "expires_at": "2025-12-05 10:30:45"
    },
    "timestamp": "2025-12-04 10:30:45"
}
```

### 1.2 Verify Token
Memverifikasi apakah token masih valid.

**Endpoint:** `GET /api/auth/verify`

**Headers:** `Authorization: Bearer <token>`

**Response Success:**
```json
{
    "success": true,
    "message": "Token is valid",
    "data": {
        "valid": true,
        "user": {
            "id": 1,
            "username": "admin",
            "full_name": "Administrator",
            "nik": null,
            "phone": null,
            "role": "admin"
        },
        "expires_at": "2025-12-05 10:30:45"
    },
    "timestamp": "2025-12-04 10:30:45"
}
```

### 1.3 Refresh Token
Memperbarui token yang akan expire.

**Endpoint:** `POST /api/auth/refresh`

**Headers:** `Authorization: Bearer <token>`

**Response Success:**
```json
{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "username": "admin",
            "full_name": "Administrator",
            "nik": null,
            "phone": null,
            "role": "admin"
        },
        "expires_at": "2025-12-05 15:30:45"
    },
    "timestamp": "2025-12-04 15:30:45"
}
```

---

## 2. Data Panen Endpoints

### 2.1 Get All Panen Data
Mengambil data panen dengan fitur filtering, sorting, dan pagination.

**Endpoint:** `GET /api/panen`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
- `page` (int, optional): Halaman yang diinginkan (default: 1)
- `limit` (int, optional): Jumlah data per halaman (default: 20, max: 100)
- `sort_by` (string, optional): Field untuk sorting
  - Options: `tanggal_pemeriksaan`, `afdeling`, `nama_pemanen`, `blok`, `no_ancak`, `no_tph`, `jumlah_janjang`, `jam`, `created_at`
  - Default: `tanggal_pemeriksaan`
- `sort_direction` (string, optional): Arah sorting (`ASC` atau `DESC`, default: `DESC`)
- `date_from` (date, optional): Filter tanggal mulai (format: YYYY-MM-DD)
- `date_to` (date, optional): Filter tanggal akhir (format: YYYY-MM-DD)
- `afdeling` (string, optional): Filter afdeling (partial match)
- `blok` (string, optional): Filter blok (partial match)
- `pemanen` (string, optional): Filter nama atau NIK pemanen (partial match)
- `kerani` (string, optional): Filter nama kerani (partial match)
- `min_janjang` (int, optional): Filter minimum jumlah janjang
- `max_janjang` (int, optional): Filter maximum jumlah janjang

**Example Request:**
```
GET /api/panen?page=1&limit=10&date_from=2025-12-01&afdeling=Afd-1&sort_by=jumlah_janjang&sort_direction=DESC
```

**Response Success:**
```json
{
    "success": true,
    "message": "Data panen retrieved successfully",
    "data": {
        "items": [
            {
                "id": 1,
                "upload_id": 1,
                "nama_kerani": "Revanza",
                "tanggal_pemeriksaan": "2025-12-01",
                "afdeling": "Afd-1",
                "nama_pemanen": "John Doe",
                "nik_pemanen": "1234567890",
                "blok": "A1",
                "no_ancak": "001",
                "no_tph": "TPH001",
                "jam": "10:33:00",
                "last_modified": "2025-12-01 10:33:45",
                "koordinat": {
                    "latitude": -6.2000,
                    "longitude": 106.8000
                },
                "jumlah_janjang": 25,
                "created_at": "2025-12-01 10:35:00",
                "upload_info": {
                    "filename": "Backup_2025-12-01_Revanza_Afd-1_10.33_1764850437_ffaff10f.json",
                    "original_filename": "backup_panen_2025-12-01.json",
                    "upload_date": "2025-12-01 10:35:00",
                    "uploaded_by": "Administrator"
                }
            }
        ],
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

### 2.2 Get Panen by ID
Mengambil data panen berdasarkan ID spesifik.

**Endpoint:** `GET /api/panen/{id}`

**Headers:** `Authorization: Bearer <token>`

**Response Success:**
```json
{
    "success": true,
    "message": "Panen record retrieved successfully",
    "data": {
        "id": 1,
        "upload_id": 1,
        "nama_kerani": "Revanza",
        "tanggal_pemeriksaan": "2025-12-01",
        "afdeling": "Afd-1",
        "nama_pemanen": "John Doe",
        "nik_pemanen": "1234567890",
        "blok": "A1",
        "no_ancak": "001",
        "no_tph": "TPH001",
        "jam": "10:33:00",
        "last_modified": "2025-12-01 10:33:45",
        "koordinat": {
            "latitude": -6.2000,
            "longitude": 106.8000
        },
        "jumlah_janjang": 25,
        "created_at": "2025-12-01 10:35:00",
        "upload_info": {
            "filename": "Backup_2025-12-01_Revanza_Afd-1_10.33_1764850437_ffaff10f.json",
            "original_filename": "backup_panen_2025-12-01.json",
            "upload_date": "2025-12-01 10:35:00",
            "uploaded_by": "Administrator"
        }
    },
    "timestamp": "2025-12-04 10:30:45"
}
```

### 2.3 Get Panen Statistics
Mengambil statistik data panen.

**Endpoint:** `GET /api/panen/statistics`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:** (sama seperti filter di endpoint utama)

**Response Success:**
```json
{
    "success": true,
    "message": "Panen statistics retrieved successfully",
    "data": {
        "overview": {
            "total_records": 150,
            "total_janjang": 3750,
            "average_janjang": 25.0,
            "min_janjang": 10,
            "max_janjang": 45,
            "total_afdeling": 5,
            "total_blok": 25,
            "total_pemanen": 30,
            "date_range": {
                "from": "2025-11-01",
                "to": "2025-12-01"
            }
        },
        "top_afdelings": [
            {
                "afdeling": "Afd-1",
                "record_count": 50,
                "total_janjang": 1250
            }
        ],
        "top_pemanen": [
            {
                "nama_pemanen": "John Doe",
                "nik_pemanen": "1234567890",
                "record_count": 15,
                "total_janjang": 375
            }
        ]
    },
    "timestamp": "2025-12-04 10:30:45"
}
```

### 2.4 Get Panen Summary
Mengambil ringkasan data panen berdasarkan grouping tertentu.

**Endpoint:** `GET /api/panen/summary`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
- `group_by` (string, required): Grouping criteria
  - Options: `date`, `afdeling`, `pemanen`
- Filter parameters lainnya sama seperti endpoint utama

**Example Request:**
```
GET /api/panen/summary?group_by=afdeling&date_from=2025-12-01&date_to=2025-12-01
```

**Response Success:**
```json
{
    "success": true,
    "message": "Panen summary retrieved successfully",
    "data": {
        "group_by": "afdeling",
        "summary": [
            {
                "group_key": "Afd-1",
                "record_count": 30,
                "total_janjang": 750,
                "average_janjang": 25.0,
                "min_janjang": 15,
                "max_janjang": 35
            }
        ]
    },
    "timestamp": "2025-12-04 10:30:45"
}
```

---

## 3. Data Pengiriman Endpoints

### 3.1 Get All Pengiriman Data
Mengambil data pengiriman dengan fitur filtering, sorting, dan pagination.

**Endpoint:** `GET /api/pengiriman`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
- `page` (int, optional): Halaman yang diinginkan (default: 1)
- `limit` (int, optional): Jumlah data per halaman (default: 20, max: 100)
- `sort_by` (string, optional): Field untuk sorting
  - Options: `tanggal`, `afdeling`, `nama_kerani`, `blok`, `nopol`, `nomor_kendaraan`, `no_tph`, `jumlah_janjang`, `kg`, `waktu`, `created_at`, `tipe_aplikasi`
  - Default: `tanggal`
- `sort_direction` (string, optional): Arah sorting (`ASC` atau `DESC`, default: `DESC`)
- `date_from` (date, optional): Filter tanggal mulai (format: YYYY-MM-DD)
- `date_to` (date, optional): Filter tanggal akhir (format: YYYY-MM-DD)
- `afdeling` (string, optional): Filter afdeling (partial match)
- `blok` (string, optional): Filter blok (partial match)
- `nopol` (string, optional): Filter nomor polisi atau nomor kendaraan (partial match)
- `kerani` (string, optional): Filter nama atau NIK kerani (partial match)
- `min_janjang` (int, optional): Filter minimum jumlah janjang
- `max_janjang` (int, optional): Filter maximum jumlah janjang
- `min_kg` (float, optional): Filter minimum berat (kg)
- `max_kg` (float, optional): Filter maximum berat (kg)
- `tipe_aplikasi` (string, optional): Filter tipe aplikasi (partial match)

**Example Request:**
```
GET /api/pengiriman?page=1&limit=10&date_from=2025-12-01&afdeling=Afd-1&sort_by=kg&sort_direction=DESC
```

**Response Success:**
```json
{
    "success": true,
    "message": "Data pengiriman retrieved successfully",
    "data": {
        "items": [
            {
                "id": 1,
                "upload_id": 2,
                "tipe_aplikasi": "Transport App v1.0",
                "nama_kerani": "John Kerani",
                "nik_kerani": "1234567890",
                "tanggal": "2025-12-01",
                "afdeling": "Afd-1",
                "nopol": "L",
                "nomor_kendaraan": "1234",
                "blok": "A1",
                "no_tph": "TPH001",
                "jumlah_janjang": 50,
                "waktu": "14:30:00",
                "koordinat": {
                    "latitude": -6.2000,
                    "longitude": 106.8000
                },
                "kg": 1250.5,
                "created_at": "2025-12-01 14:35:00",
                "upload_info": {
                    "filename": "Transport_2025-12-01_Afd-1_L_1234_TY_1764753880_76b78001.json",
                    "original_filename": "transport_data_2025-12-01.json",
                    "upload_date": "2025-12-01 14:35:00",
                    "uploaded_by": "Administrator"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 3,
            "total_items": 30,
            "items_per_page": 10,
            "has_next": true,
            "has_prev": false
        }
    },
    "timestamp": "2025-12-04 10:30:45"
}
```

### 3.2 Get Pengiriman by ID
Mengambil data pengiriman berdasarkan ID spesifik.

**Endpoint:** `GET /api/pengiriman/{id}`

**Headers:** `Authorization: Bearer <token>`

**Response Success:** (format sama seperti item di response 3.1)

### 3.3 Get Pengiriman Statistics
Mengambil statistik data pengiriman.

**Endpoint:** `GET /api/pengiriman/statistics`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:** (sama seperti filter di endpoint utama)

**Response Success:**
```json
{
    "success": true,
    "message": "Pengiriman statistics retrieved successfully",
    "data": {
        "overview": {
            "total_records": 100,
            "total_janjang": 5000,
            "average_janjang": 50.0,
            "min_janjang": 20,
            "max_janjang": 80,
            "total_kg": 125000.5,
            "average_kg": 1250.01,
            "min_kg": 500.0,
            "max_kg": 2000.0,
            "total_afdeling": 5,
            "total_blok": 20,
            "total_kendaraan": 15,
            "total_kerani": 10,
            "date_range": {
                "from": "2025-11-01",
                "to": "2025-12-01"
            }
        },
        "top_afdelings": [
            {
                "afdeling": "Afd-1",
                "record_count": 30,
                "total_janjang": 1500,
                "total_kg": 37500.0
            }
        ],
        "top_vehicles": [
            {
                "vehicle": "L - 1234",
                "record_count": 10,
                "total_janjang": 500,
                "total_kg": 12500.0
            }
        ],
        "top_kerani": [
            {
                "nama_kerani": "John Kerani",
                "nik_kerani": "1234567890",
                "record_count": 20,
                "total_janjang": 1000,
                "total_kg": 25000.0
            }
        ]
    },
    "timestamp": "2025-12-04 10:30:45"
}
```

### 3.4 Get Pengiriman Summary
Mengambil ringkasan data pengiriman berdasarkan grouping tertentu.

**Endpoint:** `GET /api/pengiriman/summary`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
- `group_by` (string, required): Grouping criteria
  - Options: `date`, `afdeling`, `vehicle`, `kerani`
- Filter parameters lainnya sama seperti endpoint utama

**Example Request:**
```
GET /api/pengiriman/summary?group_by=vehicle&date_from=2025-12-01&date_to=2025-12-01
```

**Response Success:**
```json
{
    "success": true,
    "message": "Pengiriman summary retrieved successfully",
    "data": {
        "group_by": "vehicle",
        "summary": [
            {
                "group_key": "L - 1234",
                "record_count": 5,
                "total_janjang": 250,
                "average_janjang": 50.0,
                "min_janjang": 40,
                "max_janjang": 60,
                "total_kg": 6250.0,
                "average_kg": 1250.0,
                "min_kg": 1000.0,
                "max_kg": 1500.0
            }
        ]
    },
    "timestamp": "2025-12-04 10:30:45"
}
```

---

## Error Codes

| HTTP Status | Description |
|-------------|-------------|
| 200 | Success |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Invalid or missing token |
| 404 | Not Found - Resource not found |
| 405 | Method Not Allowed |
| 500 | Internal Server Error |

## Common Error Responses

### 401 Unauthorized
```json
{
    "success": false,
    "message": "Token not provided",
    "timestamp": "2025-12-04 10:30:45"
}
```

### 400 Bad Request
```json
{
    "success": false,
    "message": "Username and password are required",
    "timestamp": "2025-12-04 10:30:45"
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Panen record not found",
    "timestamp": "2025-12-04 10:30:45"
}
```

---

## Usage Examples

### Login dan Mengambil Data
```javascript
// 1. Login
const loginResponse = await fetch('http://your-domain.com/api/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        username: 'admin',
        password: 'admin123'
    })
});
const loginData = await loginResponse.json();
const token = loginData.data.token;

// 2. Ambil data panen
const panenResponse = await fetch('http://your-domain.com/api/panen?page=1&limit=10', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});
const panenData = await panenResponse.json();
console.log(panenData.data.items);
```

### Filtering Data dengan Query Parameters
```javascript
// Ambil data panen dengan filter
const filterUrl = new URL('http://your-domain.com/api/panen');
filterUrl.searchParams.append('date_from', '2025-12-01');
filterUrl.searchParams.append('date_to', '2025-12-01');
filterUrl.searchParams.append('afdeling', 'Afd-1');
filterUrl.searchParams.append('sort_by', 'jumlah_janjang');
filterUrl.searchParams.append('sort_direction', 'DESC');

const response = await fetch(filterUrl, {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});
const data = await response.json();
```

## Notes
- Token berlaku selama 24 jam setelah login
- Gunakan endpoint `/api/auth/refresh` untuk memperbarui token sebelum expired
- Semua tanggal menggunakan format `YYYY-MM-DD`
- Pagination dimulai dari page 1
- Maximum limit per page adalah 100 items
- Koordinat disimpan dalam format JSON dengan key `latitude` dan `longitude`
- Semua response menggunakan UTF-8 encoding