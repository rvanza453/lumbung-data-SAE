<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - HR Analytics System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    
    <style>
        html, body {
            background-color: #0f172a;
            color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 sticky top-0 z-40 px-6 py-4 shadow-lg">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-indigo-500 to-blue-600 p-2 rounded-xl text-white shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-100">HR Analytics System</h1>
                    <p class="text-xs text-slate-400">Admin Dashboard</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="/" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg font-medium text-sm transition-colors">
                    Dashboard Overview
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8" id="stats-container">
            <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                <div class="text-slate-400 text-sm mb-2">Total Records</div>
                <div class="text-3xl font-bold text-white" id="stat-records">-</div>
            </div>
            <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                <div class="text-slate-400 text-sm mb-2">Total Batches</div>
                <div class="text-3xl font-bold text-white" id="stat-batches">-</div>
            </div>
            <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                <div class="text-slate-400 text-sm mb-2">Total Karyawan</div>
                <div class="text-3xl font-bold text-white" id="stat-karyawan">-</div>
            </div>
            <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                <div class="text-slate-400 text-sm mb-2">Last Upload</div>
                <div class="text-lg font-bold text-white" id="stat-last-upload">-</div>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 mb-8">
            <h2 class="text-xl font-bold text-white mb-4">Upload Data Excel</h2>
            
            <form id="upload-form" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">File Excel</label>
                        <input type="file" id="file-input" accept=".xlsx,.xls" required 
                               class="w-full px-4 py-2 bg-slate-900 border border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-slate-200">
                        <p class="text-xs text-slate-500 mt-1">Format: XLSX atau XLS (Max 10MB)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Periode</label>
                        <input type="text" id="periode-input" placeholder="Contoh: 2024-01" 
                               class="w-full px-4 py-2 bg-slate-900 border border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-slate-200">
                        <p class="text-xs text-slate-500 mt-1">Format: YYYY-MM atau custom label</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">BJR (Berat Janjang Rata-rata)</label>
                        <input type="number" id="bjr-input" value="15" step="0.1" min="1"
                               class="w-full px-4 py-2 bg-slate-900 border border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-slate-200">
                        <p class="text-xs text-slate-500 mt-1">Default: 15 Kg</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Catatan (Opsional)</label>
                        <input type="text" id="notes-input" placeholder="Tambahkan catatan..." 
                               class="w-full px-4 py-2 bg-slate-900 border border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-slate-200">
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" id="upload-btn"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-lg font-medium transition-colors">
                        Upload & Proses
                    </button>
                    <a href="/template-upload.xlsx" download 
                       class="px-6 py-3 bg-slate-700 hover:bg-slate-600 rounded-lg font-medium transition-colors">
                        Download Template
                    </a>
                </div>
            </form>
            
            <div id="upload-status" class="mt-4 hidden"></div>
        </div>

        <!-- Batches List -->
        <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
            <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Riwayat Upload</h2>
                <button onclick="loadBatches()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm font-medium transition-colors">
                    Refresh
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-900 text-slate-400 text-sm">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Batch ID</th>
                            <th class="px-6 py-4 text-left font-semibold">Filename</th>
                            <th class="px-6 py-4 text-left font-semibold">Periode</th>
                            <th class="px-6 py-4 text-center font-semibold">Records</th>
                            <th class="px-6 py-4 text-left font-semibold">Upload Date</th>
                            <th class="px-6 py-4 text-center font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody id="batches-tbody" class="divide-y divide-slate-700">
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/api/performance';
        
        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadBatches();
            
            // Set default periode
            const today = new Date();
            const defaultPeriode = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
            document.getElementById('periode-input').value = defaultPeriode;
        });

        // Load statistics
        async function loadStats() {
            try {
                const response = await fetch(API_BASE + '/stats');
                const data = await response.json();
                
                document.getElementById('stat-records').textContent = data.total_records.toLocaleString();
                document.getElementById('stat-batches').textContent = data.total_batches.toLocaleString();
                document.getElementById('stat-karyawan').textContent = data.total_karyawan.toLocaleString();
                
                if (data.latest_upload) {
                    const date = new Date(data.latest_upload.created_at);
                    document.getElementById('stat-last-upload').textContent = date.toLocaleDateString('id-ID');
                } else {
                    document.getElementById('stat-last-upload').textContent = 'Belum ada';
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Load batches
        async function loadBatches() {
            try {
                const response = await fetch(API_BASE + '/batches');
                const data = await response.json();
                
                const tbody = document.getElementById('batches-tbody');
                tbody.innerHTML = '';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(batch => {
                        const date = new Date(batch.created_at);
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-700/40 transition-colors';
                        tr.innerHTML = `
                            <td class="px-6 py-4 font-mono text-xs text-slate-300">${batch.batch_id}</td>
                            <td class="px-6 py-4 text-slate-200">${batch.filename}</td>
                            <td class="px-6 py-4 text-slate-300">${batch.periode || '-'}</td>
                            <td class="px-6 py-4 text-center font-bold text-indigo-400">${batch.performances_count}</td>
                            <td class="px-6 py-4 text-slate-400 text-sm">${date.toLocaleString('id-ID')}</td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="deleteBatch('${batch.batch_id}')" 
                                        class="px-3 py-1 bg-rose-900/40 hover:bg-rose-900/60 text-rose-400 rounded-lg text-sm font-medium transition-colors">
                                    Hapus
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada data</td></tr>';
                }
            } catch (error) {
                console.error('Error loading batches:', error);
                document.getElementById('batches-tbody').innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-rose-400">Error loading data</td></tr>';
            }
        }

        // Handle upload form
        document.getElementById('upload-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('file-input');
            const periodeInput = document.getElementById('periode-input');
            const bjrInput = document.getElementById('bjr-input');
            const notesInput = document.getElementById('notes-input');
            const uploadBtn = document.getElementById('upload-btn');
            const statusDiv = document.getElementById('upload-status');
            
            if (!fileInput.files[0]) {
                showStatus('Pilih file Excel terlebih dahulu', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('periode', periodeInput.value);
            formData.append('bjr', bjrInput.value);
            formData.append('notes', notesInput.value);
            formData.append('uploaded_by', 'Admin');
            
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            showStatus('Sedang memproses file...', 'info');
            
            try {
                const response = await fetch(API_BASE + '/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showStatus(result.message, 'success');
                    fileInput.value = '';
                    notesInput.value = '';
                    loadStats();
                    loadBatches();
                } else {
                    showStatus('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showStatus('Gagal upload: ' + error.message, 'error');
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload & Proses';
            }
        });

        // Delete batch
        async function deleteBatch(batchId) {
            if (!confirm('Yakin ingin menghapus batch ini? Data yang terhapus tidak dapat dikembalikan.')) {
                return;
            }
            
            try {
                const response = await fetch(API_BASE + '/batches/' + batchId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Batch berhasil dihapus');
                    loadStats();
                    loadBatches();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Gagal menghapus batch: ' + error.message);
            }
        }

        // Show status message
        function showStatus(message, type) {
            const statusDiv = document.getElementById('upload-status');
            statusDiv.className = 'mt-4 p-4 rounded-lg border ' + 
                (type === 'success' ? 'bg-emerald-900/40 border-emerald-700 text-emerald-300' :
                 type === 'error' ? 'bg-rose-900/40 border-rose-700 text-rose-300' :
                 'bg-blue-900/40 border-blue-700 text-blue-300');
            statusDiv.textContent = message;
            statusDiv.classList.remove('hidden');
            
            if (type === 'success') {
                setTimeout(() => statusDiv.classList.add('hidden'), 5000);
            }
        }
    </script>
</body>
</html>

