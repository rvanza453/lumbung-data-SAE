# Quick Start Guide - API Mobile App

## 🚀 Cara Cepat Menggunakan API

### 1. Setup Awal (5 menit)

#### A. Pastikan Database Siap
```sql
-- Jalankan jika belum ada
CREATE DATABASE lubung_data_sae;
-- Import database_setup.sql
```

#### B. Test Koneksi
Buka browser dan akses: `http://localhost/MyApp/lubung-data-SAE/docs/api-test.html`

### 2. Test Login Pertama

#### Menggunakan Browser Test Tool:
1. Buka `http://localhost/MyApp/lubung-data-SAE/docs/api-test.html`
2. Klik tombol "Login" (username: admin, password: admin123)
3. Jika berhasil, status akan berubah menjadi "✓ Authenticated"

#### Menggunakan curl:
```bash
curl -X POST http://localhost/MyApp/lubung-data-SAE/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

### 3. Ambil Data Panen

#### Dari Browser Test Tool:
1. Setelah login berhasil
2. Atur filter tanggal (optional)
3. Klik "Get Panen Data"

#### Menggunakan curl:
```bash
# Ganti YOUR_TOKEN dengan token dari response login
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/MyApp/lubung-data-SAE/api/panen?limit=5"
```

### 4. Implementasi di Mobile App

#### React Native Example:
```javascript
// 1. Install dependencies
npm install axios

// 2. Create API service
const API_BASE = 'http://your-server.com/api';

const apiService = {
  login: async (username, password) => {
    const response = await fetch(`${API_BASE}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });
    return response.json();
  },
  
  getPanenData: async (token, params = {}) => {
    const query = new URLSearchParams(params).toString();
    const response = await fetch(`${API_BASE}/panen?${query}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    return response.json();
  }
};

// 3. Usage in component
const App = () => {
  const [token, setToken] = useState(null);
  const [panenData, setPanenData] = useState([]);

  const handleLogin = async () => {
    try {
      const result = await apiService.login('admin', 'admin123');
      if (result.success) {
        setToken(result.data.token);
      }
    } catch (error) {
      alert('Login failed');
    }
  };

  const loadPanenData = async () => {
    try {
      const result = await apiService.getPanenData(token, {
        page: 1,
        limit: 20,
        date_from: '2025-12-01'
      });
      if (result.success) {
        setPanenData(result.data.items);
      }
    } catch (error) {
      alert('Failed to load data');
    }
  };

  return (
    <View>
      <Button title="Login" onPress={handleLogin} />
      <Button title="Load Data" onPress={loadPanenData} />
      <FlatList 
        data={panenData}
        keyExtractor={item => item.id.toString()}
        renderItem={({item}) => (
          <Text>{item.nama_pemanen} - {item.jumlah_janjang} janjang</Text>
        )}
      />
    </View>
  );
};
```

#### Flutter Example:
```dart
// 1. Add dependencies in pubspec.yaml
dependencies:
  http: ^0.13.0

// 2. Create API service
class ApiService {
  static const baseUrl = 'http://your-server.com/api';
  String? token;

  Future<bool> login(String username, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'username': username, 'password': password}),
    );
    
    final data = jsonDecode(response.body);
    if (data['success']) {
      token = data['data']['token'];
      return true;
    }
    return false;
  }

  Future<List<dynamic>> getPanenData({int page = 1, int limit = 20}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/panen?page=$page&limit=$limit'),
      headers: {'Authorization': 'Bearer $token'},
    );
    
    final data = jsonDecode(response.body);
    if (data['success']) {
      return data['data']['items'];
    }
    return [];
  }
}

