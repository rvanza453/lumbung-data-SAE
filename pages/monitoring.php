<?php
include_once '../includes/functions.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Data - Lubung Data SAE</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <style>
        /* Custom scrollbar hiding for cleaner UI */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Table styling for dense data */
        th, td {
            white-space: nowrap;
        }
        /* Sticky Footer style */
        tfoot {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background-color: #f3f4f6; /* bg-gray-100 */
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        /* Animation for Modal */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in {
            animation: fadeIn 0.2s ease-out;
        }
        
        /* Spin animation for loading button */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* --- PRINT STYLES --- */
        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }
            body {
                padding-top: 0 !important;
                background-color: white !important;
                -webkit-print-color-adjust: exact;
            }
            /* Hide UI elements */
            nav, header, .no-print, button, .shadow-sm, input, select, .border-dashed {
                display: none !important;
            }
            /* Show Print Area */
            #print-area {
                display: block !important;
                width: 100%;
                font-family: 'Times New Roman', Times, serif;
            }
            
            /* Print Table Styling */
            .print-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10px;
            }
            .print-table th, .print-table td {
                border: 1px solid black;
                padding: 4px;
                color: black;
                vertical-align: middle;
            }
            .print-table th {
                background-color: #f0f0f0 !important;
                text-align: center;
                font-weight: bold;
                text-transform: uppercase;
            }
            .signature-box {
                height: 60px;
            }
            tr {
                page-break-inside: avoid;
            }
        }
        
        /* Hide print area on screen */
        #print-area {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50">

    <nav class="bg-blue-600 shadow-lg fixed top-0 left-0 right-0 z-50 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="../dashboard.php" class="flex items-center text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-database text-xl mr-3"></i>
                        <span class="font-bold text-lg">Lubung Data SAE</span>
                    </a>
                </div>

                <div class="hidden md:block">
                    <div class="flex items-center space-x-1">
                        <a href="../dashboard.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                        <a href="upload.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                            <i class="fas fa-upload mr-2"></i>Upload Data
                        </a>
                        <a href="../admin/file-manager.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                            <i class="fas fa-folder-open mr-2"></i>File Manager
                        </a>
                        <a href="monitoring.php" class="bg-blue-800 text-blue-100 px-3 py-2 rounded-md text-sm font-medium flex items-center">
                            <i class="fas fa-chart-line mr-2"></i>Monitoring
                        </a>
                        <a href="../admin/user-management.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                            <i class="fas fa-users mr-2"></i>Kelola User
                        </a>
                        <a href="../admin/activity-logs.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                            <i class="fas fa-history mr-2"></i>Activity Logs
                        </a>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="relative">
                        <button id="profile-button" class="bg-blue-700 flex items-center text-sm rounded-full text-white hover:bg-blue-800 transition-colors px-3 py-2">
                            <i class="fas fa-user mr-2"></i>
                            <span>User Account</span>
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-user-edit mr-2"></i>Profil Saya
                            </a>
                            <hr class="border-gray-200 my-1">
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>

                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white hover:text-blue-200 focus:outline-none focus:text-blue-200 transition-colors">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-blue-700">
                <a href="dashboard.php" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="upload.php" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-upload mr-2"></i>Upload Data
                </a>
                <a href="file-manager.php" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-folder-open mr-2"></i>File Manager
                </a>
                <a href="monitoring.html" class="bg-blue-800 text-blue-100 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-chart-line mr-2"></i>Monitoring
                </a>
                <a href="user-management.php" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-users mr-2"></i>Kelola User
                </a>
                <a href="activity-logs.php" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-history mr-2"></i>Activity Logs
                </a>
                <hr class="border-blue-600 my-2">
                <a href="profile.php" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-user-edit mr-2"></i>Profil Saya
                </a>
                <a href="logout.php" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div id="root">
        <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh; background: #f9fafb;">
            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div style="font-size: 48px; margin-bottom: 20px;" class="fas fa-circle-notch fa-spin text-primary"></div>
                <h3 style="margin: 0 0 10px 0; color: #333;">Loading Monitoring System...</h3>
                <p style="margin: 0; color: #666; font-size: 14px;">Initializing React components and loading database...</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script type="text/babel">
        const { useState, useMemo } = React;

         /**
          * Sort data array berdasarkan tanggal, afdeling, blok, dan TPH
          * @param {Array} data
          * @returns {Array}
          */
        const sortData = (data) => {
            return data.sort((a, b) => {
                const dateA = a.date || a.tanggal || '';
                const dateB = b.date || b.tanggal || '';
                if (dateA !== dateB) return dateA.localeCompare(dateB);
                
                const afdA = String(a.afdeling || '').trim();
                const afdB = String(b.afdeling || '').trim();
                if (afdA !== afdB) return afdA.localeCompare(afdB);
                
                const blokA = String(a.blok || '').trim();
                const blokB = String(b.blok || '').trim();
                if (blokA !== blokB) return blokA.localeCompare(blokB, undefined, {numeric: true});
                
                const tphA = String(a.noTPH || '').trim();
                const tphB = String(b.noTPH || '').trim();
                return tphA.localeCompare(tphB, undefined, {numeric: true});
            });
        };

        // 2. Komponen Icon (Pindahkan ke Global Scope)
        const Icon = ({ name, className = "", size = 20 }) => {
            const icons = {
                upload: "📤",
                fileJson: "📄",
                alertCircle: "⚠️",
                checkCircle: "✅",
                truck: "🚛",
                trees: "🌳",
                layoutDashboard: "📊",
                search: "🔍",
                arrowRightLeft: "↔️",
                users: "👥",
                filter: "🔽",
                x: "❌",
                plus: "➕",
                trash2: "🗑️",
                pieChart: "📈",
                trendingUp: "📈",
                arrowDownCircle: "🔽",
                arrowUpCircle: "🔼",
                calendar: "📅",
                download: "⬇️",
                fileSpreadsheet: "📊",
                eye: "👁️",
                camera: "📷",
                calculator: "🧮",
                printer: "🖨️",
                database: "🗄️",
                refresh: "🔄",
                clock: "🕒",
                package: "📦",
                alertTriangle: "⚠️",
                map: "🗺️",
                percent: "📊",
                weight: "⚖️"
            };
            return (
                <span className={`${className}`} style={{fontSize: `${size}px`, lineHeight: 1}}>
                    {icons[name] || "📋"}
                </span>
            );
        };

        // 3. Komponen StatCard (Pindahkan ke Global Scope)
        const StatCard = ({ title, value, icon, color, subValue, subLabel }) => (
            <div className="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                <div className={`absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform duration-500`}>
                    <Icon name={icon} size={64} className={color.replace('bg-', 'text-')} />
                </div>
                <div className="relative z-10 flex justify-between items-start">
                    <div>
                        <p className="text-xs font-bold text-gray-400 uppercase tracking-wide">{title}</p>
                        <h3 className="text-2xl font-bold text-gray-800 mt-1">{value}</h3>
                        {subValue && (
                            <div className="flex items-center gap-1 mt-2">
                                <span className={`text-xs px-2 py-0.5 rounded-full ${color.replace('bg-', 'bg-opacity-10 text-')}`}>
                                    {subValue}
                                </span>
                                {subLabel && <span className="text-xs text-gray-400 ml-1">{subLabel}</span>}
                            </div>
                        )}
                    </div>
                    <div className={`p-3 rounded-lg ${color} bg-opacity-10`}>
                        <Icon name={icon} size={24} className={color.replace('bg-', 'text-')} />
                    </div>
                </div>
            </div>
        );

        // 4. Komponen Badge (Pindahkan ke Global Scope)
        const Badge = ({ children, color, onClick }) => (
            <span 
                onClick={onClick}
                className={`px-2 py-1 rounded text-xs font-medium ${color} ${onClick ? 'cursor-pointer hover:opacity-80' : ''}`}
            >
                {children}
            </span>
        );

        // 5. Komponen FilterSelect (Pindahkan ke Global Scope)
        const FilterSelect = ({ label, value, options, onChange }) => (
            <div className="flex flex-col gap-1 min-w-[140px]">
                <label className="text-xs font-medium text-gray-500 uppercase">{label}</label>
                <select 
                    value={value} 
                    onChange={(e) => onChange(e.target.value)}
                    className="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2"
                >
                    <option value="">Semua</option>
                    {options.map((opt, idx) => (
                        <option key={idx} value={opt}>{opt}</option>
                    ))}
                </select>
            </div>
        );

        // 6. PrintTemplate (FIXED: Transport Calculation & Data Mapping)
        const PrintTemplate = ({ data, activeTab, filters }) => {
            const title = activeTab === 'harvest' ? 'Laporan Panen Harian' : 
                          activeTab === 'transport' ? 'Laporan Pengiriman TBS' : 
                          'Restan';
            const printDate = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            // --- LOGIKA AGREGASI DATA KHUSUS PANEN ---
            let printData = data;

            if (activeTab === 'harvest') {
                // Grouping data per Pemanen (Logika Panen Tetap)
                const grouped = data.reduce((acc, curr) => {
                    const name = curr.namaPemanen || curr.pemanen || 'Tanpa Nama';
                    const nik = curr.nikPemanen || curr.nik || '';
                    
                    if (!acc[name]) {
                        acc[name] = {
                            empCode: nik,
                            namaPemanen: name,
                            status: '', 
                            jobCode: '',
                            jobDesc: 'Panen',
                            blokSet: new Set(),
                            thnTanam: '',
                            janjang: 0,
                            kg: 0,
                            hkSet: new Set(), 
                            dates: []
                        };
                    }
                    
                    // Akumulasi Panen
                    acc[name].janjang += parseInt(curr.jumlahJanjang || curr.jmlhJanjang || curr.jumlah_janjang || 0);
                    acc[name].kg += parseFloat(curr.kgTotal || curr.totalKg || curr.kg_total || 0);
                    
                    if (curr.blok) acc[name].blokSet.add(curr.blok);
                    if (curr.date) acc[name].hkSet.add(curr.date);

                    return acc;
                }, {});

                // Convert object kembali ke array dan format
                printData = Object.values(grouped).map(item => ({
                    ...item,
                    blok: Array.from(item.blokSet).join(', '), 
                    hk: item.hkSet.size 
                })).sort((a, b) => a.namaPemanen.localeCompare(b.namaPemanen));
            }

            return (
                <div id="print-area">
                    {/* Header Report */}
                    <div className="mb-4">
                        <h2 className="text-center font-bold text-lg underline mb-4">{title}</h2>
                        
                         <div className="text-xs font-bold">
                            <table className="w-full border-none">
                                <tbody>
                                    <tr>
                                        <td className="border-0 p-0 w-[150px]">Nama Kerani</td>
                                        <td className="border-0 p-0">: {filters.namaKerani ? filters.namaKerani : '_________________'}</td>
                                        <td className="border-0 p-0 w-[150px] pl-8">Estate</td>
                                        <td className="border-0 p-0">: SAE</td>
                                    </tr>
                                    <tr>
                                        <td className="border-0 p-0">Job Code/Description</td>
                                        <td className="border-0 p-0">: {activeTab === 'harvest' ? 'PANEN SAWIT' : activeTab === 'transport' ? 'ANGKUT TBS' : 'UMUM'}</td>
                                        <td className="border-0 p-0 pl-8">Afdeling</td>
                                        <td className="border-0 p-0">: {filters.afdeling || 'Semua'}</td>
                                    </tr>
                                    <tr>
                                        <td className="border-0 p-0">Tanggal</td>
                                        <td className="border-0 p-0">: {filters.startDate ? `${filters.startDate} s/d ${filters.endDate}` : printDate}</td>
                                        <td className="border-0 p-0 pl-8"></td>
                                        <td className="border-0 p-0"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Report Table */}
                    <table className="print-table">
                        <thead>
                            <tr>
                                <th rowSpan="2" style={{width: '80px'}}>Emp. Code</th>
                                <th rowSpan="2">Nama Karyawan</th>
                                <th rowSpan="2" style={{width: '50px'}}>Status</th>
                                <th rowSpan="2" style={{width: '60px'}}>Job Code</th>
                                <th rowSpan="2">Job Description</th>
                                <th rowSpan="2" style={{width: '60px'}}>Blok</th>
                                <th rowSpan="2" style={{width: '60px'}}>Thn Tanam</th>
                                <th colSpan="2">Hasil Kerja</th>
                                <th colSpan="2">Upah</th>
                                <th colSpan="3">Material</th>
                                <th colSpan="2">Overtime (Lembur)</th>
                            </tr>
                            <tr>
                                <th style={{width: '50px'}}>Sat</th>
                                <th style={{width: '60px'}}>Fisik</th>
                                <th style={{width: '40px'}}>Hk</th>
                                <th>Total (Rp)</th>
                                <th>Nama Bahan</th>
                                <th style={{width: '40px'}}>Sat</th>
                                <th style={{width: '50px'}}>Fisik</th>
                                <th>Jam</th>
                                <th>Total (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {printData.map((row, idx) => {
                                // Init variables
                                let empCode = '', nama = '', jobDesc = '', jobCode = '', status = '', blok = '', hk = '';
                                let sat = '', fisik = '';
                                let matNama = '', matSat = '', matFisik = '';

                                if (activeTab === 'harvest') {
                                    // --- LOGIC PANEN ---
                                    empCode = ''; 
                                    nama = row.namaPemanen || '';
                                    jobDesc = row.jobDesc || 'Panen';
                                    blok = row.blok && row.blok.length > 15 ? row.blok.substring(0, 15) + '..' : row.blok;
                                    hk = row.hk; 
                                    
                                    sat = ''; fisik = ''; // Hasil kerja kosong sesuai request
                                    matNama = 'Janjang'; matSat = 'Buah'; matFisik = row.janjang || '0';

                                } else if (activeTab === 'transport') {
                                    // --- LOGIC PERBAIKAN KHUSUS TRANSPORT ---
                                    empCode = row.nopol || ''; 
                                    
                                    // 1. Perbaikan Nama: Prioritas ambil dari key database (nama_kerani)
                                    nama = row.nama_kerani || row.namaKerani || row.kerani || 'Driver';
                                    
                                    jobDesc = 'Angkut';
                                    blok = row.blok || '';
                                    hk = '1';
                                    
                                    // 2. Ambil Data Angka (Prioritas key database snake_case)
                                    const janjang = parseInt(row.jumlah_janjang || row.jumlahJanjang || row.jmlhJanjang || 0);
                                    const koreksi = parseInt(row.koreksi_kirim || row.koreksiKirim || 0);
                                    const bjr = parseFloat(row.bjr || row.avgBjr || 0);
                                    const brondolan = parseFloat(row.kg_brd || row.kg_berondolan || row.kgBerondolan || row.kgBrd || 0);

                                    // 3. Kalkulasi: (Janjang + Koreksi)
                                    const totalJanjang = janjang + koreksi;
                                    
                                    // 4. Kalkulasi Kg: (Total Janjang * BJR) + Brondolan
                                    const totalKg = (totalJanjang * bjr) + brondolan;

                                    // Mapping Hasil Kerja (Fisik = Total Kg)
                                    sat = 'Kg';
                                    fisik = totalKg > 0 ? totalKg.toLocaleString('id-ID', { maximumFractionDigits: 2 }) : '0';
                                    
                                    // Mapping Material (Fisik = Total Janjang / Buah)
                                    matNama = 'TBS';
                                    matSat = 'Buah';
                                    matFisik = totalJanjang > 0 ? totalJanjang.toLocaleString('id-ID') : '0';

                                } else {
                                    // Recap / Lainnya
                                    nama = row.pemanen || '';
                                    jobDesc = row.status || '';
                                    blok = row.blok || '';
                                    sat = 'Jjg';
                                    fisik = row.panenJanjang || '0';
                                }

                                return (
                                    <tr key={idx}>
                                        <td>{empCode}</td>
                                        <td>{nama}</td>
                                        <td className="text-center">{status}</td> 
                                        <td className="text-center">{jobCode}</td> 
                                        <td>{jobDesc}</td>
                                        <td className="text-center">{blok}</td>
                                        <td></td> 
                                        <td className="text-center">{sat}</td>
                                        <td className="text-right">{fisik}</td>
                                        <td className="text-center">{hk}</td>
                                        <td></td> 
                                        <td className="text-center">{matNama}</td>
                                        <td className="text-center">{matSat}</td>
                                        <td className="text-right">{matFisik}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                );
                            })}
                            
                            {/* TOTAL ROW */}
                            <tr>
                                <td colSpan="7" className="text-center font-bold">JUMLAH TOTAL</td>
                                <td></td>
                                
                                {/* Total Hasil Kerja (KG untuk Transport) */}
                                <td className="text-right font-bold">
                                    {activeTab === 'transport'
                                        ? data.reduce((acc, curr) => {
                                            // Hitung total dengan rumus yg SAMA PERSIS dengan baris
                                            const j = parseInt(curr.jumlah_janjang || curr.jumlahJanjang || 0);
                                            const k = parseInt(curr.koreksi_kirim || curr.koreksiKirim || 0);
                                            const b = parseFloat(curr.bjr || curr.avgBjr || 0);
                                            const brd = parseFloat(curr.kg_brd || curr.kg_berondolan || curr.kgBerondolan || 0);
                                            
                                            // Rumus: (Jjg + Kor) * BJR + Brd
                                            return acc + ((j + k) * b) + brd;
                                          }, 0).toLocaleString('id-ID', {maximumFractionDigits: 2})
                                        : ''
                                    }
                                </td>
                                
                                <td className="text-center font-bold">
                                    {activeTab === 'harvest' 
                                        ? printData.reduce((acc, curr) => acc + (parseInt(curr.hk) || 0), 0)
                                        : ''
                                    }
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                
                                {/* Total Material (Janjang/Buah) */}
                                <td className="text-right font-bold">
                                    {activeTab === 'harvest' 
                                        ? printData.reduce((acc, curr) => acc + (parseInt(curr.janjang) || 0), 0).toLocaleString('id-ID')
                                        : activeTab === 'transport'
                                            ? data.reduce((acc, curr) => {
                                                const j = parseInt(curr.jumlah_janjang || curr.jumlahJanjang || 0);
                                                const k = parseInt(curr.koreksi_kirim || curr.koreksiKirim || 0);
                                                return acc + (j + k);
                                            }, 0).toLocaleString('id-ID')
                                            : ''
                                    }
                                </td>
                                <td colSpan="2"></td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="mt-8 border border-black flex">
                        <div className="flex-1 border-r border-black">
                            <div className="border-b border-black p-1 text-center font-bold">Diketahui Oleh</div>
                            <div className="h-[100px] flex items-end justify-center p-1 font-bold">Estate Manager</div>
                        </div>
                        <div className="flex-1 border-r border-black">
                            <div className="border-b border-black p-1 text-center font-bold">Disetujui Oleh</div>
                            <div className="flex">
                                <div className="flex-1 border-r border-black h-[100px] flex items-end justify-center p-1">Askep</div>
                                <div className="flex-1 h-[100px] flex items-end justify-center p-1">KTU</div>
                            </div>
                        </div>
                        <div className="flex-1 border-r border-black">
                            <div className="border-b border-black p-1 text-center font-bold">Diperiksa Oleh</div>
                            <div className="h-[100px] flex items-end justify-center p-1">Asst.</div>
                        </div>
                        <div className="flex-1">
                            <div className="border-b border-black p-1 text-center font-bold">Dibuat Oleh</div>
                            <div className="h-[100px] flex items-end justify-center p-1">Kerani</div>
                        </div>
                    </div>
                </div>
            );
        };

        const ImageModal = ({ src, onClose }) => {
            if (!src) return null;
            return (
                <div 
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 p-4 animate-fade-in backdrop-blur-sm" 
                    onClick={onClose}
                >
                    <div 
                        className="relative max-w-5xl max-h-[90vh] bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col" 
                        onClick={e => e.stopPropagation()}
                    >
                        <div className="p-2 flex justify-between items-center bg-gray-50 border-b">
                            <span className="text-sm font-medium text-gray-500 ml-2">Pratinjau Foto</span>
                            <button 
                                onClick={onClose} 
                                className="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1 rounded-full transition-colors"
                            >
                                <Icon name="x" size={24} />
                            </button>
                        </div>
                        <div className="p-1 overflow-auto flex-1 bg-black flex items-center justify-center">
                             <img src={src} alt="Bukti Foto" className="max-w-full max-h-[80vh] object-contain" />
                        </div>
                    </div>
                </div>
            );
        };

        const getInitialDateRange = () => {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - 30);

            const toYYYYMMDD = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            return {
                startDate: toYYYYMMDD(startDate),
                endDate: toYYYYMMDD(endDate),
            };
        };

        /**
         * Komponen utama aplikasi monitoring
         * Mengelola state utama, fetch data, dan handle koreksi
         */
        function App() {
            // Tab aktif: recap, harvest, atau transport
            const [activeTab, setActiveTab] = useState('recap');
            // File upload/import yang tersedia
            const [files, setFiles] = useState([]);
            // Data mentah panen dari database/API
            const [harvestRaw, setHarvestRaw] = useState([]);
            // Data mentah pengiriman dari database/API
            const [transportRaw, setTransportRaw] = useState([]);
            // Untuk preview gambar bukti
            const [previewImage, setPreviewImage] = useState(null); 
            // Status loading data
            const [loading, setLoading] = useState(true);

            // State monitoring (future use)
            const [monitoringData, setMonitoringData] = useState(null);
            const [monitoringSummary, setMonitoringSummary] = useState({});
            const [restanData, setRestanData] = useState([]);

            // State modal koreksi data
            const [showKoreksiModal, setShowKoreksiModal] = useState(false);
            const [editingRow, setEditingRow] = useState(null);
            // Form koreksi (jumlah dan alasan)
            const [koreksiForm, setKoreksiForm] = useState({
                koreksiPanen: 0,
                koreksiKirim: 0,
                alasan: ''
            });
            // Status submit koreksi
            const [isSubmittingKoreksi, setIsSubmittingKoreksi] = useState(false);

            // Base URL API
            const API_BASE_URL = window.location.origin + '/lubung-data-SAE/api';

            /**
             * Buka modal koreksi untuk baris tertentu
             * @param {Object} row - Data baris yang akan dikoreksi
             */
            const openEditKoreksiModal = (row) => {
                setEditingRow(row);
                setKoreksiForm({
                    koreksiPanen: row.koreksiPanen || 0,
                    koreksiKirim: row.koreksiKirim || 0,
                    alasan: ''
                });
                setShowKoreksiModal(true);
            };

            /**
             * Tutup modal koreksi dan reset form
             */
            const closeKoreksiModal = () => {
                setShowKoreksiModal(false);
                setEditingRow(null);
                setKoreksiForm({
                    koreksiPanen: 0,
                    koreksiKirim: 0,
                    alasan: ''
                });
                setIsSubmittingKoreksi(false);
            };

            /**
             * Handle perubahan form koreksi (jumlah/alasan)
             */
            const handleKoreksiFormChange = (field, value) => {
                setKoreksiForm(prev => ({
                    ...prev,
                    [field]: field === 'alasan' ? value : (parseInt(value) || 0)
                }));
            };

            /**
             * Submit koreksi ke backend (API PHP)
             */
            const handleKoreksiSubmit = async () => {
                if (!editingRow || !koreksiForm.alasan.trim()) {
                    alert('Alasan koreksi harus diisi!');
                    return;
                }

                let submissionOccurred = false;

                try {
                    setIsSubmittingKoreksi(true);
                    const headers = { 'Content-Type': 'application/json' };

                    // Koreksi panen
                    const hasPanenChanged = koreksiForm.koreksiPanen !== (editingRow.koreksiPanen || 0);
                    if (editingRow.panenJanjang > 0 && editingRow.panenId && hasPanenChanged) {
                        submissionOccurred = true;
                        const panenResponse = await fetch('../api/koreksi.php?action=panen', {
                            method: 'POST', headers,
                            body: JSON.stringify({
                                id: editingRow.panenId,
                                koreksi_panen: koreksiForm.koreksiPanen,
                                alasan: koreksiForm.alasan
                            })
                        });
                        const panenResult = await panenResponse.json();
                        if (!panenResponse.ok || !panenResult.success) {
                            throw new Error(panenResult.message || 'Gagal menyimpan koreksi panen');
                        }
                    }

                    // Koreksi pengiriman
                    const hasKirimChanged = koreksiForm.koreksiKirim !== (editingRow.koreksiKirim || 0);
                    if (editingRow.transportJanjang > 0 && editingRow.pengirimanId && hasKirimChanged) {
                        submissionOccurred = true;
                        const pengirimanResponse = await fetch('../api/koreksi.php?action=pengiriman', {
                            method: 'POST', headers,
                            body: JSON.stringify({
                                id: editingRow.pengirimanId,
                                koreksi_kirim: koreksiForm.koreksiKirim,
                                alasan: koreksiForm.alasan
                            })
                        });
                        const pengirimanResult = await pengirimanResponse.json();
                        if (!pengirimanResponse.ok || !pengirimanResult.success) {
                            throw new Error(pengirimanResult.message || 'Gagal menyimpan koreksi pengiriman');
                        }
                    }

                    if (submissionOccurred) {
                        alert('Koreksi berhasil disimpan!');
                        closeKoreksiModal();
                        setTimeout(() => { window.location.reload(); }, 1000);
                    } else {
                        alert('Tidak ada perubahan nilai koreksi untuk disimpan.');
                        closeKoreksiModal();
                    }

                } catch (error) {
                    alert('Error menyimpan koreksi: ' + error.message);
                } finally {
                    setIsSubmittingKoreksi(false);
                }
            };

            React.useEffect(() => {
                // Load data from database directly on component mount
                const loadDatabaseData = async () => {
                    console.log('🔄 Starting to load data from database...');
                    
                    try {
                        setLoading(true);
                        
                        console.log('📡 Fetching from API...');
                        const response = await fetch('../api/direct_data.php?action=all', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            }
                        });
                        
                        console.log('📡 Response status:', response.status);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        
                        const result = await response.json();
                        console.log('📡 API Response:', result);
                        
                        if (result.success && result.data) {
                            const { panen, pengiriman } = result.data;
                            
                            console.log(`✅ Success! Loaded ${panen.length} panen records and ${pengiriman.length} pengiriman records`);
                            
                            // NORMALISASI DATA UNTUK MENGATASI INCONSISTENT MAPPING
                            const normalizedPanen = panen.map(item => {
                                const janjang = parseInt(item.jmlhJanjang || item.jumlah_janjang || item.jumlahJanjang) || 0;
                                const bjrVal = parseFloat(item.avgBjr || item.bjr) || 0;
                                const kgBrd = parseFloat(item.kgBerondolan || item.kg_brd || item.kg_berondolan) || 0;
                                // Kalkulasi Kg Total jika 0/null untuk mengatasi delay loading
                                let total = parseFloat(item.totalKg || item.kg_total || item.kgTotal) || 0;
                                if (total === 0 && janjang > 0 && bjrVal > 0) {
                                    total = (janjang * bjrVal) + kgBrd;
                                }
                                
                                return {
                                    ...item,
                                    panenId: item.id, // Ensure panenId is set for correction requests
                                    // KUNCI PERBAIKAN: Pastikan alias konsisten
                                    jumlahJanjang: janjang,
                                    jmlhJanjang: janjang,
                                    panenJanjang: janjang,
                                    koreksiPanen: parseInt(item.koreksiPanen || 0),
                                    bjr: bjrVal,
                                    avgBjr: bjrVal,
                                    kgTotal: total,
                                    totalKg: total,
                                    kgBerondolan: kgBrd,
                                    tipeAplikasi: 'database'
                                };
                            });
                            
                            const normalizedPengiriman = pengiriman.map(item => {
                                const janjang = parseInt(item.jmlhJanjang || item.jumlah_janjang || item.jumlahJanjang) || 0;
                                const bjrVal = parseFloat(item.bjr) || 0;
                                const kgBrd = parseFloat(item.kgBrd || item.kg_brd || item.kg_berondolan) || 0;
                                let total = parseFloat(item.totalKg || item.kg_total || item.kgTotal) || 0;
                                if (total === 0 && janjang > 0 && bjrVal > 0) {
                                    total = (janjang * bjrVal) + kgBrd;
                                }
                                
                                return {
                                    ...item,
                                    pengirimanId: item.id, // Ensure pengirimanId is set for correction requests
                                    jumlahJanjang: janjang,
                                    jmlhJanjang: janjang,
                                    transportJanjang: janjang,
                                    koreksiKirim: parseInt(item.koreksiKirim || 0),
                                    bjr: bjrVal,
                                    kgTotal: total,
                                    totalKg: total,
                                    kgBerondolan: kgBrd,
                                    tipeAplikasi: 'database'
                                };
                            });
                            
                            // Set data yang sudah dinormalisasi
                            setHarvestRaw(normalizedPanen);
                            setTransportRaw(normalizedPengiriman);
                            
                            // Create simulated file entries for compatibility
                            setFiles([
                                {
                                    name: `Database_Panen_${normalizedPanen.length}_records.json`,
                                    content: normalizedPanen,
                                    size: normalizedPanen.length * 100
                                },
                                {
                                    name: `Database_Pengiriman_${normalizedPengiriman.length}_records.json`,
                                    content: normalizedPengiriman,
                                    size: normalizedPengiriman.length * 100
                                }
                            ]);
                            
                        } else {
                            console.log('❌ API Success but no data:', result);
                            setHarvestRaw([]);
                            setTransportRaw([]);
                            setFiles([]);
                        }
                        
                    } catch (error) {
                        console.error('❌ Database load error:', error);
                        alert('Error loading data from database: ' + error.message + '\n\nPlease check console for details.');
                        setHarvestRaw([]);
                        setTransportRaw([]);
                        setFiles([]);
                    } finally {
                        setLoading(false);
                        console.log('✅ Data loading completed');
                    }
                };
                
                // Add a small delay to ensure DOM is ready
                setTimeout(() => {
                    loadDatabaseData();
                }, 100);
            }, []);

            
            const [filters, setFilters] = useState({
                afdeling: '',
                blok: '',
                pemanen: '',
                namaKerani: '', 
                noKend: '', // Tambahkan state untuk noKend
                status: '',
                ...getInitialDateRange()   
            });

            /**
             * State untuk sorting tabel (kolom dan arah)
             */
            const [sortConfig, setSortConfig] = useState({
                key: null,
                direction: 'asc' // 'asc' atau 'desc'
            });

            const [currentView, setCurrentView] = useState('blok'); // 'blok', 'tph', 'detail'
            const [selectedBlok, setSelectedBlok] = useState(null);
            const [selectedTph, setSelectedTph] = useState(null);
            const [showActivityDetail, setShowActivityDetail] = useState(false);
            
            // State khusus harvest view
            const [harvestView, setHarvestView] = useState('blok'); // 'blok', 'tph', 'detail'
            const [selectedHarvestBlok, setSelectedHarvestBlok] = useState(null);
            const [selectedHarvestTph, setSelectedHarvestTph] = useState(null);

            // State khusus Transport view (Baru Ditambahkan)
            const [transportView, setTransportView] = useState('blok'); // 'blok', 'tph', 'detail'
            const [selectedTransportBlok, setSelectedTransportBlok] = useState(null);
            const [selectedTransportTph, setSelectedTransportTph] = useState(null);

            /**
             * Handler upload file manual (input[type=file])
             * Membaca file JSON dan proses ke state
             */
            const handleFiles = (event) => {
                const newFiles = Array.from(event.target.files);
                const readers = newFiles.map(file => {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = (e) => resolve({ name: file.name, content: JSON.parse(e.target.result), size: file.size });
                        reader.readAsText(file);
                    });
                });
                Promise.all(readers).then(results => {
                    processUploadedFiles(results);
                });
            };

            /**
             * Filter data berdasarkan rentang tanggal dari state filters
             */
            const isInDateRange = (itemDate) => {
                if (!filters.startDate && !filters.endDate) return true;
                if (!itemDate) return false;
                const current = new Date(itemDate).getTime();
                const start = filters.startDate ? new Date(filters.startDate).getTime() : 0;
                const end = filters.endDate ? new Date(filters.endDate).getTime() : Infinity;
                return current >= start && current <= end;
            };

            // 1. MEMOIZED DATA: Filter Tanggal Saja (Untuk Opsi Dropdown)
            const dateFilteredHarvest = useMemo(() => {
                return harvestRaw.filter(item => isInDateRange(item.date));
            }, [harvestRaw, filters.startDate, filters.endDate]);

            const dateFilteredTransport = useMemo(() => {
                return transportRaw.filter(item => isInDateRange(item.date));
            }, [transportRaw, filters.startDate, filters.endDate]);

            // 2. MEMOIZED DATA: Filter Lengkap (Tanggal + Atribut) -> Untuk Tabel & Perhitungan
            const fullyFilteredHarvest = useMemo(() => {
                return dateFilteredHarvest.filter(item => {
                    // Filter Afdeling
                    if (filters.afdeling && item.afdeling !== filters.afdeling) return false;
                    // Filter Blok
                    if (filters.blok && item.blok !== filters.blok) return false;
                    // Filter Pemanen
                    if (filters.pemanen && (item.namaPemanen || item.pemanen) !== filters.pemanen) return false;
                    // Filter Kerani
                    if (filters.namaKerani && (item.namaKerani || item.kerani) !== filters.namaKerani) return false;
                    return true;
                });
            }, [dateFilteredHarvest, filters]);

            const fullyFilteredTransport = useMemo(() => {
                return dateFilteredTransport.filter(item => {
                    // Filter Afdeling
                    if (filters.afdeling && item.afdeling !== filters.afdeling) return false;
                    // Filter Blok
                    if (filters.blok && item.blok !== filters.blok) return false;
                    // Filter Kerani
                    if (filters.namaKerani && (item.namaKerani || item.kerani) !== filters.namaKerani) return false;
                    // Filter No Kendaraan
                    if (filters.noKend && (item.nopol || item.noKend) !== filters.noKend) return false;
                    return true;
                });
            }, [dateFilteredTransport, filters]);

            // 3. OPSII FILTER: Diambil dari Data Date-Filtered (Supaya opsi tidak hilang saat dipilih)
            const filterOptions = useMemo(() => {
                const unique = (arr) => [...new Set(arr)].filter(Boolean).sort();

                if (activeTab === 'recap') {
                    const allAfdeling = [...dateFilteredHarvest, ...dateFilteredTransport].map(i => i.afdeling);
                    const allBlok = [...dateFilteredHarvest, ...dateFilteredTransport].map(i => i.blok);
                    
                    return {
                        afdeling: unique(allAfdeling),
                        blok: unique(allBlok),
                        status: ['Sesuai', 'Restan'],
                        pemanen: [],
                        namaKerani: [],
                        noKend: []
                    };
                } else if (activeTab === 'harvest') {
                    return {
                        afdeling: unique(dateFilteredHarvest.map(item => item.afdeling)),
                        blok: unique(dateFilteredHarvest.map(item => item.blok)),
                        status: [],
                        pemanen: unique(dateFilteredHarvest.map(item => item.namaPemanen || item.pemanen)),
                        namaKerani: unique(dateFilteredHarvest.map(item => item.namaKerani || item.kerani)),
                        noKend: []
                    };
                } else {
                    return {
                        afdeling: unique(dateFilteredTransport.map(item => item.afdeling)),
                        blok: unique(dateFilteredTransport.map(item => item.blok)),
                        status: [],
                        pemanen: [],
                        namaKerani: unique(dateFilteredTransport.map(item => item.namaKerani || item.kerani)),
                        noKend: unique(dateFilteredTransport.map(item => item.nopol || item.noKend))
                    };
                }
            }, [activeTab, dateFilteredHarvest, dateFilteredTransport]);

            // Helper function untuk normalisasi TPH key secara konsisten
            // Digunakan di semua agregasi data untuk memastikan konsistensi
            // Menangani: null, undefined, empty string, 0, dan format berbeda ("2" vs "02")
            const normalizeTphKey = React.useCallback((item) => {
                // Coba noTPH dulu, lalu tph
                let tphValue = item.noTPH ?? item.tph ?? null;
                
                // Handle null/undefined
                if (tphValue === null || tphValue === undefined) {
                    return 'Unknown';
                }
                
                // Convert ke string dan trim
                let tphStr = String(tphValue).trim();
                
                // Jika string kosong setelah trim, gunakan 'Unknown'
                if (!tphStr) {
                    return 'Unknown';
                }
                
                // Normalisasi: jika bisa di-parse sebagai angka, gunakan angka (untuk konsistensi "2" vs "02")
                // Tapi tetap simpan sebagai string untuk key consistency
                const numValue = parseInt(tphStr, 10);
                if (!isNaN(numValue) && String(numValue) === tphStr) {
                    return String(numValue); // Normalisasi "02" menjadi "2"
                }
                
                return tphStr;
            }, []);

            /**
             * Agregasi data recap: blok → TPH → detail
             * MENGGUNAKAN DATA YANG SUDAH DI-FILTER LENGKAP (fullyFiltered...)
             */
            const blokData = useMemo(() => {
                const blokMap = {};
            
                fullyFilteredHarvest.forEach(item => {
                    const afdeling = item.afdeling || '';
                    const blok = item.blok || '';
                    const blokKey = blok.trim();

                    if (!blokKey) return; 

                    if (!blokMap[blokKey]) {
                        blokMap[blokKey] = {
                            blok: blokKey,          
                            blokName: blokKey,       
                            afdeling: afdeling,     
                            totalPanen: 0,
                            totalKirim: 0,
                            restan: 0,
                            tphCount: 0,
                            tphData: {}
                        };
                    }
                    const janjang = (parseInt(item.janjang || item.jumlahJanjang || item.totalJanjang) || 0) + (parseInt(item.koreksiPanen) || 0);
                    blokMap[blokKey].totalPanen += janjang;

                    const tphKey = normalizeTphKey(item);
                    if (!blokMap[blokKey].tphData[tphKey]) {
                        blokMap[blokKey].tphData[tphKey] = {
                            tph: tphKey,
                            blok: blokKey,
                            afdeling: afdeling,
                            totalPanen: 0,
                            totalKirim: 0,
                            restan: 0,
                            activities: [],
                            pemanenSet: new Set()
                        };
                    }
                    blokMap[blokKey].tphData[tphKey].totalPanen += janjang;
                    
                    if (item.namaPemanen || item.pemanen) {
                        blokMap[blokKey].tphData[tphKey].pemanenSet.add(item.namaPemanen || item.pemanen);
                    }

                    blokMap[blokKey].tphData[tphKey].activities.push({
                        type: 'Panen',
                        date: item.date,
                        kerani: item.namaKerani || item.kerani,
                        pemanen: item.namaPemanen || item.pemanen,
                        janjang: janjang,
                        jam: item.jam,
                        ...item
                    });
                });

                // Proses data kirim TERFILTER
                fullyFilteredTransport.forEach(item => {
                    const afdeling = item.afdeling || '';
                    const blok = item.blok || '';
                    const blokKey = blok.trim();

                    if (!blokKey) return;

                    if (!blokMap[blokKey]) {
                        blokMap[blokKey] = {
                            blok: blokKey,
                            blokName: blokKey,
                            afdeling: afdeling,
                            totalPanen: 0,
                            totalKirim: 0,
                            restan: 0,
                            tphCount: 0,
                            tphData: {}
                        };
                    }
                    const janjang = (parseInt(item.janjang || item.jumlahJanjang || item.jmlhJanjang) || 0) + (parseInt(item.koreksiKirim) || 0);
                    blokMap[blokKey].totalKirim += janjang;
                    
                    const tphKey = normalizeTphKey(item);
                    if (!blokMap[blokKey].tphData[tphKey]) {
                        blokMap[blokKey].tphData[tphKey] = {
                            tph: tphKey,
                            blok: blokKey,
                            afdeling: afdeling,
                            totalPanen: 0,
                            totalKirim: 0,
                            restan: 0,
                            activities: [],
                            pemanenSet: new Set()
                        };
                    }
                    blokMap[blokKey].tphData[tphKey].totalKirim += janjang;
                    blokMap[blokKey].tphData[tphKey].activities.push({
                        type: 'Kirim',
                        date: item.date,
                        kerani: item.namaKerani || item.kerani,
                        nopol: item.nopol,
                        janjang: janjang,
                        waktu: item.waktu,
                        // Data tambahan
                        ...item
                    });
                });

                Object.values(blokMap).forEach(blok => {
                    blok.restan = Math.max(0, blok.totalPanen - blok.totalKirim);
                    blok.tphCount = Object.keys(blok.tphData).length;
                    
                    let maxDelayInBlok = 0;
                    
                    Object.values(blok.tphData).forEach(tph => {
                        tph.restan = Math.max(0, tph.totalPanen - tph.totalKirim);
                        tph.activities.sort((a, b) => new Date(a.date) - new Date(b.date));
                        tph.pemanen = Array.from(tph.pemanenSet || []).join(', ');
                        
                        // Hitung delay: hari sejak panen terakhir tanpa pengiriman (> 1 hari)
                        const panenActivities = tph.activities.filter(a => a.type === 'Panen');
                        const kirimActivities = tph.activities.filter(a => a.type === 'Kirim');
                        
                        tph.delay = 0; // Default no delay
                        
                        if (panenActivities.length > 0 && tph.restan > 0) {
                            // Cari panen terakhir (berdasarkan tanggal dan waktu)
                            const lastPanen = panenActivities.reduce((latest, current) => {
                                const latestDate = new Date(latest.date + ' ' + (latest.jam || '00:00:00'));
                                const currentDate = new Date(current.date + ' ' + (current.jam || '00:00:00'));
                                return currentDate > latestDate ? current : latest;
                            });
                            
                            const lastPanenDate = new Date(lastPanen.date);
                            lastPanenDate.setHours(0, 0, 0, 0);
                            
                            // Cek apakah ada pengiriman setelah atau pada hari yang sama dengan panen terakhir
                            const hasKirimAfterPanen = kirimActivities.some(kirim => {
                                const kirimDate = new Date(kirim.date);
                                kirimDate.setHours(0, 0, 0, 0);
                                return kirimDate >= lastPanenDate;
                            });
                            
                            // Jika tidak ada pengiriman setelah panen terakhir, hitung delay
                            if (!hasKirimAfterPanen) {
                                const today = new Date();
                                today.setHours(0, 0, 0, 0);
                                
                                const diffTime = today - lastPanenDate;
                                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                                
                                // Delay hanya ditampilkan jika > 1 hari
                                if (diffDays > 1) {
                                    tph.delay = diffDays;
                                    
                                    // Track delay maksimal di blok
                                    if (diffDays > maxDelayInBlok) {
                                        maxDelayInBlok = diffDays;
                                    }
                                }
                            }
                        }
                    });
                    
                    // Set maxDelay untuk blok
                    blok.maxDelay = maxDelayInBlok;
                });

                return Object.values(blokMap);
            }, [fullyFilteredHarvest, fullyFilteredTransport, normalizeTphKey]);
            
            /**
             * Agregasi data panen view blok
             * MENGGUNAKAN DATA YANG SUDAH DI-FILTER (fullyFilteredHarvest)
             */
            const harvestBlokData = useMemo(() => {
                if (!fullyFilteredHarvest || fullyFilteredHarvest.length === 0) {
                    return [];
                }
                const harvestBlokMap = {};
                fullyFilteredHarvest.forEach((item, index) => {
                    try {
                        const afdeling = item.afdeling || '';
                        const blok = item.blok || '';
                        const blokKey = blok.trim();

                        if (!blokKey) return;

                        if (!harvestBlokMap[blokKey]) {
                            harvestBlokMap[blokKey] = {
                                blok: blokKey,      
                                blokName: blokKey,  
                                afdeling: afdeling,
                                totalJanjang: 0,
                                totalKg: 0,
                                avgBjr: 0,
                                tphCount: 0,
                                pemanen: new Set(),
                                kerani: new Set(),
                                tphData: {}
                            };
                        }
                        const janjang = (parseInt(item.janjang || item.jumlahJanjang || item.totalJanjang) || 0) + (parseInt(item.koreksiPanen) || 0);
                        const kg = parseFloat(item.kgTotal || item.totalKg) || 0;
                        const bjr = parseFloat(item.bjr || item.avgBjr) || 0;

                        harvestBlokMap[blokKey].totalJanjang += janjang;
                        harvestBlokMap[blokKey].totalKg += kg;
                        
                        if (item.namaPemanen || item.pemanen) harvestBlokMap[blokKey].pemanen.add(item.namaPemanen || item.pemanen || '');
                        if (item.namaKerani || item.kerani) harvestBlokMap[blokKey].kerani.add(item.namaKerani || item.kerani || '');
                        
                        const tphKey = normalizeTphKey(item);
                        if (!harvestBlokMap[blokKey].tphData[tphKey]) {
                            harvestBlokMap[blokKey].tphData[tphKey] = {
                                tph: tphKey,
                                totalJanjang: 0,
                                totalKg: 0,
                                avgBjr: 0,
                                activities: [],
                                pemanen: new Set(),
                                kerani: new Set()
                            };
                        }
                        harvestBlokMap[blokKey].tphData[tphKey].totalJanjang += janjang;
                        harvestBlokMap[blokKey].tphData[tphKey].totalKg += kg;
                        harvestBlokMap[blokKey].tphData[tphKey].pemanen.add(item.namaPemanen || item.pemanen || '');
                        harvestBlokMap[blokKey].tphData[tphKey].kerani.add(item.namaKerani || item.kerani || '');
                        harvestBlokMap[blokKey].tphData[tphKey].activities.push({
                            ...item,
                            janjang: janjang,
                            kg: kg,
                            bjr: bjr
                        });
                    } catch (err) {}
                });
                
                Object.values(harvestBlokMap).forEach(blok => {
                    blok.avgBjr = blok.totalJanjang > 0 ? (blok.totalKg / blok.totalJanjang).toFixed(2) : 0;
                    blok.tphCount = Object.keys(blok.tphData).length;
                    blok.pemanen = Array.from(blok.pemanen).filter(p => p);
                    blok.kerani = Array.from(blok.kerani).filter(k => k);
                    
                    Object.values(blok.tphData).forEach(tph => {
                        tph.avgBjr = tph.totalJanjang > 0 ? (tph.totalKg / tph.totalJanjang).toFixed(2) : 0;
                        tph.pemanen = Array.from(tph.pemanen).filter(p => p);
                        tph.kerani = Array.from(tph.kerani).filter(k => k);
                        tph.activities.sort((a, b) => new Date(a.date) - new Date(b.date));
                    });
                });
                return Object.values(harvestBlokMap);
            }, [fullyFilteredHarvest, normalizeTphKey]);

            /**
             * Agregasi data TRANSPORT per Blok (Logika Baru)
             */
            const transportBlokData = useMemo(() => {
                if (!fullyFilteredTransport || fullyFilteredTransport.length === 0) {
                    return [];
                }
                const transportBlokMap = {};
                fullyFilteredTransport.forEach((item) => {
                    const afdeling = item.afdeling || '';
                    const blok = item.blok || '';
                    const blokKey = blok.trim();

                    if (!blokKey) return;

                    if (!transportBlokMap[blokKey]) {
                        transportBlokMap[blokKey] = {
                            blok: blokKey,
                            blokName: blokKey,
                            afdeling: afdeling,
                            totalJanjang: 0,
                            totalKoreksi: 0,
                            totalKg: 0,
                            totalKgBerondolan: 0,
                            tphCount: 0,
                            tphData: {}
                        };
                    }
                    const janjangOriginal = parseInt(item.janjang || item.jumlahJanjang || item.jmlhJanjang) || 0;
                    const koreksi = parseInt(item.koreksiKirim) || 0;
                    const janjangFinal = janjangOriginal + koreksi;
                    const kg = parseFloat(item.kgTotal || item.totalKg) || 0;
                    const kgBrd = parseFloat(item.kgBerondolan || item.kgBrd) || 0;

                    transportBlokMap[blokKey].totalJanjang += janjangOriginal; // Hanya original, tidak termasuk koreksi
                    transportBlokMap[blokKey].totalKoreksi += koreksi;
                    transportBlokMap[blokKey].totalKg += kg;
                    transportBlokMap[blokKey].totalKgBerondolan += kgBrd;

                    const tphKey = normalizeTphKey(item);
                    if (!transportBlokMap[blokKey].tphData[tphKey]) {
                        transportBlokMap[blokKey].tphData[tphKey] = {
                            tph: tphKey,
                            blok: blokKey,
                            totalJanjang: 0,
                            totalKoreksi: 0,
                            totalKg: 0,
                            totalKgBerondolan: 0,
                            activities: []
                        };
                    }
                    transportBlokMap[blokKey].tphData[tphKey].totalJanjang += janjangOriginal; // Hanya original, tidak termasuk koreksi
                    transportBlokMap[blokKey].tphData[tphKey].totalKoreksi += koreksi;
                    transportBlokMap[blokKey].tphData[tphKey].totalKg += kg;
                    transportBlokMap[blokKey].tphData[tphKey].totalKgBerondolan += kgBrd;
                    transportBlokMap[blokKey].tphData[tphKey].activities.push(item);
                });

                Object.values(transportBlokMap).forEach(blok => {
                    blok.tphCount = Object.keys(blok.tphData).length;
                    
                    Object.values(blok.tphData).forEach(tph => {
                        tph.activities.sort((a, b) => new Date(a.date + ' ' + a.waktu) - new Date(b.date + ' ' + b.waktu));
                    });
                });

                return Object.values(transportBlokMap);
            }, [fullyFilteredTransport, normalizeTphKey]);
            
            /**
             * Data yang ditampilkan pada tampilan utama
             * Menambahkan Filter STATUS untuk RECAP
             */
            const currentDisplayData = useMemo(() => {
                if (activeTab === 'recap') {
                    let data = [];
                    if (currentView === 'blok') {
                        data = blokData;
                    } else if (currentView === 'tph' && selectedBlok) {
                        const blok = blokData.find(b => b.blok === selectedBlok);
                        data = blok ? Object.values(blok.tphData) : [];
                    }

                    // APPLY FILTER STATUS (Sesuai/Restan)
                    if (filters.status) {
                        data = data.filter(item => {
                            const restan = item.restan || 0;
                            if (filters.status === 'Restan') return restan > 0;
                            if (filters.status === 'Sesuai') return restan === 0;
                            // Untuk opsi lain (Delay dll) bisa ditambahkan logikanya di sini
                            if (filters.status.includes('Restan') && restan > 0) return true;
                            return true;
                        });
                    }
                    return data;

                } else if (activeTab === 'harvest') {
                    if (harvestView === 'blok') {
                        return harvestBlokData;
                    } else if (harvestView === 'tph' && selectedHarvestBlok) {
                        const blok = harvestBlokData.find(b => b.blok === selectedHarvestBlok);
                        return blok ? Object.values(blok.tphData) : [];
                    }
                } else if (activeTab === 'transport') {
                    // Logic baru untuk Transport View
                    if (transportView === 'blok') {
                        return transportBlokData;
                    } else if (transportView === 'tph' && selectedTransportBlok) {
                        const blok = transportBlokData.find(b => b.blok === selectedTransportBlok);
                        return blok ? Object.values(blok.tphData) : [];
                    }
                }
                return [];
            }, [activeTab, blokData, currentView, selectedBlok, harvestBlokData, harvestView, selectedHarvestBlok, filters.status, transportBlokData, transportView, selectedTransportBlok]);
            
            // Functions untuk navigasi
            const handleBlokClick = (blokKey) => {
                setSelectedBlok(blokKey);
                setCurrentView('tph');
            };
            
            const handleTphClick = (tph) => {
                setSelectedTph(tph);
                setShowActivityDetail(true);
            };
            
            const handleBackToBlok = () => {
                setCurrentView('blok');
                setSelectedBlok(null);
                setSelectedTph(null);
            };
            
            const handleCloseDetail = () => {
                setShowActivityDetail(false);
                setSelectedTph(null);
                setSelectedHarvestTph(null);
                setSelectedTransportTph(null);
            };
            
            // Harvest navigation functions
            const handleHarvestBlokClick = (blokKey) => {
                setSelectedHarvestBlok(blokKey);
                setHarvestView('tph');
            };
            
            const handleHarvestTphClick = (tph) => {
                setSelectedHarvestTph(tph);
                setShowActivityDetail(true);
            };
            
            const handleBackToHarvestBlok = () => {
                setHarvestView('blok');
                setSelectedHarvestBlok(null);
                setSelectedHarvestTph(null);
            };

            // Transport navigation functions (Baru)
            const handleTransportBlokClick = (blokKey) => {
                setSelectedTransportBlok(blokKey);
                setTransportView('tph');
            };

            const handleTransportTphClick = (tph) => {
                setSelectedTransportTph(tph);
                setShowActivityDetail(true);
            };

            const handleBackToTransportBlok = () => {
                setTransportView('blok');
                setSelectedTransportBlok(null);
                setSelectedTransportTph(null);
            };
            
            
            // Filter data berdasarkan tab aktif
            const finalDisplayData = useMemo(() => {
                if (activeTab === 'recap') {
                    return currentDisplayData;
                } else if (activeTab === 'harvest') {
                    if (harvestView === 'blok' || harvestView === 'tph') {
                        return currentDisplayData;
                    } else {
                        return fullyFilteredHarvest;
                    }
                } else if (activeTab === 'transport') {
                    if (transportView === 'blok' || transportView === 'tph') {
                        return currentDisplayData;
                    } else {
                        return fullyFilteredTransport;
                    }
                }
                return [];
            }, [activeTab, currentDisplayData, harvestView, fullyFilteredHarvest, fullyFilteredTransport, transportView]);

            // Fungsi untuk sorting
            const handleSort = (key) => {
                let direction = 'asc';
                if (sortConfig.key === key && sortConfig.direction === 'asc') {
                    direction = 'desc';
                }
                setSortConfig({ key, direction });
            };

            // Fungsi untuk sort data
            const getSortedData = (data) => {
                if (!sortConfig.key) {
                    return sortData([...data]); // Return default sorting
                }
                
                return [...data].sort((a, b) => {
                    let aValue = a[sortConfig.key];
                    let bValue = b[sortConfig.key];
                    
                    // Handle null/undefined values
                    if (aValue == null) aValue = '';
                    if (bValue == null) bValue = '';
                    
                    // Convert to string for comparison
                    aValue = String(aValue);
                    bValue = String(bValue);
                    
                    let result = 0;
                    
                    // Check if values are dates
                    if (aValue.match(/^\d{4}-\d{2}-\d{2}/) && bValue.match(/^\d{4}-\d{2}-\d{2}/)) {
                        result = new Date(aValue) - new Date(bValue);
                    }
                    // Check if values are numbers
                    else if (!isNaN(Number(aValue)) && !isNaN(Number(bValue))) {
                        result = Number(aValue) - Number(bValue);
                    }
                    // String comparison with natural sorting for TPH numbers
                    else {
                        result = aValue.localeCompare(bValue, undefined, { numeric: true });
                    }
                    
                    return sortConfig.direction === 'asc' ? result : -result;
                });
            };

            // Update finalDisplayData untuk menggunakan sorting
            const sortedDisplayData = useMemo(() => {
                return getSortedData(finalDisplayData);
            }, [finalDisplayData, sortConfig]);

            const handleExport = () => {
                // 1. Tentukan sumber data dan Header
                let dataToExport = [];
                let headers = [];
                let rows = [];
                let fileName = "";

                if (activeTab === 'harvest') {
                    // TAB PANEN (Logika tetap)
                    if (harvestView === 'blok') {
                        dataToExport = sortedDisplayData;
                        fileName = `Laporan_Panen_Per_Blok_${filters.startDate || 'All'}_sd_${filters.endDate || 'Today'}`;
                        headers = [
                            'Afdeling', 'Blok', 'Jml TPH', 'Avg BJR', 
                            'Matang', 'Mengkal', 'Mentah', 'Lewat Matang', 'Abnormal', 'Hama', 'Tangkai Pjg', 'Jjg Kosong', 
                            'Brondolan (Kg)', 'Total Kg', 'Koreksi', 'Total Janjang'
                        ];
                        
                         rows = dataToExport.map(item => {
                            const summary = item.tphData ? Object.values(item.tphData).reduce((acc, tph) => {
                                tph.activities.forEach(act => {
                                    acc.koreksi += parseInt(act.koreksiPanen) || 0;
                                    acc.matang += parseInt(act.matang) || 0;
                                    acc.mengkal += parseInt(act.mengkal) || 0;
                                    acc.mentah += parseInt(act.mentah) || 0;
                                    acc.lewat += parseInt(act.lewatMatang) || 0;
                                    acc.abn += parseInt(act.abnormal) || 0;
                                    acc.hama += parseInt(act.seranganHama) || 0;
                                    acc.tgkai += parseInt(act.tangkaiPanjang) || 0;
                                    acc.kosong += parseInt(act.janjangKosong) || 0;
                                    acc.brd += parseFloat(act.kgBerondolan) || 0;
                                });
                                return acc;
                            }, { koreksi:0, matang:0, mengkal:0, mentah:0, lewat:0, abn:0, hama:0, tgkai:0, kosong:0, brd:0 }) 
                            : { koreksi:0, matang:0, mengkal:0, mentah:0, lewat:0, abn:0, hama:0, tgkai:0, kosong:0, brd:0 };

                            return [
                                item.afdeling || '-', item.blokName, item.tphCount, item.avgBjr,
                                summary.matang, summary.mengkal, summary.mentah, summary.lewat, summary.abn, summary.hama, summary.tgkai, summary.kosong,
                                summary.brd.toFixed(2), item.totalKg.toFixed(2), summary.koreksi, item.totalJanjang
                            ];
                        });

                    } else {
                        // DETAIL / TPH VIEW
                        dataToExport = fullyFilteredHarvest; 
                        dataToExport.sort((a, b) => (a.date || '').localeCompare(b.date || '') || (a.blok || '').localeCompare(b.blok || ''));
                        
                        fileName = `Laporan_Panen_Detail_${selectedHarvestBlok || 'All'}_${filters.startDate || 'All'}`;
                        headers = ['Tanggal', 'Afdeling', 'Blok', 'TPH', 'Pemanen', 'NIK', 'Kerani', 'Janjang Awal', 'Koreksi', 'Janjang Akhir', 'BJR', 'Kg Total', 'Matang', 'Mengkal', 'Mentah', 'Lewat Matang', 'Abnormal', 'Hama', 'Tangkai Pjg', 'Jjg Kosong', 'Brondolan (Kg)', 'Ancak'];
                        
                        rows = dataToExport.map(item => [
                            item.date, item.afdeling, item.blok, item.noTPH, item.namaPemanen, item.nikPemanen, item.namaKerani, 
                            item.jumlahJanjang, item.koreksiPanen || 0, (parseInt(item.jumlahJanjang) || 0) + (parseInt(item.koreksiPanen) || 0),
                            item.bjr || 0, item.kgTotal || 0, item.matang || 0, item.mengkal || 0, item.mentah || 0, item.lewatMatang || 0, item.abnormal || 0, item.seranganHama || 0, item.tangkaiPanjang || 0, item.janjangKosong || 0, item.kgBerondolan || 0, item.noAncak
                        ]);
                    }
                    
                } else if (activeTab === 'recap') {
                    fileName = `Laporan_Restan_${filters.startDate || 'All'}`;
                    dataToExport = sortedDisplayData; 

                    const isBlockView = currentView === 'blok';
                    
                    if (isBlockView) {
                        headers = ['Tanggal', 'Afdeling', 'Blok', 'TPH', 'Jjg Panen', 'Jjg Angkut', 'Restan Jjg', 'Status'];
                    } else {
                        headers = ['Tanggal', 'Afdeling', 'Blok', 'TPH', 'Pemanen', 'Jjg Panen', 'Jjg Angkut', 'Restan Jjg', 'Status'];
                    }
                    
                    rows = dataToExport.map(item => {
                        const exportDate = item.date || filters.startDate || new Date().toLocaleDateString('id-ID');
                        const cleanBlok = item.blok || item.blokName || '-'; 
                        const panen = item.totalPanen !== undefined ? item.totalPanen : (item.panenJanjang || 0);
                        const kirim = item.totalKirim !== undefined ? item.totalKirim : (item.transportJanjang || 0);
                        const restanJjg = item.restan !== undefined ? item.restan : Math.max(0, panen - kirim);
                        const infoTPH = (item.tphCount && isBlockView) ? `Total ${item.tphCount} TPH` : (item.tph || item.noTPH || '-');
                        
                        const statusText = restanJjg > 0 ? 'Restan' : 'Tuntas';

                        if (isBlockView) {
                            return [exportDate, item.afdeling || '-', cleanBlok, infoTPH, panen, kirim, restanJjg, statusText];
                        } else {
                            const infoPemanen = item.pemanen || '-';
                            return [exportDate, item.afdeling || '-', cleanBlok, infoTPH, infoPemanen, panen, kirim, restanJjg, statusText];
                        }
                    });

                } else {
                    // TAB TRANSPORT (LOGIKA BARU)
                    fileName = `Laporan_Pengiriman_${filters.startDate || 'All'}`;
                    
                    if (transportView === 'blok') {
                        // Export Summary per Blok
                        dataToExport = sortedDisplayData;
                        headers = ['Afdeling', 'Blok', 'Jml TPH', 'Total Janjang', 'Total Kg', 'Total Kg Brondolan'];
                        rows = dataToExport.map(item => [
                            item.afdeling, item.blokName, item.tphCount, item.totalJanjang, item.totalKg.toFixed(2), item.totalKgBerondolan.toFixed(2)
                        ]);
                    } else {
                        // Export Detail
                        dataToExport = fullyFilteredTransport;
                        dataToExport.sort((a, b) => (a.date || '').localeCompare(b.date || '') || (a.blok || '').localeCompare(b.blok || ''));
                        
                        headers = ['Tanggal', 'Waktu', 'Afdeling', 'Blok', 'TPH', 'Nopol', 'No Kend', 'Janjang Muat', 'Koreksi', 'Net Janjang', 'BJR', 'Kg Total', 'Kg Berondolan', 'Koordinat'];
                        rows = dataToExport.map(item => [
                            item.date, item.waktu, item.afdeling, item.blok, item.noTPH, item.nopol, item.noKend,
                            item.jumlahJanjang, item.koreksiKirim || 0, (parseInt(item.jumlahJanjang)||0) + (parseInt(item.koreksiKirim)||0),
                            item.bjr || 0, item.kgTotal || 0, item.kgBerondolan || 0, item.koordinat
                        ]);
                    }
                }

                if (rows.length === 0) {
                    alert("Tidak ada data untuk diexport!");
                    return;
                }

                // Generate Excel Blob
                const tableContent = `
                    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                    <head><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/><style>td { mso-number-format:"\@"; }</style></head>
                    <body><table border="1"><thead><tr style="background-color: #4ade80; font-weight: bold; color: white;">${headers.map(h => `<th>${h}</th>`).join('')}</tr></thead><tbody>${rows.map(row => `<tr>${row.map(cell => `<td>${cell === null || cell === undefined ? '' : cell}</td>`).join('')}</tr>`).join('')}</tbody></table></body></html>
                `;
                const blob = new Blob([tableContent], { type: 'application/vnd.ms-excel' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.setAttribute("href", url);
                link.setAttribute("download", `${fileName}.xls`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            const totals = useMemo(() => {
                if (activeTab === 'recap' && currentView === 'blok') {
                    const totalPanen = blokData.reduce((sum, item) => sum + (item.totalPanen || 0), 0);
                    const totalKirim = blokData.reduce((sum, item) => sum + (item.totalKirim || 0), 0);
                    const totalRestan = blokData.reduce((sum, item) => sum + (item.restan || 0), 0);
                    
                    return {
                        panen: totalPanen,
                        kirim: totalKirim,
                        restan: totalRestan,
                        blokCount: blokData.length,
                        tphCount: blokData.reduce((sum, item) => sum + (item.tphCount || 0), 0)
                    };
                } else if (activeTab === 'harvest') {
                    if (harvestView === 'blok') {
                        const totalJanjang = harvestBlokData.reduce((sum, item) => sum + (item.totalJanjang || 0), 0);
                        const totalKg = harvestBlokData.reduce((sum, item) => sum + (item.totalKg || 0), 0);
                        const avgBjr = totalJanjang > 0 ? (totalKg / totalJanjang).toFixed(2) : 0;
                        
                        return {
                            totalJanjang: totalJanjang,
                            totalKg: totalKg,
                            avgBjr: avgBjr,
                            blokCount: harvestBlokData.length,
                            tphCount: harvestBlokData.reduce((sum, item) => sum + (item.tphCount || 0), 0)
                        };
                    } else {
                        const totalJanjang = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.jumlahJanjang || item.janjang) || 0), 0);
                        const totalKg = finalDisplayData.reduce((sum, item) => sum + (parseFloat(item.kgTotal || item.totalKg) || 0), 0);
                        const avgBjr = totalJanjang > 0 ? (totalKg / totalJanjang).toFixed(2) : 0;
                        
                        const matang = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.matang) || 0), 0);
                        const mengkal = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.mengkal) || 0), 0);
                        const mentah = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.mentah) || 0), 0);
                        const lewat = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.lewatMatang) || 0), 0);
                        const abn = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.abnormal) || 0), 0);
                        const hama = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.seranganHama) || 0), 0);
                        const tgkai = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.tangkaiPanjang) || 0), 0);
                        const kosong = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.janjangKosong) || 0), 0);
                        const brondolan = finalDisplayData.reduce((sum, item) => sum + (parseFloat(item.kgBerondolan) || 0), 0);

                        return {
                            totalJanjang, totalKg, avgBjr,
                            matang, mengkal, mentah, lewat, abn, hama, tgkai, kosong, brondolan
                        };
                    }
                } else if (activeTab === 'transport') {
                    // Logic Totals untuk Transport
                    if (transportView === 'blok') {
                        const totalJanjang = transportBlokData.reduce((sum, item) => sum + (item.totalJanjang || 0), 0);
                        const totalKg = transportBlokData.reduce((sum, item) => sum + (item.totalKg || 0), 0);
                        const avgBjr = totalJanjang > 0 ? (totalKg / totalJanjang).toFixed(2) : 0;
                        
                        return {
                            totalJanjang: totalJanjang,
                            totalKg: totalKg,
                            avgBjr: avgBjr,
                            blokCount: transportBlokData.length,
                            tphCount: transportBlokData.reduce((sum, item) => sum + (item.tphCount || 0), 0)
                        };
                    } else {
                        const totalJanjang = finalDisplayData.reduce((sum, item) => sum + (parseInt(item.jumlahJanjang || item.janjang) || 0), 0);
                        const totalKg = finalDisplayData.reduce((sum, item) => sum + (parseFloat(item.kgTotal || item.totalKg) || 0), 0);
                        const kgBerondolan = finalDisplayData.reduce((sum, item) => sum + (parseFloat(item.kgBerondolan) || 0), 0); 
                        const avgBjr = totalJanjang > 0 ? (totalKg / totalJanjang).toFixed(2) : 0;
                        
                        return {
                            totalJanjang: totalJanjang,
                            totalKg: totalKg,
                            avgBjr: avgBjr,
                            kgBerondolan: kgBerondolan 
                        };
                    }
                }
                return null;
            }, [activeTab, currentView, harvestView, blokData, harvestBlokData, finalDisplayData, transportView, transportBlokData]);

            // Komponen Header yang bisa di-sort
            const SortableHeader = ({ children, sortKey, className = "" }) => (
                <th 
                    className={`py-3 px-4 text-center bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors select-none ${className}`}
                    onClick={() => handleSort(sortKey)}
                    title={`Klik untuk sort berdasarkan ${children}`}
                >
                    <div className="flex items-center justify-center gap-1">
                        <span>{children}</span>
                        {sortConfig.key === sortKey && (
                            <span className="text-blue-600 font-bold">
                                {sortConfig.direction === 'asc' ? '↑' : '↓'}
                            </span>
                        )}
                        {sortConfig.key !== sortKey && (
                            <span className="text-gray-400 opacity-50">↕</span>
                        )}
                    </div>
                </th>
            );

            const RecapTable = () => {
                if (currentView === 'blok') {
                    return (
                        <div className="overflow-x-auto">
                            <div className="bg-blue-50 p-4 border-b">
                                <h3 className="text-lg font-semibold text-gray-700">📍 Data Per Blok</h3>
                                <p className="text-sm text-gray-600">Klik pada blok untuk melihat detail TPH. Klik kolom header untuk sorting.</p>
                            </div>
                            <table className="w-full text-sm text-left">
                                <thead className="bg-gray-50 text-gray-600 font-medium border-b">
                                    <tr>
                                        <SortableHeader sortKey="blok">Blok</SortableHeader>
                                        <SortableHeader sortKey="totalPanen" className="bg-green-50">Panen Jjg</SortableHeader>
                                        <SortableHeader sortKey="totalKirim" className="bg-blue-50">Kirim Jjg</SortableHeader>
                                        <SortableHeader sortKey="restan" className="bg-red-50">Restan</SortableHeader>
                                        <SortableHeader sortKey="tphCount">Jumlah TPH</SortableHeader>
                                        <th className="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {sortedDisplayData.map((blok, idx) => (
                                        <tr key={idx} className="hover:bg-gray-50 cursor-pointer group">
                                            <td className="py-3 px-4 font-medium text-gray-700">
                                                <div className="flex items-center gap-2">
                                                    <span>{blok.blok}</span>
                                                    {blok.maxDelay > 0 && (
                                                        <span className="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full font-medium border border-red-200">
                                                            Delay: {blok.maxDelay} hari
                                                        </span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="py-3 px-4 text-center bg-green-50/50 font-medium text-green-700">
                                                {blok.totalPanen.toLocaleString()}
                                            </td>
                                            <td className="py-3 px-4 text-center bg-blue-50/50 font-medium text-blue-700">
                                                {blok.totalKirim.toLocaleString()}
                                            </td>
                                            <td className="py-3 px-4 text-center bg-red-50/50 font-medium text-red-700">
                                                {blok.restan.toLocaleString()}
                                            </td>
                                            <td className="py-3 px-4 text-center text-gray-600">
                                                {blok.tphCount} TPH
                                            </td>
                                            <td className="py-3 px-4 text-center">
                                                <button 
                                                    onClick={() => handleBlokClick(blok.blok)}
                                                    className="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded hover:bg-blue-200 transition-colors"
                                                >
                                                    Lihat TPH
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    );
                } else if (currentView === 'tph') {
                    return (
                        <div className="overflow-x-auto">
                            <div className="bg-green-50 p-4 border-b">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-700">🌴 TPH di Blok: {selectedBlok}</h3>
                                        <p className="text-sm text-gray-600">Klik pada TPH untuk melihat detail aktivitas. Klik kolom header untuk sorting.</p>
                                    </div>
                                    <button 
                                        onClick={handleBackToBlok}
                                        className="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200 transition-colors"
                                    >
                                        ← Kembali ke Blok
                                    </button>
                                </div>
                            </div>
                            <table className="w-full text-sm text-left">
                                <thead className="bg-gray-50 text-gray-600 font-medium border-b">
                                    <tr>
                                        <SortableHeader sortKey="tph">TPH</SortableHeader>
                                        <SortableHeader sortKey="totalPanen" className="bg-green-50">Panen Jjg</SortableHeader>
                                        <SortableHeader sortKey="totalKirim" className="bg-blue-50">Kirim Jjg</SortableHeader>
                                        <SortableHeader sortKey="restan" className="bg-red-50">Restan</SortableHeader>
                                        <th className="py-3 px-4 text-center">Aktivitas</th>
                                        <th className="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {sortedDisplayData.map((tph, idx) => (
                                        <tr key={idx} className="hover:bg-gray-50 cursor-pointer group">
                                            <td className="py-3 px-4 font-medium text-gray-700">
                                                <div className="flex items-center gap-2">
                                                    <span>{tph.tph}</span>
                                                    {tph.delay > 0 && (
                                                        <span className="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full font-medium border border-red-200">
                                                            Delay: {tph.delay} hari
                                                        </span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="py-3 px-4 text-center bg-green-50/50 font-medium text-green-700">
                                                {tph.totalPanen.toLocaleString()}
                                            </td>
                                            <td className="py-3 px-4 text-center bg-blue-50/50 font-medium text-blue-700">
                                                {tph.totalKirim.toLocaleString()}
                                            </td>
                                            <td className="py-3 px-4 text-center bg-red-50/50 font-medium text-red-700">
                                                {tph.restan.toLocaleString()}
                                            </td>
                                            <td className="py-3 px-4 text-center text-gray-600">
                                                {tph.activities.length} record
                                            </td>
                                            <td className="py-3 px-4 text-center">
                                                <button 
                                                    onClick={() => handleTphClick(tph)}
                                                    className="px-3 py-1 bg-green-100 text-green-700 text-xs rounded hover:bg-green-200 transition-colors"
                                                >
                                                    Lihat Detail
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    );
                }
                return null;
            };

            const HarvestTable = () => {
                if (harvestView === 'blok') {
                    // --- TAMPILAN BLOK ---
                    return (
                        <div className="overflow-x-auto">
                            <div className="bg-green-50 p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-700">🌱 Data Panen Per Blok</h3>
                                        <p className="text-sm text-gray-600">Total akumulasi data panen dan kualitas buah per blok</p>
                                    </div>
                                </div>
                            </div>
                            <table className="w-full text-sm text-left">
                                <thead className="bg-green-50 text-green-800 font-medium border-b">
                                    <tr>
                                        <SortableHeader sortKey="blok">Blok</SortableHeader>
                                        <SortableHeader sortKey="tphCount">Jml TPH</SortableHeader>
                                        
                                        {/* GRADING */}
                                        <th className="py-3 px-2 text-center bg-gray-100 border-l" title="Matang">Mtg</th>
                                        <th className="py-3 px-2 text-center bg-gray-100" title="Mengkal">Mkl</th>
                                        <th className="py-3 px-2 text-center bg-gray-100" title="Mentah">Mth</th>
                                        <th className="py-3 px-2 text-center bg-gray-100" title="Lewat Matang">Lwt</th>
                                        <th className="py-3 px-2 text-center bg-gray-100" title="Abnormal">Abn</th>
                                        <th className="py-3 px-2 text-center bg-gray-100" title="Serangan Hama">Hama</th>
                                        <th className="py-3 px-2 text-center bg-gray-100" title="Tangkai Panjang">Tgkai</th>
                                        <th className="py-3 px-2 text-center bg-gray-100 border-r" title="Janjang Kosong">Ksg</th>

                                        {/* KALKULASI UTAMA */}
                                        <SortableHeader sortKey="avgBjr">Avg BJR</SortableHeader>
                                        <th className="py-3 px-2 text-center bg-yellow-50" title="Brondolan (Kg)">Brd (Kg)</th>
                                        <SortableHeader sortKey="totalKg" className="bg-blue-50">Total Kg</SortableHeader>
                                        <th className="py-3 px-4 text-center bg-green-100 text-green-800">Koreksi</th>
                                        <SortableHeader sortKey="totalJanjang" className="bg-green-50">Total Jjg</SortableHeader>
                                        
                                        <th className="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {sortedDisplayData.map((item, index) => {
                                        const summary = item.tphData ? Object.values(item.tphData).reduce((acc, tph) => {
                                            tph.activities.forEach(act => {
                                                acc.koreksi += parseInt(act.koreksiPanen) || 0;
                                                acc.matang += parseInt(act.matang) || 0;
                                                acc.mengkal += parseInt(act.mengkal) || 0;
                                                acc.mentah += parseInt(act.mentah) || 0;
                                                acc.lewat += parseInt(act.lewatMatang) || 0;
                                                acc.abn += parseInt(act.abnormal) || 0;
                                                acc.hama += parseInt(act.seranganHama) || 0;
                                                acc.tgkai += parseInt(act.tangkaiPanjang) || 0;
                                                acc.kosong += parseInt(act.janjangKosong) || 0;
                                                acc.brd += parseFloat(act.kgBerondolan) || 0;
                                            });
                                            return acc;
                                        }, { koreksi:0, matang:0, mengkal:0, mentah:0, lewat:0, abn:0, hama:0, tgkai:0, kosong:0, brd:0 }) 
                                        : { koreksi:0, matang:0, mengkal:0, mentah:0, lewat:0, abn:0, hama:0, tgkai:0, kosong:0, brd:0 };

                                        return (
                                            <tr key={index} className="hover:bg-green-50 transition-colors">
                                                <td className="py-3 px-4 font-medium text-gray-900">{item.blokName}</td>
                                                <td className="py-3 px-4 text-center text-gray-600">{item.tphCount}</td>
                                                
                                                {/* GRADING */}
                                                <td className="py-3 px-2 text-center text-gray-600 border-l">{summary.matang}</td>
                                                <td className="py-3 px-2 text-center text-gray-600">{summary.mengkal}</td>
                                                <td className="py-3 px-2 text-center text-red-600 font-medium">{summary.mentah}</td>
                                                <td className="py-3 px-2 text-center text-gray-600">{summary.lewat}</td>
                                                <td className="py-3 px-2 text-center text-gray-600">{summary.abn}</td>
                                                <td className="py-3 px-2 text-center text-gray-600">{summary.hama}</td>
                                                <td className="py-3 px-2 text-center text-gray-600">{summary.tgkai}</td>
                                                <td className="py-3 px-2 text-center text-gray-600 border-r">{summary.kosong}</td>

                                                {/* KALKULASI */}
                                                <td className="py-3 px-4 text-center text-orange-600 font-medium">{item.avgBjr}</td>
                                                <td className="py-3 px-2 text-center text-yellow-700 font-medium bg-yellow-50/50">{summary.brd.toFixed(1)}</td>
                                                <td className="py-3 px-4 text-center text-blue-700 font-semibold bg-blue-50/30">{item.totalKg.toFixed(2)}</td>
                                                <td className="py-3 px-4 text-center font-medium bg-green-100/50">
                                                    <span className={summary.koreksi !== 0 ? (summary.koreksi > 0 ? "text-green-600" : "text-red-600") : "text-gray-400"}>
                                                        {summary.koreksi > 0 ? `+${summary.koreksi}` : summary.koreksi}
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4 text-center text-green-700 font-bold bg-green-50/30">{item.totalJanjang}</td>

                                                <td className="py-3 px-4 text-center">
                                                    <button 
                                                        onClick={() => handleHarvestBlokClick(item.blok)}
                                                        className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors"
                                                    >
                                                        Lihat TPH
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                                {/* FOOTER TOTALS BLOK */}
                                {sortedDisplayData.length > 0 && (() => {
                                    const totals = sortedDisplayData.reduce((acc, item) => {
                                        if(item.tphData) {
                                            Object.values(item.tphData).forEach(tph => {
                                                tph.activities.forEach(act => {
                                                    acc.koreksi += parseInt(act.koreksiPanen) || 0;
                                                    acc.matang += parseInt(act.matang) || 0;
                                                    acc.mengkal += parseInt(act.mengkal) || 0;
                                                    acc.mentah += parseInt(act.mentah) || 0;
                                                    acc.lewat += parseInt(act.lewatMatang) || 0;
                                                    acc.abn += parseInt(act.abnormal) || 0;
                                                    acc.hama += parseInt(act.seranganHama) || 0;
                                                    acc.tgkai += parseInt(act.tangkaiPanjang) || 0;
                                                    acc.kosong += parseInt(act.janjangKosong) || 0;
                                                    acc.brd += parseFloat(act.kgBerondolan) || 0;
                                                });
                                            });
                                        }
                                        acc.janjang += item.totalJanjang;
                                        acc.kg += item.totalKg;
                                        acc.tph += item.tphCount;
                                        return acc;
                                    }, { janjang:0, kg:0, tph:0, koreksi:0, matang:0, mengkal:0, mentah:0, lewat:0, abn:0, hama:0, tgkai:0, kosong:0, brd:0 });
                                    
                                    const avgBjr = totals.janjang > 0 ? (totals.kg / totals.janjang).toFixed(2) : '0.00';
                                    
                                    return (
                                        <tfoot className="bg-green-200 text-green-800 font-bold border-t-2 border-green-300 text-xs">
                                            <tr>
                                                <td className="py-3 px-4 font-bold">TOTAL:</td>
                                                <td className="py-3 px-4 text-center">{totals.tph}</td>
                                                
                                                <td className="py-3 px-2 text-center border-l border-green-300">{totals.matang}</td>
                                                <td className="py-3 px-2 text-center">{totals.mengkal}</td>
                                                <td className="py-3 px-2 text-center text-red-800">{totals.mentah}</td>
                                                <td className="py-3 px-2 text-center">{totals.lewat}</td>
                                                <td className="py-3 px-2 text-center">{totals.abn}</td>
                                                <td className="py-3 px-2 text-center">{totals.hama}</td>
                                                <td className="py-3 px-2 text-center">{totals.tgkai}</td>
                                                <td className="py-3 px-2 text-center border-r border-green-300">{totals.kosong}</td>
                                                
                                                <td className="py-3 px-4 text-center bg-orange-200 text-orange-800">{avgBjr}</td>
                                                <td className="py-3 px-2 text-center bg-yellow-200">{totals.brd.toFixed(1)}</td>
                                                <td className="py-3 px-4 text-center bg-blue-200 text-blue-800">{totals.kg.toFixed(2)}</td>
                                                <td className="py-3 px-4 text-center bg-green-100">{totals.koreksi > 0 ? `+${totals.koreksi}` : totals.koreksi}</td>
                                                <td className="py-3 px-4 text-center bg-green-300">{totals.janjang.toLocaleString()}</td>
                                                
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    );
                                })()}
                            </table>
                        </div>
                    );
                } else if (harvestView === 'tph') {
                    // --- TAMPILAN TPH ---
                    return (
                        <div className="overflow-x-auto">
                            <div className="bg-green-50 p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-700">🌱 TPH Panen di Blok: {selectedHarvestBlok ? selectedHarvestBlok.replace(/^\d+\s*/, '') : ''}</h3>
                                        <p className="text-sm text-gray-600">Klik pada TPH untuk melihat detail aktivitas lengkap</p>
                                    </div>
                                    <div className="flex gap-2">
                                        <button 
                                            onClick={handleBackToHarvestBlok}
                                            className="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200 transition-colors"
                                        >
                                            ← Kembali ke Blok
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <table className="w-full text-sm text-left">
                                <thead className="bg-green-50 text-green-800 font-medium border-b">
                                    <tr>
                                        <SortableHeader sortKey="tph">TPH</SortableHeader>
                                        <th className="py-3 px-4 text-center">Pemanen</th>
                                        <th className="py-3 px-4 text-center">Kerani</th>
                                        
                                        <SortableHeader sortKey="avgBjr">BJR</SortableHeader>
                                        <th className="py-3 px-4 text-center">Brd (Kg)</th>
                                        <SortableHeader sortKey="totalKg">Total Kg</SortableHeader>
                                        
                                        <th className="py-3 px-4 text-center bg-green-100 text-green-800">Koreksi</th>
                                        
                                        <SortableHeader sortKey="totalJanjang">Total Jjg</SortableHeader>
                                        
                                        <th className="py-3 px-4 text-center">Aktivitas</th>
                                        <th className="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {sortedDisplayData.map((item, index) => {
                                        const summary = item.activities.reduce((sum, activity) => {
                                            sum.brondolan += parseFloat(activity.kgBerondolan) || 0;
                                            sum.koreksi += parseInt(activity.koreksiPanen) || 0;
                                            return sum;
                                        }, {brondolan: 0, koreksi: 0});

                                        return (
                                            <tr key={index} className="hover:bg-green-50 transition-colors">
                                                <td className="py-3 px-4 font-medium text-gray-900">{item.tph}</td>
                                                <td className="py-3 px-4 text-center text-gray-600 text-xs">
                                                    <div className="max-w-20 truncate" title={item.pemanen.join(', ')}>
                                                        {item.pemanen.slice(0, 2).join(', ')}{item.pemanen.length > 2 ? ` +${item.pemanen.length - 2}` : ''}
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4 text-center text-gray-600 text-xs">
                                                    <div className="max-w-16 truncate" title={item.kerani.join(', ')}>
                                                        {item.kerani.slice(0, 1).join('')}{item.kerani.length > 1 ? ` +${item.kerani.length - 1}` : ''}
                                                    </div>
                                                </td>
                                                
                                                <td className="py-3 px-4 text-center text-orange-600 font-medium">{item.avgBjr}</td>
                                                <td className="py-3 px-4 text-center text-yellow-700 font-medium text-xs">
                                                    {summary.brondolan.toFixed(1)}
                                                </td>
                                                <td className="py-3 px-4 text-center text-blue-700 font-semibold">{item.totalKg.toFixed(2)}</td>
                                                
                                                <td className="py-3 px-4 text-center font-medium bg-green-100/50">
                                                    <span className={summary.koreksi !== 0 ? (summary.koreksi > 0 ? "text-green-600" : "text-red-600") : "text-gray-400"}>
                                                        {summary.koreksi > 0 ? `+${summary.koreksi}` : summary.koreksi}
                                                    </span>
                                                </td>

                                                <td className="py-3 px-4 text-center text-green-700 font-bold">{item.totalJanjang}</td>
                                                
                                                <td className="py-3 px-4 text-center text-gray-600">{item.activities.length} rec</td>
                                                <td className="py-3 px-4 text-center">
                                                    <button onClick={() => handleHarvestTphClick(item)} className="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">Lihat Detail</button>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                                {/* FOOTER TOTALS TPH */}
                                {sortedDisplayData.length > 0 && (() => {
                                    const totals = sortedDisplayData.reduce((sum, item) => {
                                        sum.janjang += item.totalJanjang;
                                        sum.kg += item.totalKg;
                                        sum.records += item.activities.length;
                                        
                                        item.activities.forEach(activity => {
                                            sum.brondolan += parseFloat(activity.kgBerondolan) || 0;
                                            sum.koreksi += parseInt(activity.koreksiPanen) || 0;
                                        });
                                        return sum;
                                    }, { janjang: 0, kg: 0, records: 0, brondolan: 0, koreksi: 0 });
                                    
                                    const avgBjr = totals.janjang > 0 ? (totals.kg / totals.janjang).toFixed(2) : '0.00';
                                    
                                    return (
                                        <tfoot className="bg-green-200 text-green-800 font-bold border-t-2 border-green-300">
                                            <tr>
                                                <td className="py-3 px-4 font-bold">TOTAL:</td>
                                                <td colSpan="2"></td> 
                                                
                                                <td className="py-3 px-4 text-center bg-orange-200 text-orange-800">{avgBjr}</td>
                                                <td className="py-3 px-4 text-center bg-yellow-200 text-yellow-800">{totals.brondolan.toFixed(1)}</td>
                                                <td className="py-3 px-4 text-center bg-blue-200 text-blue-800">{totals.kg.toFixed(2)}</td>
                                                
                                                <td className="py-3 px-4 text-center bg-green-100">
                                                    {totals.koreksi > 0 ? `+${totals.koreksi}` : totals.koreksi}
                                                </td>

                                                <td className="py-3 px-4 text-center bg-green-300">{totals.janjang.toLocaleString()}</td>
                                                
                                                <td className="py-3 px-4 text-center">{totals.records}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    );
                                })()}
                            </table>
                        </div>
                    );
                }
                return null;
            };

            const TransportTable = () => {
                if (transportView === 'blok') {
                    // --- TAMPILAN BLOK (Pengiriman) ---
                    return (
                        <div className="overflow-x-auto">
                            <div className="bg-blue-50 p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-700">🚛 Data Pengiriman Per Blok</h3>
                                        <p className="text-sm text-gray-600">Total akumulasi data pengiriman TBS per blok</p>
                                    </div>
                                </div>
                            </div>
                            <table className="w-full text-sm text-left">
                                <thead className="bg-blue-50 text-blue-800 font-medium border-b">
                                    <tr>
                                        <SortableHeader sortKey="blok">Blok</SortableHeader>
                                        <SortableHeader sortKey="tphCount">Jml TPH</SortableHeader>
                                        
                                        <SortableHeader sortKey="totalJanjang" className="bg-blue-100">Total Muat (Jjg)</SortableHeader>
                                        <SortableHeader sortKey="totalKoreksi" className="bg-orange-100">Koreksi</SortableHeader>
                                        <SortableHeader sortKey="totalKg" className="bg-orange-50">Total Kg</SortableHeader>
                                        <th className="py-3 px-4 text-center bg-yellow-50">Total Kg Brondolan</th>
                                        
                                        <th className="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {sortedDisplayData.map((item, index) => (
                                        <tr key={index} className="hover:bg-blue-50 transition-colors">
                                            <td className="py-3 px-4 font-medium text-gray-900">{item.blokName}</td>
                                            <td className="py-3 px-4 text-center text-gray-600">{item.tphCount}</td>
                                            
                                            <td className="py-3 px-4 text-center text-blue-700 font-bold bg-blue-50/50">{(item.totalJanjang + item.totalKoreksi).toLocaleString()}</td>
                                            <td className="py-3 px-4 text-center font-medium bg-orange-100/50">
                                                <span className={item.totalKoreksi !== 0 ? (item.totalKoreksi > 0 ? "text-green-600" : "text-red-600") : "text-gray-400"}>
                                                    {item.totalKoreksi > 0 ? `+${item.totalKoreksi.toLocaleString()}` : item.totalKoreksi.toLocaleString()}
                                                </span>
                                            </td>
                                            <td className="py-3 px-4 text-center text-orange-700 font-medium bg-orange-50/50">{item.totalKg.toLocaleString()}</td>
                                            <td className="py-3 px-4 text-center text-yellow-700 font-medium bg-yellow-50/50">{item.totalKgBerondolan.toFixed(2)}</td>
                                            
                                            <td className="py-3 px-4 text-center">
                                                <button 
                                                    onClick={() => handleTransportBlokClick(item.blok)}
                                                    className="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors"
                                                >
                                                    Lihat TPH
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                                {/* FOOTER TOTALS BLOK */}
                                {sortedDisplayData.length > 0 && (() => {
                                    const totals = sortedDisplayData.reduce((acc, item) => {
                                        acc.janjang += item.totalJanjang;
                                        acc.koreksi += item.totalKoreksi;
                                        acc.kg += item.totalKg;
                                        acc.kgBrd += item.totalKgBerondolan;
                                        acc.tph += item.tphCount;
                                        return acc;
                                    }, { janjang:0, koreksi: 0, kg:0, kgBrd:0, tph:0 });
                                    
                                    return (
                                        <tfoot className="bg-blue-200 text-blue-800 font-bold border-t-2 border-blue-300 text-xs">
                                            <tr>
                                                <td className="py-3 px-4 font-bold">TOTAL:</td>
                                                <td className="py-3 px-4 text-center">{totals.tph}</td>
                                                
                                                <td className="py-3 px-4 text-center bg-blue-300">{(totals.janjang + totals.koreksi).toLocaleString()}</td>
                                                <td className="py-3 px-4 text-center bg-orange-200">
                                                    <span className={totals.koreksi !== 0 ? (totals.koreksi > 0 ? "text-green-800" : "text-red-800") : ""}>
                                                        {totals.koreksi > 0 ? `+${totals.koreksi.toLocaleString()}` : totals.koreksi.toLocaleString()}
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4 text-center bg-orange-200">{totals.kg.toLocaleString()}</td>
                                                <td className="py-3 px-4 text-center bg-yellow-200">{totals.kgBrd.toFixed(2)}</td>
                                                
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    );
                                })()}
                            </table>
                        </div>
                    );
                } else if (transportView === 'tph') {
                    // --- TAMPILAN TPH (Pengiriman) ---
                    return (
                        <div className="overflow-x-auto">
                            <div className="bg-blue-50 p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-700">🚛 TPH Pengiriman di Blok: {selectedTransportBlok}</h3>
                                        <p className="text-sm text-gray-600">Klik pada TPH untuk melihat detail pengangkutan</p>
                                    </div>
                                    <button 
                                        onClick={handleBackToTransportBlok}
                                        className="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200 transition-colors"
                                    >
                                        ← Kembali ke Blok
                                    </button>
                                </div>
                            </div>
                            <table className="w-full text-sm text-left">
                                <thead className="bg-blue-50 text-blue-800 font-medium border-b">
                                    <tr>
                                        <SortableHeader sortKey="tph">TPH</SortableHeader>
                                        <SortableHeader sortKey="totalJanjang" className="bg-blue-100">Total Muat (Jjg)</SortableHeader>
                                        <SortableHeader sortKey="totalKoreksi" className="bg-orange-100">Koreksi</SortableHeader>
                                        <SortableHeader sortKey="totalKg" className="bg-orange-50">Total Kg</SortableHeader>
                                        <th className="py-3 px-4 text-center bg-yellow-50">Total Kg Brondolan</th>
                                        <th className="py-3 px-4 text-center">Aktivitas</th>
                                        <th className="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {sortedDisplayData.map((item, index) => (
                                        <tr key={index} className="hover:bg-blue-50 transition-colors">
                                            <td className="py-3 px-4 font-medium text-gray-900">{item.tph}</td>
                                            <td className="py-3 px-4 text-center text-blue-700 font-bold bg-blue-50/50">{(item.totalJanjang + item.totalKoreksi).toLocaleString()}</td>
                                            <td className="py-3 px-4 text-center font-medium bg-orange-100/50">
                                                <span className={item.totalKoreksi !== 0 ? (item.totalKoreksi > 0 ? "text-green-600" : "text-red-600") : "text-gray-400"}>
                                                    {item.totalKoreksi > 0 ? `+${item.totalKoreksi.toLocaleString()}` : item.totalKoreksi.toLocaleString()}
                                                </span>
                                            </td>
                                            <td className="py-3 px-4 text-center text-orange-700 font-medium bg-orange-50/50">{item.totalKg.toLocaleString()}</td>
                                            <td className="py-3 px-4 text-center text-yellow-700 font-medium bg-yellow-50/50">{item.totalKgBerondolan.toFixed(2)}</td>
                                            <td className="py-3 px-4 text-center text-gray-600">{item.activities.length} rec</td>
                                            <td className="py-3 px-4 text-center">
                                                <button onClick={() => handleTransportTphClick(item)} className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">Lihat Detail</button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                                {/* FOOTER TOTALS TPH */}
                                {sortedDisplayData.length > 0 && (() => {
                                    const totals = sortedDisplayData.reduce((acc, item) => {
                                        acc.janjang += item.totalJanjang;
                                        acc.koreksi += item.totalKoreksi;
                                        acc.kg += item.totalKg;
                                        acc.kgBrd += item.totalKgBerondolan;
                                        acc.records += item.activities.length;
                                        return acc;
                                    }, { janjang:0, koreksi:0, kg:0, kgBrd:0, records:0 });
                                    
                                    return (
                                        <tfoot className="bg-blue-200 text-blue-800 font-bold border-t-2 border-blue-300">
                                            <tr>
                                                <td className="py-3 px-4 font-bold">TOTAL:</td>
                                                <td className="py-3 px-4 text-center bg-blue-300">{(totals.janjang + totals.koreksi).toLocaleString()}</td>
                                                <td className="py-3 px-4 text-center bg-orange-200">
                                                    <span className={totals.koreksi !== 0 ? (totals.koreksi > 0 ? "text-green-800" : "text-red-800") : ""}>
                                                        {totals.koreksi > 0 ? `+${totals.koreksi.toLocaleString()}` : totals.koreksi.toLocaleString()}
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4 text-center bg-orange-200">{totals.kg.toLocaleString()}</td>
                                                <td className="py-3 px-4 text-center bg-yellow-200">{totals.kgBrd.toFixed(2)}</td>
                                                <td className="py-3 px-4 text-center">{totals.records}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    );
                                })()}
                            </table>
                        </div>
                    );
                }
                return null;
            };

            // Modal untuk menampilkan detail aktivitas TPH
            const ActivityDetailModal = () => {
                if (!showActivityDetail || (!selectedTph && !selectedHarvestTph && !selectedTransportTph)) return null;

                const tphData = selectedTph || selectedHarvestTph || selectedTransportTph;
                const blokName = selectedBlok || selectedHarvestBlok || selectedTransportBlok;
                const isHarvest = activeTab === 'harvest';
                const isTransport = activeTab === 'transport';

                // Fungsi Export Excel Khusus Modal
                const handleModalExport = () => {
                    let headers = [];
                    let rows = [];
                    let fileName = `Detail_Aktivitas_${blokName}_TPH_${tphData.tph}`;

                    if (isHarvest) {
                        headers = [
                            'Tanggal', 'Waktu', 'Pemanen', 'NIK', 'Kerani', 
                            'Janjang', 'Koreksi', 'Total Janjang', 
                            'Berat (Kg)', 'Brondolan (Kg)', 'BJR',
                            'Matang', 'Mengkal', 'Mentah', 'Lewat', 'Abnormal', 'Hama', 'Tangkai Pjg', 'Kosong',
                            'Ancak', 'Catatan'
                        ];
                        
                        rows = tphData.activities.map(item => [
                            new Date(item.createdAt || item.tanggal || item.date).toLocaleDateString('id-ID'),
                            new Date(item.createdAt || item.tanggal || item.date).toLocaleTimeString('id-ID'),
                            item.namaPemanen, item.nikPemanen, (item.namaKerani || item.kerani),
                            item.jumlahJanjang || item.janjang,
                            item.koreksiPanen || 0,
                            (parseInt(item.jumlahJanjang || item.janjang) || 0) + (parseInt(item.koreksiPanen) || 0),
                            (item.kgTotal || item.totalKg || item.kg || 0),
                            item.kgBerondolan || 0,
                            ((item.kgTotal || item.totalKg) / (item.jumlahJanjang || 1)).toFixed(2),
                            item.matang, item.mengkal, item.mentah, item.lewatMatang, item.abnormal, item.seranganHama, item.tangkaiPanjang, item.janjangKosong,
                            item.noAncak, item.catatan
                        ]);
                    } else if (isTransport) {
                        // Header khusus detail Transport di Modal
                        headers = ['Tanggal', 'Waktu', 'Nopol', 'No Unit', 'Kerani', 'Muatan (Jjg)', 'Koreksi', 'Total Jjg', 'Koordinat'];
                        rows = tphData.activities.map(item => [
                            item.date,
                            item.waktu,
                            item.nopol,
                            item.noKend,
                            item.namaKerani,
                            item.jumlahJanjang,
                            item.koreksiKirim || 0,
                            (parseInt(item.jumlahJanjang) + parseInt(item.koreksiKirim || 0)),
                            item.koordinat
                        ]);
                    } else {
                        // Format Header untuk Restan
                        headers = ['Tanggal', 'Waktu', 'Jenis', 'Kerani', 'Unit/Driver', 'Muatan Jjg', 'Status'];
                        rows = tphData.activities.map(item => [
                            new Date(item.date).toLocaleDateString('id-ID'),
                            item.waktu || item.jam || '-',
                            item.type || 'Panen',
                            item.kerani || item.namaKerani,
                            item.nopol || item.pemanen || item.namaPemanen,
                            (parseInt(item.janjang || item.jumlahJanjang) || 0) + (item.type === 'Kirim' ? (parseInt(item.koreksiKirim) || 0) : (parseInt(item.koreksiPanen) || 0)),
                            'Selesai'
                        ]);
                    }

                    // Generate Excel File
                    const tableContent = `
                        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                        <head>
                            <meta http-equiv="content-type" content="text/plain; charset=UTF-8"/>
                            <style>td { mso-number-format:"\@"; }</style>
                        </head>
                        <body>
                            <h3>Detail Aktivitas - Blok ${blokName} - TPH ${tphData.tph}</h3>
                            <table border="1">
                                <thead>
                                    <tr style="background-color: #4ade80; font-weight: bold; color: white;">
                                        ${headers.map(h => `<th>${h}</th>`).join('')}
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows.map(row => `<tr>${row.map(cell => `<td>${cell === null || cell === undefined ? '' : cell}</td>`).join('')}</tr>`).join('')}
                                </tbody>
                            </table>
                        </body>
                        </html>
                    `;

                    const blob = new Blob([tableContent], { type: 'application/vnd.ms-excel' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement("a");
                    link.setAttribute("href", url);
                    link.setAttribute("download", `${fileName}.xls`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                };

                const enhancedSummary = useMemo(() => {
                    if (!tphData.activities) return null;
                    
                    if (isHarvest) {
                        return tphData.activities.reduce((acc, activity) => {
                            acc.janjang += (parseInt(activity.jumlahJanjang) || 0) + (parseInt(activity.koreksiPanen) || 0);
                            acc.koreksi += parseInt(activity.koreksiPanen) || 0;
                            acc.kg += parseFloat(activity.kgTotal || activity.totalKg) || 0;
                            acc.brondolan += parseFloat(activity.kgBerondolan) || 0;
                            acc.matang += parseInt(activity.matang) || 0;
                            acc.mengkal += parseInt(activity.mengkal) || 0;
                            acc.mentah += parseInt(activity.mentah) || 0;
                            acc.lewatMatang += parseInt(activity.lewatMatang) || 0;
                            acc.abnormal += parseInt(activity.abnormal) || 0;
                            acc.seranganHama += parseInt(activity.seranganHama) || 0;
                            acc.tangkaiPanjang += parseInt(activity.tangkaiPanjang) || 0;
                            acc.janjangKosong += parseInt(activity.janjangKosong) || 0;
                            
                            if (activity.namaPemanen) acc.pemanens.add(activity.namaPemanen);
                            if (activity.namaKerani) acc.keranis.add(activity.namaKerani);
                            if (activity.noAncak) acc.ancaks.add(activity.noAncak);
                            
                            return acc;
                        }, { 
                            janjang: 0, koreksi: 0, kg: 0, brondolan: 0, matang: 0, mengkal: 0, 
                            mentah: 0, lewatMatang: 0, abnormal: 0, seranganHama: 0, 
                            tangkaiPanjang: 0, janjangKosong: 0,
                            pemanens: new Set(), keranis: new Set(), ancaks: new Set()
                        });
                    } else if (isTransport) {
                        return tphData.activities.reduce((acc, activity) => {
                            acc.janjang += (parseInt(activity.jumlahJanjang || activity.janjang) || 0) + (parseInt(activity.koreksiKirim) || 0);
                            acc.koreksi += parseInt(activity.koreksiKirim) || 0;
                            acc.kg += parseFloat(activity.kgTotal || activity.totalKg) || 0;
                            acc.brondolan += parseFloat(activity.kgBerondolan || activity.kgBrd) || 0;
                            
                            if (activity.namaKerani) acc.keranis.add(activity.namaKerani);
                            if (activity.nopol) acc.nopols.add(activity.nopol);
                            
                            return acc;
                        }, { 
                            janjang: 0, koreksi: 0, kg: 0, brondolan: 0,
                            keranis: new Set(), nopols: new Set()
                        });
                    }
                    
                    return null;
                }, [isHarvest, isTransport, tphData.activities]);

                return (
                    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 animate-fade-in">
                        <div className="bg-white rounded-xl shadow-2xl max-w-[95vw] w-full mx-4 max-h-[95vh] overflow-hidden flex flex-col">
                            {/* Header Modal */}
                            <div className={`bg-gradient-to-r p-5 text-white flex-shrink-0 ${isHarvest ? 'from-green-600 to-green-700' : 'from-blue-600 to-blue-700'}`}>
                                <div className="flex justify-between items-center">
                                    <div>
                                        <h3 className="text-xl font-bold flex items-center gap-2">
                                            <Icon name={isHarvest ? "trees" : "truck"} size={24} />
                                            {isHarvest ? 'Detail Aktivitas Panen' : 'Detail Aktivitas Pengiriman'}
                                        </h3>
                                        <div className="flex gap-4 mt-1 text-sm opacity-90">
                                            <span className="bg-white/20 px-2 py-0.5 rounded">Blok: <strong>{blokName}</strong></span>
                                            <span className="bg-white/20 px-2 py-0.5 rounded">TPH: <strong>{tphData.tph}</strong></span>
                                            <span className="bg-white/20 px-2 py-0.5 rounded">Records: <strong>{tphData.activities.length}</strong></span>
                                        </div>
                                    </div>
                                    <button 
                                        onClick={handleCloseDetail}
                                        className="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-full transition-colors"
                                    >
                                        <Icon name="x" size={24} />
                                    </button>
                                </div>
                            </div>
                            
                            {/* Content Scrollable */}
                            <div className="p-6 overflow-y-auto flex-grow bg-gray-50">
                                {(isHarvest && enhancedSummary) || (isTransport && enhancedSummary) ? (
                                    <div className="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                                        <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                            <div className="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Total Janjang</div>
                                            <div className="text-2xl font-bold text-green-600">{enhancedSummary.janjang}</div>
                                            {enhancedSummary.koreksi !== 0 && (
                                                <div className="text-xs text-orange-500 font-medium mt-1">
                                                    {enhancedSummary.koreksi > 0 ? '+' : ''}{enhancedSummary.koreksi} koreksi
                                                </div>
                                            )}
                                        </div>
                                        <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                            <div className="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Total Berat</div>
                                            <div className="text-2xl font-bold text-blue-600">{enhancedSummary.kg.toFixed(0)} <span className="text-sm font-normal text-gray-400">kg</span></div>
                                            <div className="text-xs text-gray-400 mt-1">BJR: {enhancedSummary.janjang > 0 ? (enhancedSummary.kg / enhancedSummary.janjang).toFixed(2) : '0.00'}</div>
                                        </div>
                                        {isTransport && enhancedSummary.brondolan > 0 && (
                                            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                                <div className="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Brondolan</div>
                                                <div className="text-2xl font-bold text-yellow-600">{enhancedSummary.brondolan.toFixed(1)} <span className="text-sm font-normal text-gray-400">kg</span></div>
                                            </div>
                                        )}
                                    </div>
                                ) : null}

                                {/* TABEL DETAIL */}
                                <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                {isHarvest ? (
                                                    <tr className="bg-gray-50 text-gray-500 text-xs uppercase font-bold border-b border-gray-200">
                                                        <th className="py-4 px-4 text-left w-48">Waktu & Petugas</th>
                                                        <th className="py-4 px-4 text-center w-24 bg-green-50/50 text-green-700 border-x border-gray-100">Janjang</th>
                                                        <th className="py-4 px-4 text-center w-32 bg-blue-50/50 text-blue-700">Berat (Kg)</th>
                                                        <th className="py-4 px-4 text-left">Detail Kualitas (Grading)</th>
                                                        <th className="py-4 px-4 text-left w-40">Info Lain</th>
                                                        <th className="py-4 px-4 text-center w-24">Aksi</th>
                                                    </tr>
                                                ) : isTransport ? (
                                                    <tr className="bg-gray-50 text-gray-500 text-xs uppercase font-bold border-b border-gray-200">
                                                        <th className="py-4 px-4 text-left w-48">Waktu & Petugas</th>
                                                        <th className="py-4 px-4 text-center w-24 bg-green-50/50 text-green-700 border-x border-gray-100">Janjang</th>
                                                        <th className="py-4 px-4 text-center w-32 bg-blue-50/50 text-blue-700">Berat (Kg)</th>
                                                        <th className="py-4 px-4 text-left">Info Kendaraan</th>
                                                        <th className="py-4 px-4 text-left w-40">Info Lain</th>
                                                        <th className="py-4 px-4 text-center w-24">Aksi</th>
                                                    </tr>
                                                ) : (
                                                    <tr className="bg-gray-50 text-gray-500 text-xs uppercase font-bold border-b border-gray-200">
                                                        <th className="py-4 px-4 text-left">Waktu</th>
                                                        <th className="py-4 px-4 text-left">Kerani</th>
                                                        <th className="py-4 px-4 text-left">Unit / Driver</th>
                                                        <th className="py-4 px-4 text-center">Muatan</th>
                                                        <th className="py-4 px-4 text-center">Status</th>
                                                    </tr>
                                                )}
                                            </thead>
                                            <tbody className="divide-y divide-gray-100">
                                                {tphData.activities.map((activity, idx) => (
                                                    <tr key={idx} className="hover:bg-gray-50 transition-colors">
                                                        {isHarvest ? (
                                                            // Logic Render Panen
                                                            <>
                                                                <td className="py-4 px-4 align-top">
                                                                    <div className="flex flex-col gap-1">
                                                                        <div className="flex items-center gap-2 text-gray-900 font-medium">
                                                                            <span className="bg-gray-100 text-gray-600 py-0.5 px-1.5 rounded text-xs font-mono">
                                                                                {new Date(activity.createdAt || activity.tanggal || activity.date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                                                                            </span>
                                                                            <span>{new Date(activity.createdAt || activity.tanggal || activity.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</span>
                                                                        </div>
                                                                        <div className="mt-1">
                                                                            <div className="text-sm font-semibold text-gray-800">{activity.namaPemanen || 'Tanpa Nama'}</div>
                                                                            <div className="text-xs text-gray-500">Kerani: {activity.namaKerani || activity.kerani || '-'}</div>
                                                                            <div className="text-xs text-gray-400">NIK: {activity.nikPemanen || '-'}</div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td className="py-4 px-4 align-top text-center bg-green-50/10 border-x border-gray-50">
                                                                    <div className="text-lg font-bold text-gray-700 mb-1">
                                                                        <span className="text-xs text-gray-500 font-normal">Ori: </span>
                                                                        {parseInt(activity.jumlahJanjang || activity.janjang) || 0}
                                                                    </div>
                                                                    {activity.koreksiPanen && parseInt(activity.koreksiPanen) !== 0 && (
                                                                        <div className="mb-1">
                                                                            <div className={`text-xs font-bold px-2 py-0.5 rounded-full inline-block ${parseInt(activity.koreksiPanen) > 0 ? 'bg-orange-100 text-orange-600' : 'bg-red-100 text-red-600'}`}>
                                                                                {parseInt(activity.koreksiPanen) > 0 ? '+' : ''}{activity.koreksiPanen} Kor
                                                                            </div>
                                                                        </div>
                                                                    )}
                                                                    <div className="text-2xl font-bold text-green-600 mt-1">{(parseInt(activity.jumlahJanjang || activity.janjang) || 0) + (parseInt(activity.koreksiPanen) || 0)}</div>
                                                                    <div className="text-xs text-gray-400">Total</div>
                                                                    {activity.koreksiByName && activity.koreksiPanen && parseInt(activity.koreksiPanen) !== 0 && (
                                                                        <div className="text-[10px] text-gray-500 mt-1" title={`Dikoreksi oleh ${activity.koreksiByName} pada ${new Date(activity.koreksiAt).toLocaleString('id-ID')}`}>
                                                                            oleh {activity.koreksiByName.split(' ')[0]}
                                                                        </div>
                                                                    )}
                                                                </td>
                                                                <td className="py-4 px-4 align-top text-center bg-blue-50/10">
                                                                    <div className="text-lg font-bold text-blue-700">{(activity.kgTotal || activity.totalKg || activity.kg || 0).toFixed(0)} <span className="text-sm font-normal">kg</span></div>
                                                                    <div className="text-xs text-gray-500 mt-1">BJR: <span className="font-mono font-medium">{((activity.kgTotal || activity.totalKg) / (((parseInt(activity.jumlahJanjang) || 0) + (parseInt(activity.koreksiPanen) || 0)) || 1)).toFixed(2)}</span></div>
                                                                    {(parseFloat(activity.kgBerondolan) > 0) && (<div className="text-xs text-yellow-600 mt-2 bg-yellow-50 px-2 py-1 rounded inline-block">Brd: {parseFloat(activity.kgBerondolan).toFixed(1)} kg</div>)}
                                                                </td>
                                                                <td className="py-4 px-4 align-top">
                                                                    <div className="grid grid-cols-4 gap-2 text-xs">
                                                                        <div className="flex flex-col items-center p-1 rounded bg-green-50 border border-green-100"><span className="font-bold text-green-700 text-sm">{activity.matang || 0}</span><span className="text-[10px] text-green-600 uppercase">Matang</span></div>
                                                                        <div className="flex flex-col items-center p-1 rounded bg-yellow-50 border border-yellow-100"><span className="font-bold text-yellow-700 text-sm">{activity.mengkal || 0}</span><span className="text-[10px] text-yellow-600 uppercase">Mengkal</span></div>
                                                                        <div className="flex flex-col items-center p-1 rounded bg-red-50 border border-red-100"><span className="font-bold text-red-700 text-sm">{activity.mentah || 0}</span><span className="text-[10px] text-red-600 uppercase">Mentah</span></div>
                                                                        <div className="flex flex-col items-center p-1 rounded bg-purple-50 border border-purple-100"><span className="font-bold text-purple-700 text-sm">{activity.lewatMatang || 0}</span><span className="text-[10px] text-purple-600 uppercase">Lewat</span></div>
                                                                        {(activity.abnormal > 0) && (<div className="flex flex-col items-center p-1 rounded bg-gray-100"><span className="font-bold text-gray-700">{activity.abnormal}</span><span className="text-[10px] text-gray-500">Abnormal</span></div>)}
                                                                        {(activity.seranganHama > 0) && (<div className="flex flex-col items-center p-1 rounded bg-red-50 border border-red-100 col-span-2"><span className="font-bold text-red-700">{activity.seranganHama}</span><span className="text-[10px] text-red-600">Hama</span></div>)}
                                                                        {(activity.tangkaiPanjang > 0) && (<div className="flex flex-col items-center p-1 rounded bg-orange-50 border border-orange-100 col-span-2"><span className="font-bold text-orange-700">{activity.tangkaiPanjang}</span><span className="text-[10px] text-orange-600">Tgkai Pjg</span></div>)}
                                                                        {(activity.janjangKosong > 0) && (<div className="flex flex-col items-center p-1 rounded bg-gray-100 border border-gray-200 col-span-2"><span className="font-bold text-gray-700">{activity.janjangKosong}</span><span className="text-[10px] text-gray-600">Kosong</span></div>)}
                                                                    </div>
                                                                </td>
                                                                <td className="py-4 px-4 align-top">
                                                                    <div className="space-y-1">
                                                                        <div className="flex justify-between text-xs"><span className="text-gray-500">Afdeling:</span><span className="font-medium text-gray-700">{activity.afdeling}</span></div>
                                                                        <div className="flex justify-between text-xs"><span className="text-gray-500">Ancak:</span><span className="font-medium text-gray-700">{activity.noAncak || '-'}</span></div>
                                                                        {activity.catatan && (<div className="mt-2 text-xs bg-yellow-50 p-2 rounded text-yellow-800 border border-yellow-100 italic">"{activity.catatan}"</div>)}
                                                                    </div>
                                                                </td>
                                                                <td className="py-4 px-4 align-middle text-center">
                                                                    <div className="flex flex-col gap-2">
                                                                        {activity.mainFoto && ( <button onClick={() => setPreviewImage(activity.mainFoto)} className="flex items-center justify-center gap-1 w-full py-1.5 px-3 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-xs font-medium"><Icon name="camera" size={14} /> Foto</button>)}
                                                                        {activity.id && (<button onClick={() => openEditKoreksiModal(activity)} className="flex items-center justify-center gap-1 w-full py-1.5 px-3 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors text-xs font-medium border border-yellow-200">Edit</button>)}
                                                                    </div>
                                                                </td>
                                                            </>
                                                        ) : isTransport ? (
                                                            // Logic Render Transport Detail (Mirip dengan Panen)
                                                            <>
                                                                <td className="py-4 px-4 align-top">
                                                                    <div className="flex flex-col gap-1">
                                                                        <div className="flex items-center gap-2 text-gray-900 font-medium">
                                                                            <span className="bg-gray-100 text-gray-600 py-0.5 px-1.5 rounded text-xs font-mono">
                                                                                {activity.waktu || '-'}
                                                                            </span>
                                                                            <span>{new Date(activity.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</span>
                                                                        </div>
                                                                        <div className="mt-1">
                                                                            <div className="text-sm font-semibold text-gray-800">{activity.namaKerani || activity.kerani || 'Tanpa Nama'}</div>
                                                                            <div className="text-xs text-gray-500">NIK: {activity.nikKerani || '-'}</div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td className="py-4 px-4 align-top text-center bg-green-50/10 border-x border-gray-50">
                                                                    <div className="text-lg font-bold text-gray-700 mb-1">
                                                                        <span className="text-xs text-gray-500 font-normal">Ori: </span>
                                                                        {parseInt(activity.jumlahJanjang || activity.janjang) || 0}
                                                                    </div>
                                                                    {activity.koreksiKirim && parseInt(activity.koreksiKirim) !== 0 && (
                                                                        <div className="mb-1">
                                                                            <div className={`text-xs font-bold px-2 py-0.5 rounded-full inline-block ${parseInt(activity.koreksiKirim) > 0 ? 'bg-orange-100 text-orange-600' : 'bg-red-100 text-red-600'}`}>
                                                                                {parseInt(activity.koreksiKirim) > 0 ? '+' : ''}{activity.koreksiKirim} Kor
                                                                            </div>
                                                                        </div>
                                                                    )}
                                                                    <div className="text-2xl font-bold text-green-600 mt-1">{(parseInt(activity.jumlahJanjang || activity.janjang) || 0) + (parseInt(activity.koreksiKirim) || 0)}</div>
                                                                    <div className="text-xs text-gray-400">Total</div>
                                                                    {activity.koreksiByName && activity.koreksiKirim && parseInt(activity.koreksiKirim) !== 0 && (
                                                                        <div className="text-[10px] text-gray-500 mt-1" title={`Dikoreksi oleh ${activity.koreksiByName} pada ${new Date(activity.koreksiAt).toLocaleString('id-ID')}`}>
                                                                            oleh {activity.koreksiByName.split(' ')[0]}
                                                                        </div>
                                                                    )}
                                                                </td>
                                                                <td className="py-4 px-4 align-top text-center bg-blue-50/10">
                                                                    <div className="text-lg font-bold text-blue-700">{(activity.kgTotal || activity.totalKg || 0).toFixed(0)} <span className="text-sm font-normal">kg</span></div>
                                                                    <div className="text-xs text-gray-500 mt-1">BJR: <span className="font-mono font-medium">{((activity.kgTotal || activity.totalKg || 0) / (((parseInt(activity.jumlahJanjang || activity.janjang) || 0) + (parseInt(activity.koreksiKirim) || 0)) || 1)).toFixed(2)}</span></div>
                                                                    {(parseFloat(activity.kgBerondolan || activity.kgBrd) > 0) && (<div className="text-xs text-yellow-600 mt-2 bg-yellow-50 px-2 py-1 rounded inline-block">Brd: {parseFloat(activity.kgBerondolan || activity.kgBrd).toFixed(1)} kg</div>)}
                                                                </td>
                                                                <td className="py-4 px-4 align-top">
                                                                    <div className="space-y-0.5">
                                                                        <div className="text-xs"><span className="text-gray-500">Nopol: </span><span className="font-medium text-gray-700 uppercase">{activity.nopol || '-'}</span></div>
                                                                        <div className="text-xs"><span className="text-gray-500">No Unit: </span><span className="font-medium text-gray-700">{activity.noKend || '-'}</span></div>
                                                                    </div>
                                                                </td>
                                                                <td className="py-4 px-4 align-top">
                                                                    <div className="space-y-1">
                                                                        <div className="flex justify-between text-xs"><span className="text-gray-500">Afdeling:</span><span className="font-medium text-gray-700">{activity.afdeling || '-'}</span></div>
                                                                        {activity.koordinat && (<div className="flex justify-between text-xs"><span className="text-gray-500">Koordinat:</span><span className="font-medium text-gray-700 text-[10px]">{activity.koordinat}</span></div>)}
                                                                    </div>
                                                                </td>
                                                                <td className="py-4 px-4 align-middle text-center">
                                                                    <div className="flex flex-col gap-2">
                                                                        {activity.foto && ( <button onClick={() => setPreviewImage(activity.foto)} className="flex items-center justify-center gap-1 w-full py-1.5 px-3 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-xs font-medium"><Icon name="camera" size={14} /> Foto</button>)}
                                                                        {activity.id && (<button onClick={() => openEditKoreksiModal(activity)} className="flex items-center justify-center gap-1 w-full py-1.5 px-3 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors text-xs font-medium border border-yellow-200">Edit</button>)}
                                                                    </div>
                                                                </td>
                                                            </>
                                                        ) : (
                                                            // Logic Render Recap (Sesuai Screenshot)
                                                            <>
                                                                <td className="py-4 px-4 align-top">
                                                                    <div className="flex flex-col gap-1">
                                                                        <div className="flex items-center gap-2 text-gray-900 font-medium">
                                                                            <span className="bg-gray-100 text-gray-600 py-0.5 px-1.5 rounded text-xs font-mono">
                                                                                {activity.waktu || activity.jam || '-'}
                                                                            </span>
                                                                            <span>{new Date(activity.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</span>
                                                                        </div>
                                                                        <div className="mt-1 text-sm text-gray-600">{activity.type}</div>
                                                                    </div>
                                                                </td>
                                                                <td className="py-4 px-4">{activity.kerani || activity.namaKerani || '-'}</td>
                                                                <td className="py-4 px-4">
                                                                    <div className="font-medium">{activity.nopol || activity.pemanen || activity.namaPemanen || '-'}</div>
                                                                    <div className="text-xs text-gray-500">{activity.noKend}</div>
                                                                </td>
                                                                <td className="py-4 px-4 text-center">
                                                                    <span className="font-bold text-gray-800">{
                                                                        activity.type === 'Kirim'
                                                                            ? ((parseInt(activity.janjang || activity.jumlahJanjang) || 0) + (parseInt(activity.koreksiKirim) || 0))
                                                                            : ((parseInt(activity.janjang || activity.jumlahJanjang) || 0) + (parseInt(activity.koreksiPanen) || 0))
                                                                    }</span> <span className="text-xs text-gray-500">Jjg</span>
                                                                </td>
                                                                <td className="py-4 px-4 text-center">
                                                                    <span className="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">Selesai</span>
                                                                </td>
                                                            </>
                                                        )}
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Footer Modal: Tombol Export Excel & Tutup */}
                            <div className="p-4 border-t bg-gray-50 flex justify-end items-center flex-shrink-0 gap-3">
                                <button 
                                    onClick={handleModalExport}
                                    className="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm"
                                >
                                    <Icon name="fileSpreadsheet" size={16} /> Export Excel
                                </button>
                                <button 
                                    onClick={handleCloseDetail}
                                    className="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                );
            };

            return (
                <div className="min-h-screen bg-gray-50 font-sans text-gray-800">
                    {/* Loading Overlay */}
                    {loading && (
                        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div className="bg-white p-6 rounded-lg shadow-lg flex items-center gap-3">
                                <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600"></div>
                                <span className="text-gray-700">Memuat data dari database...</span>
                            </div>
                        </div>
                    )}

                    {/* HEADER */}
                    <header className="bg-white border-b border-gray-200 sticky top-16 z-20 shadow-sm no-print">
                        <div className="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8">
                            <div className="flex flex-col md:flex-row md:items-center justify-between h-auto md:h-16 py-3 md:py-0 gap-4">
                                
                                <div className="flex items-center gap-3">
                                    <div className="bg-gradient-to-br from-green-500 to-green-700 p-2 rounded-lg shadow-lg shadow-green-500/30">
                                        <Icon name="layoutDashboard" size={20} className="text-white" />
                                    </div>
                                    <div>
                                        <h1 className="text-xl font-bold text-gray-900 leading-none">Data <span className="text-green-600">Monitoring</span></h1>
                                        <p className="text-xs text-gray-500 mt-0.5 font-medium">Real-time Database Integration | {harvestRaw.length} panen + {transportRaw.length} pengiriman records</p>
                                    </div>
                                </div>

                                <div className="flex bg-gray-100 p-1 rounded-lg overflow-x-auto no-scrollbar">
                                    {[
                                        { id: 'recap', label: 'Restan', icon: 'arrowRightLeft' },
                                        { id: 'harvest', label: 'Grading', icon: 'trees' },
                                        { id: 'transport', label: 'Pengiriman', icon: 'truck' }
                                    ].map(tab => (
                                        <button
                                            key={tab.id}
                                            onClick={() => { setActiveTab(tab.id); setFilters({...filters, status: '', blok: '', pemanen: '', namaKerani: '', noKend: ''}); }}
                                            className={`
                                                flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all whitespace-nowrap
                                                ${activeTab === tab.id ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200'}
                                            `}
                                        >
                                            <Icon name={tab.icon} size={16} /> {tab.label}
                                        </button>
                                    ))}
                                </div>


                            </div>
                        </div>
                    </header>

                    <main className="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-8 space-y-6 no-print">

                        {/* CONTENT */}
                        {harvestRaw.length > 0 || transportRaw.length > 0 ? (
                            <>
                                {/* STATS & MAIN CONTENT */}
                                {activeTab === 'recap' && currentView === 'blok' && totals && (
                                    <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                                        <StatCard 
                                            title="Total Blok" 
                                            value={(totals.blokCount || 0).toLocaleString()} 
                                            icon="map" color="bg-purple-500" 
                                            subValue={`${totals.tphCount || 0} TPH`}
                                        />
                                        <StatCard 
                                            title="Total Panen" 
                                            value={(totals.panen || 0).toLocaleString()} 
                                            icon="trees" color="bg-green-500" 
                                            subValue="Janjang"
                                        />
                                        <StatCard 
                                            title="Total Kirim" 
                                            value={(totals.kirim || 0).toLocaleString()} 
                                            icon="truck" color="bg-blue-500" 
                                            subValue="Janjang"
                                        />
                                        <StatCard 
                                            title="Total Restan" 
                                            value={(totals.restan || 0).toLocaleString()} 
                                            icon="alertTriangle" color="bg-red-500" 
                                            subValue="Janjang"
                                        />
                                        <StatCard 
                                            title="Persentase Restan" 
                                            value={totals.panen > 0 ? `${(((totals.restan || 0) / (totals.panen || 1)) * 100).toFixed(1)}%` : '0%'} 
                                            icon="percent" color="bg-orange-500" 
                                            subValue={`dari ${(totals.panen || 0).toLocaleString()}`}
                                        />
                                    </div>
                                )}
                                
                                {activeTab === 'recap' && currentView === 'tph' && (
                                    <div className="bg-blue-50 p-4 rounded-lg">
                                        <h2 className="text-lg font-semibold text-blue-800">TPH di Blok: {selectedBlok}</h2>
                                        <p className="text-blue-600">Menampilkan detail TPH dan aktivitas per lokasi</p>
                                    </div>
                                )}
                                
                                {activeTab !== 'recap' && totals && (
                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <StatCard 
                                            title="Total Data" 
                                            value={finalDisplayData.length.toLocaleString()} 
                                            icon="database" color="bg-purple-500" 
                                        />
                                        <StatCard 
                                            title="Total Janjang" 
                                            value={(totals.totalJanjang || 0).toLocaleString()} 
                                            icon={activeTab === 'harvest' ? "trees" : "truck"} color="bg-green-500" 
                                        />
                                        <StatCard 
                                            title="Rata-rata BJR" 
                                            value={totals.avgBjr ? `${parseFloat(totals.avgBjr).toFixed(1)} kg` : '0 kg'} 
                                            icon="weight" color="bg-blue-500" 
                                        />
                                        <StatCard 
                                            title="Total KG" 
                                            value={totals.totalKg ? `${parseFloat(totals.totalKg).toFixed(0)} kg` : '0 kg'} 
                                            icon="package" color="bg-orange-500" 
                                        />
                                    </div>
                                )}

                                {/* MAIN FILTER TOOLBAR */}
                                <div className="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                                    <div className="flex flex-col lg:flex-row gap-4 w-full">
                                        {/* Filters section */}
                                        <div className="flex-grow flex flex-wrap gap-4 items-end">
                                            {/* DATE FILTERS */}
                                            <div className="flex flex-col sm:flex-row gap-2 bg-gray-50 p-2 rounded-lg border border-gray-200 items-center flex-wrap">
                                                <div className="flex items-center gap-2 flex-shrink-0">
                                                    <Icon name="calendar" size={16} className="text-gray-500" />
                                                    <span className="text-xs font-bold text-gray-500 uppercase">Tanggal:</span>
                                                </div>
                                                <input 
                                                    type="date" 
                                                    className="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-green-500 focus:border-green-500 block p-1 w-full sm:w-auto"
                                                    value={filters.startDate}
                                                    onChange={(e) => setFilters({...filters, startDate: e.target.value})}
                                                />
                                                <span className="text-gray-400 self-center">-</span>
                                                <input 
                                                    type="date" 
                                                    className="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-green-500 focus:border-green-500 block p-1 w-full sm:w-auto"
                                                    value={filters.endDate}
                                                    onChange={(e) => setFilters({...filters, endDate: e.target.value})}
                                                />
                                                {(filters.startDate || filters.endDate) && (
                                                    <button 
                                                        onClick={() => setFilters({...filters, startDate: getInitialDateRange().startDate, endDate: getInitialDateRange().endDate})}
                                                        className="text-xs text-red-500 hover:text-red-700 underline self-center ml-2"
                                                    >
                                                        Reset
                                                    </button>
                                                )}
                                            </div>

                                            {/* DATA FILTERS */}
                                            <div className="flex flex-wrap gap-4 items-end">
                                                {activeTab === 'recap' && (
                                                    <FilterSelect 
                                                        label="Status" value={filters.status} options={['Sesuai', 'Restan']}
                                                        onChange={(val) => setFilters({...filters, status: val})} 
                                                    />
                                                )}
                                                <FilterSelect 
                                                    label="Afdeling" value={filters.afdeling} options={filterOptions.afdeling}
                                                    onChange={(val) => setFilters({...filters, afdeling: val, blok: ''})} 
                                                />
                                                <FilterSelect 
                                                    label="Blok" value={filters.blok} options={filterOptions.blok}
                                                    onChange={(val) => setFilters({...filters, blok: val})} 
                                                />
                                                {activeTab !== 'transport' && (
                                                    <FilterSelect 
                                                        label="Pemanen" value={filters.pemanen} options={filterOptions.pemanen}
                                                        onChange={(val) => setFilters({...filters, pemanen: val})} 
                                                    />
                                                )}
                                                {(activeTab === 'harvest' || activeTab === 'transport') && (
                                                    <FilterSelect 
                                                        label="Kerani" value={filters.namaKerani} options={filterOptions.namaKerani}
                                                        onChange={(val) => setFilters({...filters, namaKerani: val})} 
                                                    />
                                                )}
                                                {activeTab === 'transport' && (
                                                    <FilterSelect 
                                                        label="No. Kendaraan" value={filters.noKend} options={filterOptions.noKend}
                                                        onChange={(val) => setFilters({...filters, noKend: val})} 
                                                    />
                                                )}
                                            </div>
                                        </div>
                                        
                                        {/* Spacer */}
                                        <div className="flex-none hidden lg:block border-l mx-2"></div>

                                        {/* Search and actions section */}
                                        <div className="flex-shrink-0 flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                                            <button 
                                                onClick={handleExport}
                                                className="flex-shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm"
                                            >
                                                <Icon name="fileSpreadsheet" size={16} /> <span>Excel</span>
                                            </button>
                                            
                                            {activeTab === 'harvest' && (
                                                <button 
                                                    onClick={() => window.print()}
                                                    className="flex-shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm"
                                                >
                                                    <Icon name="printer" size={16} /> <span>Print</span>
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* DATA TABLE */}
                                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden min-h-[400px]">
                                    {activeTab === 'recap' && <RecapTable />}
                                    
                                    {activeTab === 'harvest' && <HarvestTable />}
                                    
                                    {activeTab === 'transport' && <TransportTable />}
                                    
                                    {sortedDisplayData.length === 0 && (
                                        <div className="p-10 text-center text-gray-400">
                                            Tidak ada data yang sesuai dengan filter.
                                        </div>
                                    )}
                                </div>
                            </>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-20 border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50">
                                <Icon name="database" size={64} className="text-gray-300 mb-4" />
                                <h3 className="text-lg font-semibold text-gray-600">Belum ada data monitoring</h3>
                                <p className="text-gray-400 text-sm mb-6 text-center max-w-md">
                                    Data monitoring akan otomatis ditampilkan dari database saat tersedia.
                                </p>
                                <div className="flex gap-3">
                                    <a href="dashboard.php" className="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-colors">
                                        <i class="fas fa-arrow-left"></i>
                                        <span>Kembali ke Dashboard</span>
                                    </a>
                                </div>
                            </div>
                        )}
                    </main>
                    
                    <PrintTemplate
                        data={activeTab === 'harvest' ? fullyFilteredHarvest : sortedDisplayData} 
                        activeTab={activeTab} 
                        filters={filters} 
                    />

                    {/* MODAL PHOTO PREVIEW */}
                    {previewImage && (
                        <ImageModal src={previewImage} onClose={() => setPreviewImage(null)} />
                    )}
                    
                    {/* ACTIVITY DETAIL MODAL */}
                    {showActivityDetail && (
                        <ActivityDetailModal />
                    )}
                    
                    {/* MODAL KOREKSI */}
                    {showKoreksiModal && (
                        <div 
                            style={{
                                position: 'fixed', top: 0, left: 0, width: '100%', height: '100%',
                                backgroundColor: 'rgba(0, 0, 0, 0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center',
                                zIndex: 9999, padding: '1rem'
                            }}
                            onClick={(e) => {
                                if (e.target === e.currentTarget) closeKoreksiModal();
                            }}
                        >
                            <div 
                                style={{
                                    backgroundColor: 'white', borderRadius: '8px',
                                    boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                                    maxWidth: '28rem', width: '100%', maxHeight: '90vh', overflow: 'auto'
                                }}
                                onClick={(e) => e.stopPropagation()}
                            >
                                {/* Header Modal */}
                                <div style={{ padding: '1.5rem 1.5rem 1rem 1.5rem', borderBottom: '1px solid #e5e7eb' }}>
                                    <h2 style={{ fontSize: '1.125rem', fontWeight: '600', color: '#1f2937', margin: 0 }}>
                                        Edit Koreksi Data
                                    </h2>
                                    <p style={{ fontSize: '0.875rem', color: '#6b7280', marginTop: '0.25rem', marginBottom: 0 }}>
                                        {editingRow ? `${editingRow.afdeling} - Blok ${editingRow.blok} - TPH ${editingRow.noTPH}` : ''}
                                    </p>
                                </div>
                                
                                <div style={{ padding: '1.5rem' }}>
                                    
                                    {/* LOGIC PENENTUAN TAMPILAN FIELD */}
                                    {(() => {
                                        // Tentukan field mana yang harus muncul
                                        const showPanenInput = activeTab === 'harvest' || (activeTab === 'recap' && (editingRow?.type === 'Panen' || editingRow?.panenJanjang !== undefined));
                                        const showKirimInput = activeTab === 'transport' || (activeTab === 'recap' && (editingRow?.type === 'Kirim' || editingRow?.transportJanjang !== undefined));
                                        
                                        // Hitung grid columns: jika dua-duanya muncul pakai 2 kolom, jika satu saja pakai 1 kolom
                                        const gridStyle = (showPanenInput && showKirimInput) 
                                            ? { display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '1rem', marginBottom: '1rem' }
                                            : { display: 'grid', gridTemplateColumns: '1fr', gap: '1rem', marginBottom: '1rem' };

                                        return (
                                            <>
                                                <div style={gridStyle}>
                                                    {/* INPUT KOREKSI PANEN */}
                                                    {showPanenInput && (
                                                        <div style={{ backgroundColor: '#f0fdf4', padding: '0.75rem', borderRadius: '6px' }}>
                                                            <label style={{ display: 'block', fontSize: '0.75rem', fontWeight: '500', color: '#15803d', marginBottom: '0.25rem' }}>
                                                                Data Panen: {editingRow?.panenJanjang || editingRow?.jumlahJanjang || 0} Jjg
                                                            </label>
                                                            <input
                                                                type="number" min="-999" max="999"
                                                                value={koreksiForm.koreksiPanen}
                                                                onChange={(e) => handleKoreksiFormChange('koreksiPanen', e.target.value)}
                                                                style={{
                                                                    width: '100%', padding: '0.5rem 0.75rem', border: '1px solid #bbf7d0',
                                                                    borderRadius: '4px', fontSize: '0.875rem', outline: 'none'
                                                                }}
                                                                placeholder="Koreksi (+/-)"
                                                            />
                                                            <p style={{ fontSize: '0.75rem', color: '#059669', marginTop: '0.25rem', marginBottom: 0 }}>
                                                                Total: {(parseInt(editingRow?.panenJanjang || editingRow?.jumlahJanjang || 0) + koreksiForm.koreksiPanen)} Jjg
                                                            </p>
                                                        </div>
                                                    )}
                                                    
                                                    {/* INPUT KOREKSI KIRIM */}
                                                    {showKirimInput && (
                                                        <div style={{ backgroundColor: '#eff6ff', padding: '0.75rem', borderRadius: '6px' }}>
                                                            <label style={{ display: 'block', fontSize: '0.75rem', fontWeight: '500', color: '#1d4ed8', marginBottom: '0.25rem' }}>
                                                                Data Kirim: {editingRow?.transportJanjang || editingRow?.jumlahJanjang || 0} Jjg
                                                            </label>
                                                            <input
                                                                type="number" min="-999" max="999"
                                                                value={koreksiForm.koreksiKirim}
                                                                onChange={(e) => handleKoreksiFormChange('koreksiKirim', e.target.value)}
                                                                style={{
                                                                    width: '100%', padding: '0.5rem 0.75rem', border: '1px solid #bfdbfe',
                                                                    borderRadius: '4px', fontSize: '0.875rem', outline: 'none'
                                                                }}
                                                                placeholder="Koreksi (+/-)"
                                                            />
                                                            <p style={{ fontSize: '0.75rem', color: '#2563eb', marginTop: '0.25rem', marginBottom: 0 }}>
                                                                Total: {(parseInt(editingRow?.transportJanjang || editingRow?.jumlahJanjang || 0) + koreksiForm.koreksiKirim)} Jjg
                                                            </p>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* INPUT ALASAN */}
                                                <div style={{ marginBottom: '1rem' }}>
                                                    <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: '500', color: '#374151', marginBottom: '0.5rem' }}>
                                                        Alasan Koreksi <span style={{color: 'red'}}>*</span>
                                                    </label>
                                                    <textarea
                                                        value={koreksiForm.alasan}
                                                        onChange={(e) => handleKoreksiFormChange('alasan', e.target.value)}
                                                        style={{
                                                            width: '100%', padding: '0.5rem 0.75rem', border: '1px solid #d1d5db',
                                                            borderRadius: '4px', fontSize: '0.875rem', minHeight: '4rem', resize: 'vertical'
                                                        }}
                                                        rows={3}
                                                        placeholder="Jelaskan alasan koreksi data..."
                                                    />
                                                </div>
                                                
                                                {/* INFO SUMMARY DINAMIS */}
                                                <div style={{ backgroundColor: '#fffbeb', border: '1px solid #fde68a', borderRadius: '6px', padding: '0.75rem', marginBottom: '1rem' }}>
                                                    <p style={{ fontSize: '0.75rem', color: '#92400e', margin: 0 }}>
                                                        <strong>Ringkasan Perubahan:</strong><br />
                                                        {showPanenInput && showKirimInput ? (
                                                            // Jika muncul dua-duanya (Recap lengkap)
                                                            <span>
                                                                Restan Baru: <strong>{((editingRow?.panenJanjang || 0) + koreksiForm.koreksiPanen) - ((editingRow?.transportJanjang || 0) + koreksiForm.koreksiKirim)} Jjg</strong>
                                                            </span>
                                                        ) : showPanenInput ? (
                                                            // Jika hanya Panen
                                                            <span>Total Panen akan menjadi <strong>{(parseInt(editingRow?.panenJanjang || editingRow?.jumlahJanjang || 0) + koreksiForm.koreksiPanen)} Jjg</strong></span>
                                                        ) : (
                                                            // Jika hanya Kirim
                                                            <span>Total Kirim akan menjadi <strong>{(parseInt(editingRow?.transportJanjang || editingRow?.jumlahJanjang || 0) + koreksiForm.koreksiKirim)} Jjg</strong></span>
                                                        )}
                                                    </p>
                                                </div>
                                            </>
                                        );
                                    })()}
                                </div>
                                
                                {/* Footer Tombol */}
                                <div style={{ padding: '1rem 1.5rem 1.5rem 1.5rem', borderTop: '1px solid #e5e7eb', display: 'flex', justifyContent: 'flex-end', gap: '0.75rem' }}>
                                    <button
                                        onClick={closeKoreksiModal}
                                        disabled={isSubmittingKoreksi}
                                        style={{
                                            padding: '0.5rem 1rem', fontSize: '0.875rem', color: '#6b7280',
                                            border: '1px solid #d1d5db', borderRadius: '4px', backgroundColor: 'white',
                                            cursor: isSubmittingKoreksi ? 'not-allowed' : 'pointer'
                                        }}
                                    >
                                        Batal
                                    </button>
                                    <button
                                        onClick={handleKoreksiSubmit}
                                        disabled={isSubmittingKoreksi || !koreksiForm.alasan.trim()}
                                        style={{
                                            padding: '0.5rem 1rem', backgroundColor: '#7c3aed', color: 'white',
                                            fontSize: '0.875rem', borderRadius: '4px', border: 'none',
                                            cursor: (isSubmittingKoreksi || !koreksiForm.alasan.trim()) ? 'not-allowed' : 'pointer',
                                            display: 'flex', alignItems: 'center', gap: '0.5rem', opacity: (isSubmittingKoreksi || !koreksiForm.alasan.trim()) ? 0.5 : 1
                                        }}
                                    >
                                        {isSubmittingKoreksi && (
                                            <div style={{
                                                width: '1rem', height: '1rem', border: '2px solid white',
                                                borderTop: '2px solid transparent', borderRadius: '50%', animation: 'spin 1s linear infinite'
                                            }}></div>
                                        )}
                                        {isSubmittingKoreksi ? 'Menyimpan...' : 'Simpan Koreksi'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            );
        }

        // Render aplikasi
        console.log('🎯 Attempting to render React app...');
        
        try {
            const root = ReactDOM.createRoot(document.getElementById('root'));
            console.log('✅ React root created successfully');
            
            root.render(React.createElement(App));
            console.log('✅ App component rendered successfully');
        } catch (error) {
            console.error('❌ React rendering failed:', error);
            document.getElementById('root').innerHTML = `
                <div style="padding: 20px; color: red; background: #ffebee; border: 1px solid red; margin: 20px; border-radius: 5px;">
                    <h3>React Rendering Error</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p><strong>Please check console for details.</strong></p>
                </div>
            `;
        }
    </script>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const profileDropdownButton = document.getElementById('profile-dropdown-button');
            const profileDropdown = document.getElementById('profile-dropdown');

            // Toggle mobile menu
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Toggle profile dropdown
            if (profileDropdownButton && profileDropdown) {
                profileDropdownButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileDropdownButton.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>