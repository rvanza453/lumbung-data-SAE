<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Overview - HR Analytics</title>
    
    @verbatim
    <script src="https://unpkg.com/react@18.2.0/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18.2.0/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/prop-types/prop-types.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/recharts@2.10.3/umd/Recharts.js"></script>
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
    @endverbatim
    
    <style>
        html, body {
            background-color: #0f172a;
            color: #f8fafc;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Custom Select Style */
        select.custom-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            appearance: none;
        }
    </style>
</head>
<body class="min-h-screen">
    
    <div id="root" class="min-h-screen flex items-center justify-center">
        <div class="animate-spin w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full"></div>
    </div>

    @verbatim
    <script type="text/babel">
        const { useState, useEffect, useMemo } = React;

        const Icons = {
            Chart: () => <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>,
            ArrowLeft: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>,
            Filter: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        };

        function DashboardOverview() {
            const [loading, setLoading] = useState(true);
            const [rawData, setRawData] = useState([]); 
            const [viewMode, setViewMode] = useState('yearly'); // 'yearly' | 'monthly'
            const [selectedYear, setSelectedYear] = useState(null);
            const [isRechartsReady, setIsRechartsReady] = useState(false);
            
            const getRechartsComponents = () => {
                if (!window.Recharts) return null;
                const R = window.Recharts;
                return {
                    BarChart: R.BarChart || R.default?.BarChart || R.default,
                    Bar: R.Bar || R.default?.Bar,
                    XAxis: R.XAxis || R.default?.XAxis,
                    YAxis: R.YAxis || R.default?.YAxis,
                    CartesianGrid: R.CartesianGrid || R.default?.CartesianGrid,
                    Tooltip: R.Tooltip || R.default?.Tooltip,
                    ResponsiveContainer: R.ResponsiveContainer || R.default?.ResponsiveContainer,
                };
            };

            useEffect(() => {
                if (window.Recharts && (window.Recharts.BarChart || window.Recharts.default)) {
                    setIsRechartsReady(true);
                    fetchMonthlyData();
                } else {
                    const checkInterval = setInterval(() => {
                        if (window.Recharts && (window.Recharts.BarChart || window.Recharts.default)) {
                            setIsRechartsReady(true);
                            clearInterval(checkInterval);
                            fetchMonthlyData();
                        }
                    }, 200);
                    setTimeout(() => clearInterval(checkInterval), 10000);
                }
            }, []);

            const fetchMonthlyData = async () => {
                try {
                    const response = await fetch('/api/performance/monthly-overview');
                    const result = await response.json();
                    
                    if (result.success) {
                        setRawData(result.data);
                    } else {
                        alert('Gagal memuat data: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error fetching data:', error);
                    alert('Terjadi kesalahan saat memuat data');
                } finally {
                    setLoading(false);
                }
            };

            // 1. Ekstrak daftar tahun unik untuk Dropdown Filter
            const yearsList = useMemo(() => {
                if (!rawData.length) return [];
                // Ambil tahun dari setiap periode (format: YYYY-MM)
                const years = new Set(rawData.map(item => item.periode.split('-')[0]));
                // Ubah ke array dan urutkan descending (terbaru di atas)
                return Array.from(years).sort().reverse();
            }, [rawData]);

            // Logic untuk memproses data berdasarkan View Mode
            const activeData = useMemo(() => {
                if (rawData.length === 0) return [];

                if (viewMode === 'yearly') {
                    const yearlyMap = {};
                    
                    rawData.forEach(item => {
                        const year = item.periode.split('-')[0];
                        if (!yearlyMap[year]) {
                            yearlyMap[year] = {
                                periode: year,
                                count: 0,
                                stats: {
                                    total: 0,
                                    star: 0,
                                    potential: 0,
                                    workhorse: 0,
                                    underperformer: 0,
                                    avg_hk: 0,
                                    avg_prod: 0,
                                    total_jjg: 0,
                                    total_ton: 0
                                },
                                top_players: [],
                                underperformers: []
                            };
                        }
                        
                        yearlyMap[year].count += 1;
                        yearlyMap[year].stats.total += item.stats.total;
                        yearlyMap[year].stats.star += item.stats.star;
                        yearlyMap[year].stats.potential += item.stats.potential;
                        yearlyMap[year].stats.workhorse += item.stats.workhorse;
                        yearlyMap[year].stats.underperformer += item.stats.underperformer;
                        yearlyMap[year].stats.avg_hk += item.stats.avg_hk;
                        yearlyMap[year].stats.avg_prod += item.stats.avg_prod;
                        yearlyMap[year].stats.total_jjg += item.stats.total_jjg;
                        yearlyMap[year].stats.total_ton += item.stats.total_ton;
                        yearlyMap[year].top_players = item.top_players;
                        yearlyMap[year].underperformers = item.underperformers;
                    });

                    // Hitung rata-rata
                    return Object.values(yearlyMap).map(y => ({
                        periode: y.periode,
                        stats: {
                            total: Math.round(y.stats.total / y.count),
                            star: Math.round(y.stats.star / y.count),
                            potential: Math.round(y.stats.potential / y.count),
                            workhorse: Math.round(y.stats.workhorse / y.count),
                            underperformer: Math.round(y.stats.underperformer / y.count),
                            avg_hk: y.stats.avg_hk / y.count,
                            avg_prod: y.stats.avg_prod / y.count,
                            total_jjg: y.stats.total_jjg,
                            total_ton: y.stats.total_ton 
                        },
                        top_players: y.top_players,
                        underperformers: y.underperformers
                    })).sort((a, b) => a.periode.localeCompare(b.periode));

                } else {
                    // View Mode: Monthly (Filter berdasarkan tahun terpilih)
                    return rawData
                        .filter(item => item.periode.startsWith(selectedYear))
                        .sort((a, b) => a.periode.localeCompare(b.periode));
                }
            }, [rawData, viewMode, selectedYear]);

            // Handler ketika Grafik diklik
            const handleChartClick = (data) => {
                if (viewMode === 'yearly') {
                    setSelectedYear(data.periode);
                    setViewMode('monthly');
                } else {
                    window.location.href = `/analisis/${data.periode}`;
                }
            };

            // 2. Handler Baru untuk Dropdown Filter
            const handleFilterChange = (e) => {
                const value = e.target.value;
                if (value === 'all') {
                    setViewMode('yearly');
                    setSelectedYear(null);
                } else {
                    setSelectedYear(value);
                    setViewMode('monthly');
                }
            };

            const RechartComponents = getRechartsComponents();

            const chartDataKaryawan = activeData.map(m => ({
                periode: m.periode,
                total: m.stats.total,
            }));

            const chartDataStar = activeData.map(m => ({
                periode: m.periode,
                star: m.stats.star,
            }));

            const chartDataHK = activeData.map(m => ({
                periode: m.periode,
                avg_hk: Number(m.stats.avg_hk).toFixed(1),
            }));

            const chartDataProd = activeData.map(m => ({
                periode: m.periode,
                avg_prod: Number(m.stats.avg_prod).toFixed(1),
            }));

            if (loading || !isRechartsReady) {
                return (
                    <div className="min-h-screen flex items-center justify-center">
                        <div className="text-center">
                            <div className="animate-spin w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                            <p className="text-slate-400">Memuat {!isRechartsReady ? 'library grafik' : 'data'}...</p>
                        </div>
                    </div>
                );
            }

            if (rawData.length === 0) {
                return (
                    <div className="min-h-screen flex items-center justify-center">
                        <div className="text-center">
                            <p className="text-slate-400 mb-4">Belum ada data. Silakan upload data di halaman Admin.</p>
                            <a href="/admin" className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg inline-block">
                                Ke Halaman Admin
                            </a>
                        </div>
                    </div>
                );
            }

            // UI Helpers
            const getTitle = (prefix) => {
                if (viewMode === 'yearly') return `${prefix} per Tahun`;
                return `${prefix} Tahun ${selectedYear}`;
            };

            return (
                <div className="min-h-screen pb-20 px-4">
                    {/* Navbar */}
                    <nav className="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 sticky top-0 z-40 px-6 py-4 shadow-lg -mx-4 mb-6">
                        <div className="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                            <div className="flex items-center gap-3 w-full md:w-auto">
                                <div className="bg-gradient-to-br from-indigo-500 to-blue-600 p-2 rounded-xl text-white shadow-lg">
                                    <Icons.Chart />
                                </div>
                                <div>
                                    <h1 className="text-xl font-bold text-slate-100">HR Analytics</h1>
                                    <p className="text-xs text-slate-400">
                                        {viewMode === 'yearly' ? 'Yearly Overview' : `Monthly Detail`}
                                    </p>
                                </div>
                            </div>
                            
                            <div className="flex items-center gap-3 w-full md:w-auto justify-end">
                                {/* FILTER DROPDOWN */}
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <Icons.Filter />
                                    </div>
                                    <select 
                                        value={viewMode === 'yearly' ? 'all' : selectedYear} 
                                        onChange={handleFilterChange}
                                        className="custom-select bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-9 py-2 cursor-pointer transition-colors outline-none"
                                    >
                                        <option value="all">Semua Tahun</option>
                                        {yearsList.map(year => (
                                            <option key={year} value={year}>{year}</option>
                                        ))}
                                    </select>
                                </div>

                                <a href="/admin" className="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm font-medium transition-colors text-white whitespace-nowrap">
                                    Admin
                                </a>
                            </div>
                        </div>
                    </nav>

                    <div className="max-w-7xl mx-auto space-y-8">
                        {/* HEADER STATS SUMMARY (Dinamis berdasarkan view) */}
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="bg-slate-800 p-4 rounded-xl border border-slate-700">
                                <p className="text-xs text-slate-400">Total Produksi (Akumulasi)</p>
                                <p className="text-2xl font-bold text-white">
                                    {activeData.reduce((acc, curr) => acc + curr.stats.total_ton, 0).toLocaleString('id-ID')} <span className="text-sm font-normal text-slate-400">Ton</span>
                                </p>
                            </div>
                            <div className="bg-slate-800 p-4 rounded-xl border border-slate-700">
                                <p className="text-xs text-slate-400">Rata-rata Produktivitas</p>
                                <p className="text-2xl font-bold text-indigo-400">
                                    {activeData.length ? (activeData.reduce((acc, curr) => acc + curr.stats.avg_prod, 0) / activeData.length).toFixed(1) : 0} <span className="text-sm font-normal text-slate-400">Kg/HK</span>
                                </p>
                            </div>
                             <div className="bg-slate-800 p-4 rounded-xl border border-slate-700">
                                <p className="text-xs text-slate-400">Rata-rata Karyawan</p>
                                <p className="text-2xl font-bold text-emerald-400">
                                    {activeData.length ? Math.round(activeData.reduce((acc, curr) => acc + curr.stats.total, 0) / activeData.length) : 0} <span className="text-sm font-normal text-slate-400">Org</span>
                                </p>
                            </div>
                        </div>

                        {/* Section 1: Total Karyawan + Tabel List */}
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div className="lg:col-span-2 bg-slate-800 p-6 rounded-2xl border border-slate-700">
                                <h2 className="text-lg font-bold text-white mb-4">{getTitle('Total Karyawan')}</h2>
                                <div className="h-[300px]">
                                    {isRechartsReady && RechartComponents ? (
                                        <RechartComponents.ResponsiveContainer width="100%" height="100%">
                                            <RechartComponents.BarChart data={chartDataKaryawan}>
                                                <RechartComponents.CartesianGrid strokeDasharray="3 3" stroke="#334155" />
                                                <RechartComponents.XAxis dataKey="periode" tick={{fill: '#94a3b8', fontSize: 12}} />
                                                <RechartComponents.YAxis tick={{fill: '#94a3b8', fontSize: 12}} />
                                                <RechartComponents.Tooltip contentStyle={{backgroundColor: '#1e293b', border: '1px solid #475569', borderRadius: '8px'}} />
                                                <RechartComponents.Bar dataKey="total" fill="#6366f1" onClick={(data) => handleChartClick(data)} cursor="pointer" />
                                            </RechartComponents.BarChart>
                                        </RechartComponents.ResponsiveContainer>
                                    ) : (
                                        <div className="flex items-center justify-center h-full text-slate-500">Loading chart...</div>
                                    )}
                                </div>
                                <p className="text-xs text-slate-500 mt-2 text-center">
                                    {viewMode === 'yearly' ? 'Klik bar tahun atau gunakan filter di atas untuk detail bulanan' : 'Klik bar bulan untuk melihat detail analisis'}
                                </p>
                            </div>
                            
                            <div className="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                                <h2 className="text-lg font-bold text-white mb-4">Ringkasan Data</h2>
                                <div className="space-y-3 max-h-[300px] overflow-y-auto no-scrollbar">
                                    {activeData.map(item => (
                                        <div key={item.periode} onClick={() => handleChartClick(item)} className="bg-slate-900/50 p-3 rounded-lg hover:bg-slate-700 transition-colors cursor-pointer">
                                            <div className="flex justify-between items-center mb-1">
                                                <span className="font-bold text-white">{item.periode}</span>
                                                <span className="text-indigo-400 font-bold">{item.stats.total} Org</span>
                                            </div>
                                            <div className="text-xs text-slate-400 space-y-0.5">
                                                <div className="flex justify-between">
                                                    <span>Star:</span><span className="text-emerald-400">{item.stats.star}</span>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span>Tonase:</span><span className="text-blue-400">{item.stats.total_ton.toLocaleString()} Ton</span>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Section 2: Star Player + Top Players */}
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div className="lg:col-span-2 bg-slate-800 p-6 rounded-2xl border border-slate-700">
                                <h2 className="text-lg font-bold text-white mb-4">{getTitle('Star Players')}</h2>
                                <div className="h-[300px]">
                                    {isRechartsReady && RechartComponents ? (
                                        <RechartComponents.ResponsiveContainer width="100%" height="100%">
                                            <RechartComponents.BarChart data={chartDataStar}>
                                                <RechartComponents.CartesianGrid strokeDasharray="3 3" stroke="#334155" />
                                                <RechartComponents.XAxis dataKey="periode" tick={{fill: '#94a3b8', fontSize: 12}} />
                                                <RechartComponents.YAxis tick={{fill: '#94a3b8', fontSize: 12}} />
                                                <RechartComponents.Tooltip contentStyle={{backgroundColor: '#1e293b', border: '1px solid #475569', borderRadius: '8px'}} />
                                                <RechartComponents.Bar dataKey="star" fill="#34d399" onClick={(data) => handleChartClick(data)} cursor="pointer" />
                                            </RechartComponents.BarChart>
                                        </RechartComponents.ResponsiveContainer>
                                    ) : (
                                        <div className="flex items-center justify-center h-full text-slate-500">Loading chart...</div>
                                    )}
                                </div>
                            </div>
                            
                            <div className="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                                <h2 className="text-lg font-bold text-white mb-4">Top 5 Star Players (Latest)</h2>
                                <div className="space-y-2 max-h-[300px] overflow-y-auto no-scrollbar">
                                    {activeData.length > 0 && activeData[activeData.length - 1].top_players.slice(0, 5).map((player, idx) => (
                                        <div key={idx} className="bg-emerald-900/20 p-3 rounded-lg border border-emerald-800/30">
                                            <div className="flex items-start gap-2">
                                                <div className="bg-emerald-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                                    {idx + 1}
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <div className="font-bold text-white text-sm truncate">{player.nama}</div>
                                                    <div className="text-xs text-slate-400">AFD: {player.afd}</div>
                                                    <div className="text-xs text-emerald-400 font-bold">{Math.round(player.kg_per_hk)} Kg/HK</div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Section 3: HKE + Underperformers */}
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div className="lg:col-span-2 bg-slate-800 p-6 rounded-2xl border border-slate-700">
                                <h2 className="text-lg font-bold text-white mb-4">{getTitle('Rata-rata HKE')}</h2>
                                <div className="h-[300px]">
                                    {isRechartsReady && RechartComponents ? (
                                        <RechartComponents.ResponsiveContainer width="100%" height="100%">
                                            <RechartComponents.BarChart data={chartDataHK}>
                                                <RechartComponents.CartesianGrid strokeDasharray="3 3" stroke="#334155" />
                                                <RechartComponents.XAxis dataKey="periode" tick={{fill: '#94a3b8', fontSize: 12}} />
                                                <RechartComponents.YAxis tick={{fill: '#94a3b8', fontSize: 12}} />
                                                <RechartComponents.Tooltip contentStyle={{backgroundColor: '#1e293b', border: '1px solid #475569', borderRadius: '8px'}} />
                                                <RechartComponents.Bar dataKey="avg_hk" fill="#fbbf24" onClick={(data) => handleChartClick(data)} cursor="pointer" />
                                            </RechartComponents.BarChart>
                                        </RechartComponents.ResponsiveContainer>
                                    ) : (
                                        <div className="flex items-center justify-center h-full text-slate-500">Loading chart...</div>
                                    )}
                                </div>
                            </div>
                            
                            <div className="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                                <h2 className="text-lg font-bold text-white mb-4">Underperformers (Latest)</h2>
                                <div className="space-y-2 max-h-[300px] overflow-y-auto no-scrollbar">
                                    {activeData.length > 0 && activeData[activeData.length - 1].underperformers.slice(0, 5).map((player, idx) => (
                                        <div key={idx} className="bg-rose-900/20 p-3 rounded-lg border border-rose-800/30">
                                            <div className="font-bold text-white text-sm truncate">{player.nama}</div>
                                            <div className="text-xs text-slate-400">AFD: {player.afd}</div>
                                            <div className="flex justify-between text-xs mt-1">
                                                <span className="text-slate-400">HK: {player.hk}</span>
                                                <span className="text-rose-400 font-bold">{Math.round(player.kg_per_hk)} Kg/HK</span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Section 4: Output + Performance Summary */}
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div className="lg:col-span-2 bg-slate-800 p-6 rounded-2xl border border-slate-700">
                                <h2 className="text-lg font-bold text-white mb-4">{getTitle('Rata-rata Output (Kg/HK)')}</h2>
                                <div className="h-[300px]">
                                    {isRechartsReady && RechartComponents ? (
                                        <RechartComponents.ResponsiveContainer width="100%" height="100%">
                                            <RechartComponents.BarChart data={chartDataProd}>
                                                <RechartComponents.CartesianGrid strokeDasharray="3 3" stroke="#334155" />
                                                <RechartComponents.XAxis dataKey="periode" tick={{fill: '#94a3b8', fontSize: 12}} />
                                                <RechartComponents.YAxis tick={{fill: '#94a3b8', fontSize: 12}} />
                                                <RechartComponents.Tooltip contentStyle={{backgroundColor: '#1e293b', border: '1px solid #475569', borderRadius: '8px'}} />
                                                <RechartComponents.Bar dataKey="avg_prod" fill="#60a5fa" onClick={(data) => handleChartClick(data)} cursor="pointer" />
                                            </RechartComponents.BarChart>
                                        </RechartComponents.ResponsiveContainer>
                                    ) : (
                                        <div className="flex items-center justify-center h-full text-slate-500">Loading chart...</div>
                                    )}
                                </div>
                            </div>
                            
                            <div className="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                                <h2 className="text-lg font-bold text-white mb-4">Summary Akhir {viewMode === 'yearly' ? 'Tahun' : 'Bulan'} Ini</h2>
                                <div className="space-y-3">
                                    {activeData.length > 0 && (
                                        <>
                                            <div className="bg-slate-900/50 p-3 rounded-lg">
                                                <div className="text-xs text-slate-400 mb-1">Total Produksi</div>
                                                <div className="text-2xl font-bold text-white">{activeData[activeData.length - 1].stats.total_ton.toLocaleString()} Ton</div>
                                            </div>
                                            <div className="bg-slate-900/50 p-3 rounded-lg">
                                                <div className="text-xs text-slate-400 mb-1">Total Janjang</div>
                                                <div className="text-2xl font-bold text-white">{activeData[activeData.length - 1].stats.total_jjg.toLocaleString()}</div>
                                            </div>
                                            <div className="bg-slate-900/50 p-3 rounded-lg">
                                                <div className="text-xs text-slate-400 mb-1">Avg Produktivitas</div>
                                                <div className="text-2xl font-bold text-indigo-400">{Math.round(activeData[activeData.length - 1].stats.avg_prod)} Kg/HK</div>
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<DashboardOverview />);
    </script>
    @endverbatim

</body>
</html>