// 3. Usage in widget
class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final apiService = ApiService();
  List<dynamic> panenData = [];
  bool isLoggedIn = false;

  Future<void> handleLogin() async {
    final success = await apiService.login('admin', 'admin123');
    setState(() {
      isLoggedIn = success;
    });
  }

  Future<void> loadData() async {
    final data = await apiService.getPanenData();
    setState(() {
      panenData = data;
    });
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: Scaffold(
        appBar: AppBar(title: Text('Lubung Data SAE')),
        body: Column(
          children: [
            ElevatedButton(
              onPressed: handleLogin,
              child: Text('Login'),
            ),
            if (isLoggedIn) ...[
              ElevatedButton(
                onPressed: loadData,
                child: Text('Load Data'),
              ),
              Expanded(
                child: ListView.builder(
                  itemCount: panenData.length,
                  itemBuilder: (context, index) {
                    final item = panenData[index];
                    return ListTile(
                      title: Text(item['nama_pemanen']),
                      subtitle: Text('${item['jumlah_janjang']} janjang'),
                      trailing: Text(item['afdeling']),
                    );
                  },
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
```

## 🔥 Tips untuk Developer Mobile

### 1. Handle Token Expiration
```javascript
// Check if token is expired before each request
const isTokenExpired = (token) => {
  const payload = JSON.parse(atob(token.split('.')[1]));
  return payload.exp * 1000 < Date.now();
};

// Auto refresh token
const makeAuthenticatedRequest = async (url, options = {}) => {
  if (isTokenExpired(token)) {
    await refreshToken();
  }
  
  return fetch(url, {
    ...options,
    headers: {
      ...options.headers,
      'Authorization': `Bearer ${token}`
    }
  });
};
```

### 2. Offline Support
```javascript
// Cache data for offline use
const cacheData = (key, data) => {
  AsyncStorage.setItem(key, JSON.stringify(data));
};

const getCachedData = async (key) => {
  const cached = await AsyncStorage.getItem(key);
  return cached ? JSON.parse(cached) : null;
};

// Load data with fallback to cache
const loadDataWithCache = async () => {
  try {
    const onlineData = await apiService.getPanenData(token);
    cacheData('panen_data', onlineData.data.items);
    return onlineData.data.items;
  } catch (error) {
    // Fallback to cached data
    return await getCachedData('panen_data') || [];
  }
};
```

### 3. Optimized Pagination
```javascript
const usePaginatedData = (apiCall) => {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [page, setPage] = useState(1);

  const loadMore = async () => {
    if (loading || !hasMore) return;
    
    setLoading(true);
    try {
      const result = await apiCall({ page, limit: 20 });
      if (result.success) {
        setData(prev => [...prev, ...result.data.items]);
        setHasMore(result.data.pagination.has_next);
        setPage(prev => prev + 1);
      }
    } finally {
      setLoading(false);
    }
  };

  return { data, loading, hasMore, loadMore };
};
```

## 🎯 URL Endpoints Lengkap

```
Base: http://your-domain.com/api

Authentication:
POST   /auth/login
GET    /auth/verify  
POST   /auth/refresh

Data Panen:
GET    /panen                    # List dengan filter & pagination
GET    /panen/{id}               # Detail berdasarkan ID
GET    /panen/statistics         # Statistik
GET    /panen/summary            # Ringkasan

Data Pengiriman:
GET    /pengiriman               # List dengan filter & pagination  
GET    /pengiriman/{id}          # Detail berdasarkan ID
GET    /pengiriman/statistics    # Statistik
GET    /pengiriman/summary       # Ringkasan
```

## 📱 Testing di Device

### Android (React Native)
```bash
# Enable network access di android/app/src/main/AndroidManifest.xml
<uses-permission android:name="android.permission.INTERNET" />

# Untuk development, gunakan IP komputer bukan localhost
const API_BASE = 'http://192.168.1.100/MyApp/lubung-data-SAE/api';
```

### iOS (React Native)  
```bash
# Add App Transport Security exception di Info.plist
<key>NSAppTransportSecurity</key>
<dict>
  <key>NSAllowsArbitraryLoads</key>
  <true/>
</dict>
```

## 🚨 Common Issues & Solutions

### Issue: CORS Error
**Solution:** API sudah include CORS headers, pastikan tidak ada proxy yang memblok

### Issue: Token Invalid
**Solution:** 
```javascript
// Clear token dan redirect ke login
localStorage.removeItem('token');
// atau AsyncStorage.removeItem('token') di React Native
```

### Issue: Data Kosong
**Solution:**
1. Check apakah data ada di database
2. Periksa filter parameter
3. Test dengan browser tool dulu

### Issue: Network Error
**Solution:**
1. Pastikan server running
2. Check firewall/antivirus
3. Gunakan IP address bukan localhost untuk device testing

## 📞 Support

Jika ada masalah:
1. Coba dulu dengan browser test tool: `/docs/api-test.html`
2. Check error response untuk detail
3. Lihat documentation lengkap: `/docs/api-documentation.md`