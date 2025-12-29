<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HR Analytics Dashboard - Panen Sawit</title>
    
    @verbatim
    <!-- LIBRARIES -->
    <script src="https://unpkg.com/react@18.2.0/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18.2.0/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/prop-types/prop-types.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/recharts@2.10.3/umd/Recharts.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { slate: { 850: '#1e293b', 900: '#0f172a', 950: '#020617' } },
                    screens: { 'xs': '375px' }
                }
            }
        }
        window.onerror = function(msg, url, line, col, error) {
            if(msg.includes('ResizeObserver')) return false;
            const errorBox = document.getElementById('error-box');
            if (errorBox) { errorBox.style.display = 'block'; document.getElementById('error-msg').innerText = msg; }
            return false;
        };
        
        // API Configuration
        window.API_BASE = '/api/performance';
    </script>

    <style>
        html, body {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
            background-color: #0f172a; 
            color: #f8fafc;
            -webkit-tap-highlight-color: transparent;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #1e293b; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }

        input[type=range] { -webkit-appearance: none; background: transparent; }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none; height: 16px; width: 16px; border-radius: 50%;
            background: #1e293b; border: 2px solid currentColor; margin-top: -6px; 
            box-shadow: 0 0 5px rgba(0,0,0,0.5);
        }
        input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; cursor: pointer; background: #334155; border-radius: 2px; }
    </style>
</head>
<body>

<div id="app-data" style="display:none;"></div>

<div id="root" class="min-h-screen flex items-center justify-center text-slate-500 font-mono text-sm relative px-4">
    <div class="text-center">
        <div class="animate-pulse mb-4">
            <svg class="w-10 h-10 mx-auto text-indigo-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div class="mt-2 text-slate-400">Memuat Dashboard...</div>
        </div>
    </div>
</div>

<div id="error-box" style="display:none;" class="fixed bottom-4 left-4 right-4 max-w-sm mx-auto bg-rose-900/95 border border-rose-700 text-white p-4 rounded-lg shadow-2xl z-50 backdrop-blur-sm">
    <h4 class="font-bold text-sm mb-1">Terjadi Kesalahan</h4>
    <p id="error-msg" class="text-xs font-mono break-words opacity-90"></p>
    <button onclick="document.getElementById('error-box').style.display='none'" class="mt-2 text-xs bg-rose-950/50 hover:bg-rose-950 px-2 py-1 rounded border border-rose-800">Tutup</button>
</div>

<script type="text/babel">
    const { useState, useMemo, useEffect, useRef } = React;
    const { ScatterChart, Scatter, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, ReferenceLine, ReferenceArea, Cell, Label } = window.Recharts;

    // Icons component - same as before
    const Icons = {
        Chart: () => <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>,
        Users: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>,
        Star: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>,
        Trending: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>,
        Briefcase: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>,
        Alert: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>,
        Search: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>,
        Calculator: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="14" x2="16" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/></svg>,
        Download: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>,
        Upload: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>,
        Refresh: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>,
        Image: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>,
        FileExcel: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/></svg>,
        Filter: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>,
        Code: () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
    };

    // StatCard component - reuse from original
    const StatCard = ({ title, value, subtext, color, icon: Icon, onClick, isActive }) => {
        const themes = {
            gray:   { base: 'from-slate-800 to-slate-900 border-slate-700', active: 'ring-2 ring-slate-500 from-slate-800 to-slate-700', text: 'text-slate-200', icon: 'bg-slate-700 text-slate-300' },
            green:  { base: 'from-emerald-900/40 to-slate-900 border-emerald-800/50', active: 'ring-2 ring-emerald-500/50 from-emerald-900/60 to-slate-900', text: 'text-emerald-400', icon: 'bg-emerald-900/50 text-emerald-400' },
            blue:   { base: 'from-blue-900/40 to-slate-900 border-blue-800/50', active: 'ring-2 ring-blue-500/50 from-blue-900/60 to-slate-900', text: 'text-blue-400', icon: 'bg-blue-900/50 text-blue-400' },
            yellow: { base: 'from-amber-900/40 to-slate-900 border-amber-800/50', active: 'ring-2 ring-amber-500/50 from-amber-900/60 to-slate-900', text: 'text-amber-400', icon: 'bg-amber-900/50 text-amber-400' },
            red:    { base: 'from-rose-900/40 to-slate-900 border-rose-800/50', active: 'ring-2 ring-rose-500/50 from-rose-900/60 to-slate-900', text: 'text-rose-400', icon: 'bg-rose-900/50 text-rose-400' },
        };
        const t = themes[color] || themes.gray;
        const containerClass = isActive ? t.active : `${t.base} hover:shadow-lg hover:shadow-slate-900 hover:-translate-y-1`;
        
        return (
            <div onClick={onClick} className={`relative overflow-hidden p-3 sm:p-5 rounded-2xl border shadow-md cursor-pointer transition-all duration-300 bg-gradient-to-br min-w-0 h-full flex flex-col justify-between ${containerClass}`}>
                <div className="flex justify-between items-start z-10 relative h-full">
                    <div className="min-w-0 flex-1 flex flex-col justify-center"> 
                        <h3 className="text-[9px] xs:text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 sm:mb-1 whitespace-normal leading-tight">{title}</h3>
                        <div className={`text-lg xs:text-xl sm:text-3xl font-black tracking-tight ${t.text} whitespace-normal break-words leading-none my-0.5`}>{value}</div>
                        <p className="text-[9px] xs:text-[10px] sm:text-xs text-slate-500 mt-0.5 sm:mt-2 font-medium leading-tight whitespace-normal">{subtext}</p>
                    </div>
                    <div className={`p-1.5 sm:p-2.5 rounded-xl border border-white/5 ${t.icon} ml-2 flex-shrink-0 self-start`}>
                        <Icon />
                    </div>
                </div>
                <div className={`absolute -bottom-4 -right-4 w-16 h-16 sm:w-24 sm:h-24 rounded-full opacity-10 blur-xl ${t.icon.split(' ')[0]} z-0`}></div>
            </div>
        );
    };

    // SimulationInput component - reuse from original
    const SimulationInput = ({ title, value, onChange, color, avgProd }) => {
        const theme = { green: 'text-emerald-400', blue: 'text-blue-400', yellow: 'text-amber-400', red: 'text-rose-400' }[color];
        return (
            <div className="bg-slate-800 p-3 rounded-xl border border-slate-700 shadow-sm min-w-0">
                <div className="flex justify-between items-start mb-2">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <div className={`w-2 h-2 rounded-full ${theme.replace('text', 'bg')} shadow-[0_0_8px_currentColor]`}></div>
                            <span className={`text-xs font-bold uppercase tracking-wide ${theme}`}>{title}</span>
                        </div>
                        <div className="text-[10px] text-slate-400 font-mono ml-4"><span className="text-slate-200 font-bold">{Math.round(avgProd)}</span> Jjg/HK</div>
                    </div>
                </div>
                <div className="mb-1 mt-3">
                     <div className="flex justify-between text-[10px] text-slate-500 uppercase font-bold mb-1"><span>Target HK</span><span className="text-slate-300">{value} Hari</span></div>
                     <input type="range" min="1" max="31" step="1" value={value} onChange={(e) => onChange(Number(e.target.value))} className={`w-full h-1.5 bg-slate-700 rounded-lg appearance-none cursor-pointer ${theme}`} />
                </div>
            </div>
        );
    };

    // TooltipCustom component - reuse from original
    const TooltipCustom = ({ active, payload }) => {
        if (active && payload && payload.length) {
            const d = payload[0].payload;
            const badgeColor = d.Category === 'Star' ? 'bg-emerald-900/40 text-emerald-300 border-emerald-700' : d.Category === 'Potential' ? 'bg-blue-900/40 text-blue-300 border-blue-700' : d.Category === 'Workhorse' ? 'bg-amber-900/40 text-amber-300 border-amber-700' : 'bg-rose-900/40 text-rose-300 border-rose-700';
            return (
                <div className="bg-slate-800/95 backdrop-blur-md p-3 sm:p-4 border border-slate-700 shadow-2xl rounded-xl text-xs sm:text-sm z-50 min-w-[150px] sm:min-w-[200px]">
                    <div className="flex justify-between items-start mb-2 sm:mb-3">
                        <div>
                            <p className="font-bold text-slate-100 text-sm sm:text-base">{d.NAMA}</p>
                            <div className="flex items-center gap-2">
                                <span className="text-[10px] sm:text-xs px-1.5 py-0.5 rounded bg-slate-700 text-slate-300 border border-slate-600 font-mono">AFD: {d.AFD}</span>
                                <p className="text-[10px] sm:text-xs text-slate-500">ID: {d.ID}</p>
                            </div>
                        </div>
                        <span className={`text-[9px] sm:text-[10px] px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full font-bold border ${badgeColor}`}>{d.Category}</span>
                    </div>
                    <div className="space-y-1">
                        <div className="flex justify-between text-[10px] sm:text-xs text-slate-400"><span>Kehadiran:</span> <span className="font-bold font-mono text-slate-200">{d.HK}</span></div>
                        <div className="flex justify-between text-[10px] sm:text-xs text-slate-400"><span>Total Janjang:</span> <span className="font-bold font-mono text-slate-200">{d.JJG}</span></div>
                        <div className="flex justify-between text-[10px] sm:text-xs text-slate-400"><span>Produktivitas:</span> <span className="font-bold font-mono text-slate-200">{d.Kg_per_HK.toFixed(0)} Kg/HK</span></div>
                    </div>
                </div>
            );
        }
        return null;
    };

    function App() {
        const [processedData, setProcessedData] = useState([]);
        const [benchmarks, setBenchmarks] = useState({ hk: 0, prod: 0 });
        const [filterCategory, setFilterCategory] = useState('All');
        const [filterAFD, setFilterAFD] = useState('All');
        const [filterPeriode, setFilterPeriode] = useState([]);
        const [searchInput, setSearchInput] = useState(''); // Input value untuk UI
        const [searchTerm, setSearchTerm] = useState(''); // Actual search term untuk filter (dengan debounce)
        
        // Get periode from URL if exists
        const urlPeriode = window.location.pathname.split('/').pop();
        const [bjr, setBjr] = useState(15.0);
        const [targetHKE, setTargetHKE] = useState({ Star: 26, Workhorse: 26, Potential: 26, Underperformer: 26 });
        const [filters, setFilters] = useState({ periodes: [], afds: [], batches: [] });
        const [loading, setLoading] = useState(true);
        const dashboardRef = useRef(null);
        const tableContainerRef = useRef(null);

        // Fetch filters on mount
        useEffect(() => {
            fetchFilters();
        }, []);

        // Fetch data when filters change
        useEffect(() => {
            if (filterPeriode.length > 0 || filters.periodes.length === 0) {
                fetchData();
            }
        }, [filterPeriode]);

        const fetchFilters = async () => {
            try {
                const response = await fetch(window.API_BASE + '/filters');
                const data = await response.json();
                setFilters(data);
                
                // Auto-select periode from URL or latest
                if (urlPeriode && urlPeriode !== 'analisis' && data.periodes && data.periodes.includes(urlPeriode)) {
                    setFilterPeriode([urlPeriode]);
                } else if (data.periodes && data.periodes.length > 0) {
                    setFilterPeriode([data.periodes[0]]);
                }
            } catch (error) {
                console.error('Error fetching filters:', error);
            }
        };

        const fetchData = async () => {
            setLoading(true);
            try {
                const params = new URLSearchParams();
                if (filterPeriode.length > 0) {
                    filterPeriode.forEach(p => params.append('periode[]', p));
                }
                
                const response = await fetch(window.API_BASE + '/data?' + params.toString());
                const result = await response.json();
                
                let transformedData = result.data.map(item => ({
                    ID: item.nik || item.id,
                    NAMA: item.nama ? String(item.nama) : '-', 
                    AFD: item.afd ? String(item.afd).trim() : '?',
                    HK: parseFloat(item.hk),
                    JJG: parseInt(item.jjg),
                    TON: parseFloat(item.ton),
                    Kg_per_HK: parseFloat(item.kg_per_hk),
                    Category: item.Category || null 
                }));
                
                // Calculate Category if not provided (like in reference HTML)
                if (transformedData.length > 0) {
                    const avgHK = result.benchmarks?.hk || transformedData.reduce((sum, d) => sum + d.HK, 0) / transformedData.length;
                    const avgProd = result.benchmarks?.prod || transformedData.reduce((sum, d) => sum + d.Kg_per_HK, 0) / transformedData.length;
                    
                    transformedData = transformedData.map(item => {
                        let cat = 'Underperformer';
                        if (item.HK >= avgHK && item.Kg_per_HK >= avgProd) cat = 'Star';
                        else if (item.HK >= avgHK && item.Kg_per_HK < avgProd) cat = 'Workhorse';
                        else if (item.HK < avgHK && item.Kg_per_HK >= avgProd) cat = 'Potential';
                        return { ...item, Category: cat };
                    });
                }
                
                setProcessedData(transformedData);
                setBenchmarks(result.benchmarks || { hk: 0, prod: 0 });
            } catch (error) {
                console.error('Error fetching data:', error);
                alert('Gagal memuat data. Pastikan sudah ada data yang diupload di halaman Admin.');
            } finally {
                setLoading(false);
            }
        };

        // Data yang sudah difilter AFD dan Search (untuk stat cards)
        const filteredForStats = useMemo(() => {
            if (!processedData || processedData.length === 0) return [];
            return processedData.filter(d => {
                const matchAFD = filterAFD === 'All' || d.AFD === filterAFD;
                const matchSearch = !searchTerm || (d.NAMA && d.NAMA.toLowerCase().includes(searchTerm.toLowerCase()));
                return matchAFD && matchSearch;
            });
        }, [processedData, filterAFD, searchTerm]);

        // Stats dihitung dari data yang sudah difilter AFD dan Search
        const stats = useMemo(() => {
            const s = { Star: { count: 0, totalJJG: 0, totalHK: 0 }, Workhorse: { count: 0, totalJJG: 0, totalHK: 0 }, Potential: { count: 0, totalJJG: 0, totalHK: 0 }, Underperformer: { count: 0, totalJJG: 0, totalHK: 0 } };
            filteredForStats.forEach(d => { if (s[d.Category]) { s[d.Category].count++; s[d.Category].totalJJG += d.JJG; s[d.Category].totalHK += d.HK; } });
            Object.keys(s).forEach(k => { s[k].avgJJGperHK = s[k].totalHK > 0 ? s[k].totalJJG / s[k].totalHK : 0; });
            return s;
        }, [filteredForStats]);

        // Data yang sudah difilter lengkap (untuk tabel dan grafik)
        const filteredList = useMemo(() => {
            if (!filteredForStats || filteredForStats.length === 0) return [];
            
            return filteredForStats.filter(d => {
                const matchCategory = filterCategory === 'All' || d.Category === filterCategory;
                return matchCategory;
            });
        }, [filteredForStats, filterCategory]);
        
        // Debounce search input
        useEffect(() => {
            const timer = setTimeout(() => {
                setSearchTerm(searchInput);
            }, 300); // 300ms delay
            
            return () => clearTimeout(timer);
        }, [searchInput]);
        
        // Auto-scroll tabel ke atas saat filter berubah
        useEffect(() => {
            if (tableContainerRef.current) {
                tableContainerRef.current.scrollTop = 0;
            }
        }, [filteredList]);

        const uniqueAFDs = useMemo(() => { 
            const list = [...new Set(processedData.map(d => d.AFD))].filter(Boolean).sort(); 
            return ['All', ...list]; 
        }, [processedData]);

        const simulation = useMemo(() => {
            let cTon = 0, pTon = 0, cJJG = 0, pJJG = 0;
            Object.keys(stats).forEach(cat => {
                const s = stats[cat];
                cJJG += s.totalJJG;
                cTon += (s.totalJJG * bjr) / 1000;
                const pCatJJG = s.count * targetHKE[cat] * s.avgJJGperHK;
                pJJG += pCatJJG;
                pTon += (pCatJJG * bjr) / 1000;
            });
            return { currentTon: cTon, projectedTon: pTon, diffTon: pTon - cTon, currentJJG: cJJG, projectedJJG: pJJG, diffJJG: pJJG - cJJG };
        }, [stats, targetHKE, bjr]);

        const exportPNG = async () => {
            if(!dashboardRef.current || !tableContainerRef.current) return;
            tableContainerRef.current.style.maxHeight = 'none';
            tableContainerRef.current.style.overflow = 'visible';
            try {
                const canvas = await html2canvas(dashboardRef.current, { scale: 2, backgroundColor: '#0f172a', windowWidth: dashboardRef.current.scrollWidth, windowHeight: dashboardRef.current.scrollHeight });
                const link = document.createElement('a');
                link.download = 'HR_Dashboard.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            } catch (err) { console.error("Export PNG Error", err); alert("Gagal export PNG");
            } finally { tableContainerRef.current.style.maxHeight = ''; tableContainerRef.current.style.overflow = ''; }
        };

        const exportPDF = async () => {
            if(!dashboardRef.current || !tableContainerRef.current) return;
            tableContainerRef.current.style.maxHeight = 'none';
            tableContainerRef.current.style.overflow = 'visible';
            try {
                const canvas = await html2canvas(dashboardRef.current, { scale: 2, backgroundColor: '#0f172a', windowWidth: dashboardRef.current.scrollWidth, windowHeight: dashboardRef.current.scrollHeight });
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jspdf.jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const imgHeight = (canvas.height * pdfWidth) / canvas.width;
                let heightLeft = imgHeight; let position = 0;
                pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, imgHeight);
                heightLeft -= pdfHeight;
                while (heightLeft > 0) { position = heightLeft - imgHeight; pdf.addPage(); pdf.addImage(imgData, 'PNG', 0, position - heightLeft - position, pdfWidth, imgHeight); heightLeft -= pdfHeight; }
                pdf.save("Laporan_Lengkap_HR.pdf");
            } catch (err) { console.error("Export Error", err);
            } finally { tableContainerRef.current.style.maxHeight = ''; tableContainerRef.current.style.overflow = ''; }
        };

        const exportHTML = async () => {
            // This will export current filtered data as standalone HTML
            const currentData = { data: filteredList, config: { bjr, targetHKE } };
            const jsonStr = JSON.stringify(currentData);
            const binaryStr = String.fromCharCode(...new Uint8Array(new TextEncoder().encode(jsonStr)));
            const b64 = btoa(binaryStr);
            
            // Fetch the original template
            const response = await fetch('/detail_analisis_performa.html');
            let htmlContent = await response.text();
            
            // Inject data
            const regexDiv = /<div id="app-data" style="display:none;">[\s\S]*?<\/div>/;
            const newDiv = `<div id="app-data" style="display:none;">${b64}</div>`;
            if (regexDiv.test(htmlContent)) { 
                htmlContent = htmlContent.replace(regexDiv, newDiv); 
            } else { 
                htmlContent = htmlContent.replace('<body>', `<body>\n${newDiv}`); 
            }
            
            // Download
            const blob = new Blob([htmlContent], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a'); 
            link.href = url; 
            link.download = `HR_Dashboard_${filterPeriode.join('_')}.html`;
            document.body.appendChild(link); 
            link.click(); 
            document.body.removeChild(link); 
            URL.revokeObjectURL(url);
        };

        const getHex = (cat) => { if(cat === 'Star') return '#34d399'; if(cat === 'Potential') return '#60a5fa'; if(cat === 'Workhorse') return '#fbbf24'; return '#f87171'; };
        const formatTon = (val) => val.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        const maxHK = useMemo(() => {
            if (filteredList.length === 0) return 32;
            const max = Math.max(...filteredList.map(d => d.HK));
            return Math.max(max, 30) + 2;
        }, [filteredList]);
        const maxProd = useMemo(() => {
            if (filteredList.length === 0) return 1100;
            const max = Math.max(...filteredList.map(d => d.Kg_per_HK));
            return Math.max(max, 1000) + 100;
        }, [filteredList]);

        if (loading) {
            return (
                <div className="min-h-screen flex items-center justify-center">
                    <div className="text-center">
                        <div className="animate-spin w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                        <p className="text-slate-400">Memuat data...</p>
                    </div>
                </div>
            );
        }

        return (
            <div className="min-h-screen pb-20 text-slate-200 font-sans selection:bg-indigo-500 selection:text-white w-full overflow-hidden">
                <nav className="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 sticky top-0 z-40 px-3 sm:px-6 py-3 shadow-lg shadow-black/20 w-full">
                    <div className="flex flex-col sm:flex-row justify-between items-center w-full">
                        <div className="flex items-center gap-3 mb-3 sm:mb-0 w-full sm:w-auto">
                            <div className="bg-gradient-to-br from-indigo-500 to-blue-600 p-2 rounded-xl text-white shadow-lg shadow-indigo-500/30 ring-1 ring-white/10 shrink-0"><Icons.Chart /></div>
                            <div className="min-w-0">
                                <h1 className="text-base sm:text-xl font-bold text-slate-100 tracking-tight truncate">HR Analytics</h1>
                                <p className="hidden sm:block text-xs text-slate-400 font-medium">Performance Monitoring Dashboard</p>
                            </div>
                        </div>
                        
                        <div className="w-full sm:w-auto overflow-x-auto no-scrollbar pb-1 sm:pb-0">
                            <div className="flex items-center gap-2 w-max mx-auto sm:mx-0">
                                <a href="/" className="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 rounded-lg text-xs font-medium transition-colors">
                                    ← Dashboard
                                </a>
                                
                                <select value={filterPeriode[0] || ''} onChange={(e) => setFilterPeriode(e.target.value ? [e.target.value] : [])} className="px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs font-medium">
                                    <option value="">Pilih Periode</option>
                                    {filters.periodes.map(p => <option key={p} value={p}>{p}</option>)}
                                </select>
                                
                                <div className="flex bg-slate-800 p-1 rounded-lg border border-slate-700 gap-1">
                                    <button onClick={exportHTML} className="p-2 hover:bg-slate-700 hover:shadow-sm rounded-md transition-all text-pink-400 hover:text-pink-300" title="Export HTML"><Icons.Code /></button>
                                    {/* <button onClick={exportPNG} className="p-2 hover:bg-slate-700 hover:shadow-sm rounded-md transition-all text-emerald-400 hover:text-emerald-300" title="Export PNG"><Icons.Image /></button>
                                    <button onClick={exportPDF} className="p-2 hover:bg-slate-700 hover:shadow-sm rounded-md transition-all text-slate-400 hover:text-white" title="Export PDF"><Icons.Download /></button>} */}
                                    <div className="w-px bg-slate-700 mx-1 my-1"></div>
                                    <a href="/admin" className="p-2 hover:bg-slate-700 hover:shadow-sm rounded-md transition-all text-indigo-400 hover:text-indigo-300" title="Admin"><Icons.Upload /></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <div ref={dashboardRef} className="max-w-7xl mx-auto px-3 sm:px-6 pt-6 w-full">
                    <div className="grid grid-cols-2 lg:grid-cols-5 gap-2 sm:gap-3 mb-6 w-full">
                        <div className="col-span-2 lg:col-span-1 min-w-0"> 
                             <StatCard title="Total Karyawan" value={filteredForStats.length} subtext={filterAFD !== 'All' ? `AFD ${filterAFD}` : 'Total Populasi'} color="gray" icon={Icons.Users} isActive={filterCategory === 'All'} onClick={() => setFilterCategory('All')} />
                        </div>
                        <StatCard title="Star Player" value={stats.Star.count} subtext="Rajin Masuk, Tinggi Prod." color="green" icon={Icons.Star} isActive={filterCategory === 'Star'} onClick={() => setFilterCategory('Star')} />
                        <StatCard title="Potential" value={stats.Potential.count} subtext="Jarang Masuk, Tinggi Prod." color="blue" icon={Icons.Trending} isActive={filterCategory === 'Potential'} onClick={() => setFilterCategory('Potential')} />
                        <StatCard title="Workhorse" value={stats.Workhorse.count} subtext="Rajin Masuk, Rendah Prod." color="yellow" icon={Icons.Briefcase} isActive={filterCategory === 'Workhorse'} onClick={() => setFilterCategory('Workhorse')} />
                        <StatCard title="Underperformer" value={stats.Underperformer.count} subtext="Jarang Masuk, Rendah Prod." color="red" icon={Icons.Alert} isActive={filterCategory === 'Underperformer'} onClick={() => setFilterCategory('Underperformer')} />
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 w-full min-w-0">
                        <div className="lg:col-span-2 bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-700 shadow-xl shadow-black/20 relative overflow-hidden min-w-0">
                            <div className="flex flex-col sm:flex-row justify-between items-start mb-6">
                                <div className="mb-2 sm:mb-0 min-w-0">
                                    <h2 className="text-lg font-bold text-slate-100 truncate">Matriks Produktivitas</h2>
                                    <p className="text-sm text-slate-400 truncate">Pemetaan Kinerja Karyawan</p>
                                </div>
                                <div className="text-sm sm:text-lg font-mono text-slate-400 bg-slate-900/50 px-3 py-1.5 rounded-lg border border-slate-700 whitespace-nowrap">
                                    Avg: {benchmarks.hk.toFixed(1)} HKE | {Math.round(benchmarks.prod)} Kg
                                </div>
                            </div>
                            <div className="h-[300px] sm:h-[450px] w-full relative z-10 min-w-0">
                                <ResponsiveContainer width="100%" height="100%">
                                    <ScatterChart margin={{ top: 20, right: 20, bottom: 20, left: 0 }}>
                                        <CartesianGrid strokeDasharray="3 3" stroke="#334155" />
                                        <XAxis type="number" dataKey="HK" name="HK" domain={[0, maxHK]} unit=" HK" tick={{fontSize: 10, fill: '#94a3b8'}} tickLine={false} axisLine={{stroke: '#475569'}} />
                                        <YAxis type="number" dataKey="Kg_per_HK" name="Prod" domain={[0, maxProd]} unit=" Kg" tick={{fontSize: 10, fill: '#94a3b8'}} tickLine={false} axisLine={{stroke: '#475569'}} />
                                        <Tooltip content={<TooltipCustom />} cursor={{ strokeDasharray: '3 3' }} />
                                        <ReferenceArea x1={0} x2={benchmarks.hk} y1={benchmarks.prod} y2={maxProd} fill="#1e3a8a" fillOpacity={0.15} />
                                        <ReferenceArea x1={benchmarks.hk} x2={maxHK} y1={benchmarks.prod} y2={maxProd} fill="#064e3b" fillOpacity={0.15} />
                                        <ReferenceArea x1={0} x2={benchmarks.hk} y1={0} y2={benchmarks.prod} fill="#7f1d1d" fillOpacity={0.15} />
                                        <ReferenceArea x1={benchmarks.hk} x2={maxHK} y1={0} y2={benchmarks.prod} fill="#78350f" fillOpacity={0.15} />
                                        <ReferenceLine x={benchmarks.hk} stroke="#64748b" strokeDasharray="4 4" strokeWidth={1}>
                                            <Label value="Avg HK" position="insideTopRight" offset={10} fontSize={10} fill="#94a3b8" />
                                        </ReferenceLine>
                                        <ReferenceLine y={benchmarks.prod} stroke="#64748b" strokeDasharray="4 4" strokeWidth={1}>
                                            <Label value="Avg Prod" position="insideTopRight" offset={10} fontSize={10} fill="#94a3b8" />
                                        </ReferenceLine>
                                        <Scatter name="Karyawan" data={filteredList} animationDuration={1000}>
                                            {filteredList.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={getHex(entry.Category)} strokeWidth={0} />
                                            ))}
                                        </Scatter>
                                    </ScatterChart>
                                </ResponsiveContainer>
                            </div>
                            
                            <div className="mt-4 sm:absolute sm:bottom-6 sm:right-6 bg-slate-900/80 backdrop-blur border border-slate-700 p-2 rounded-lg text-[10px] flex flex-wrap gap-3 z-20 shadow-lg text-slate-300 justify-center sm:justify-start">
                                <div className="flex items-center gap-1"><div className="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_5px_currentColor]"></div>Star</div>
                                <div className="flex items-center gap-1"><div className="w-2 h-2 rounded-full bg-blue-400 shadow-[0_0_5px_currentColor]"></div>Potential</div>
                                <div className="flex items-center gap-1"><div className="w-2 h-2 rounded-full bg-amber-400 shadow-[0_0_5px_currentColor]"></div>Workhorse</div>
                                <div className="flex items-center gap-1"><div className="w-2 h-2 rounded-full bg-rose-400 shadow-[0_0_5px_currentColor]"></div>Underperf.</div>
                            </div>
                        </div>

                        <div className="lg:col-span-1 flex flex-col gap-6 min-w-0">
                            <div className="bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-700 shadow-xl shadow-black/20 min-w-0">
                                <div className="flex items-center gap-3 mb-6 pb-4 border-b border-slate-700">
                                    <div className="p-2 bg-indigo-900/40 text-indigo-400 rounded-lg border border-indigo-500/20"><Icons.Calculator /></div>
                                    <div className="min-w-0">
                                        <h2 className="font-bold text-slate-100 truncate">Simulasi Produksi</h2>
                                        <p className="text-xs text-slate-400 truncate">Estimasi kenaikan tonase</p>
                                    </div>
                                </div>
                                <div className="flex justify-between items-center bg-slate-900/50 p-3 rounded-xl border border-slate-700 mb-6 min-w-0">
                                    <label className="text-xs font-bold text-slate-400 uppercase tracking-wide ml-1 truncate">Basis BJR (Kg)</label>
                                    <input type="number" value={bjr} onChange={(e) => setBjr(parseFloat(e.target.value))} className="w-20 text-right font-bold bg-slate-800 border border-slate-600 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none text-indigo-400 shadow-sm" />
                                </div>
                                <div className="flex flex-col gap-3 mb-6 min-w-0">
                                    <SimulationInput title="Star" count={stats.Star.count} color="green" value={targetHKE.Star} avgProd={stats.Star.avgJJGperHK} onChange={(val) => setTargetHKE({...targetHKE, Star: val})} />
                                    <SimulationInput title="Potential" count={stats.Potential.count} color="blue" value={targetHKE.Potential} avgProd={stats.Potential.avgJJGperHK} onChange={(val) => setTargetHKE({...targetHKE, Potential: val})} />
                                    <SimulationInput title="Workhorse" count={stats.Workhorse.count} color="yellow" value={targetHKE.Workhorse} avgProd={stats.Workhorse.avgJJGperHK} onChange={(val) => setTargetHKE({...targetHKE, Workhorse: val})} />
                                    <SimulationInput title="Underperf." count={stats.Underperformer.count} color="red" value={targetHKE.Underperformer} avgProd={stats.Underperformer.avgJJGperHK} onChange={(val) => setTargetHKE({...targetHKE, Underperformer: val})} />
                                </div>
                                <div className="bg-gradient-to-br from-slate-950 to-slate-900 border border-slate-800 text-white p-5 rounded-xl shadow-2xl relative overflow-hidden min-w-0">
                                    <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-500 opacity-10 blur-3xl rounded-full -mr-10 -mt-10"></div>
                                    <div className="relative z-10 space-y-3">
                                        <div className="flex justify-between items-center">
                                            <div className="text-xs text-slate-400 font-bold uppercase tracking-wide">Aktual</div>
                                            <div className="text-right">
                                                 <div className="text-sm font-bold font-mono text-slate-200">{formatTon(simulation.currentTon)} Ton</div>
                                                 <div className="text-[10px] text-slate-500 font-mono">{Math.round(simulation.currentJJG).toLocaleString()} Jjg</div>
                                            </div>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <div className="text-xs text-emerald-400 font-bold uppercase tracking-wide">Potensi</div>
                                            <div className="text-right">
                                                 <div className={`text-sm font-bold font-mono ${simulation.diffTon >= 0 ? 'text-emerald-400' : 'text-rose-400'}`}>{simulation.diffTon > 0 ? '+' : ''}{formatTon(simulation.diffTon)} Ton</div>
                                                 <div className={`text-[10px] font-mono ${simulation.diffTon >= 0 ? 'text-emerald-600' : 'text-rose-600'}`}>{simulation.diffJJG > 0 ? '+' : ''}{Math.round(simulation.diffJJG).toLocaleString()} Jjg</div>
                                            </div>
                                        </div>
                                        <div className="h-px bg-slate-800 w-full"></div>
                                        <div className="flex justify-between items-center">
                                            <div className="text-xs text-indigo-400 font-bold uppercase tracking-wide">Total Proyeksi</div>
                                            <div className="text-right">
                                                 <div className="text-lg font-black font-mono text-white">{formatTon(simulation.projectedTon)} Ton</div>
                                                 <div className="text-xs text-indigo-300 font-mono">{Math.round(simulation.projectedJJG).toLocaleString()} Jjg</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-slate-800 rounded-2xl border border-slate-700 shadow-xl shadow-black/20 overflow-hidden w-full min-w-0">
                        <div className="p-4 sm:p-5 border-b border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-800">
                            <div className="flex items-center gap-2 w-full sm:w-auto">
                                <div className="bg-indigo-900/40 p-1.5 rounded text-indigo-400 border border-indigo-500/20 shrink-0"><Icons.Search /></div>
                                <h3 className="font-bold text-slate-200 truncate">Detail Data Karyawan ({filteredList.length})</h3>
                            </div>
                            <div className="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                <div className="relative group w-full sm:w-32">
                                    <select value={filterAFD} onChange={(e) => setFilterAFD(e.target.value)} className="appearance-none w-full pl-4 pr-8 py-2 text-sm bg-slate-900/50 border border-slate-600 rounded-xl focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500 outline-none text-slate-200 cursor-pointer">
                                        <option value="All">Semua AFD</option>
                                        {uniqueAFDs.filter(a => a !== 'All').map(afd => (<option key={afd} value={afd}>{afd}</option>))}
                                    </select>
                                    <div className="absolute right-3 top-2.5 text-slate-500 pointer-events-none"><Icons.Filter /></div>
                                </div>
                                <div className="relative w-full sm:w-72 group">
                                    <input 
                                        type="text" 
                                        placeholder="Cari nama karyawan..." 
                                        value={searchInput} 
                                        onChange={(e) => setSearchInput(e.target.value)} 
                                        className="w-full pl-10 pr-4 py-2 text-sm bg-slate-900/50 border border-slate-600 rounded-xl focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none text-slate-200 placeholder-slate-500" 
                                    />
                                    <div className="absolute left-3 top-2.5 text-slate-500 group-focus-within:text-indigo-400 transition-colors"><Icons.Search /></div>
                                    {searchInput !== searchTerm && (
                                        <div className="absolute right-3 top-2.5">
                                            <div className="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div ref={tableContainerRef} className="w-full overflow-auto max-h-[600px]">
                            <table className="w-full text-left border-collapse table-fixed">
                                <thead className="text-[9px] sm:text-xs text-slate-400 uppercase bg-slate-800/90 backdrop-blur sticky top-0 z-20 shadow-sm">
                                    <tr>
                                        <th className="w-[10%] sm:w-24 px-1 py-2 sm:px-6 sm:py-4 font-semibold tracking-wide border-b border-slate-700">AFD</th>
                                        <th className="w-[28%] sm:w-auto px-1 py-2 sm:px-6 sm:py-4 font-semibold tracking-wide border-b border-slate-700">Nama <span className="hidden sm:inline">Karyawan</span></th>
                                        <th className="w-[18%] sm:w-auto px-1 py-2 sm:px-6 sm:py-4 text-center font-semibold tracking-wide border-b border-slate-700">Kat. <span className="hidden sm:inline">Kategori</span></th>
                                        <th className="w-[10%] sm:w-auto px-1 py-2 sm:px-6 sm:py-4 text-center font-semibold tracking-wide border-b border-slate-700">HK</th>
                                        <th className="w-[14%] sm:w-auto px-1 py-2 sm:px-6 sm:py-4 text-right font-semibold tracking-wide border-b border-slate-700">Jjg <span className="hidden sm:inline">Total Janjang</span></th>
                                        <th className="w-[20%] sm:w-auto px-1 py-2 sm:px-6 sm:py-4 text-right font-semibold tracking-wide border-b border-slate-700">Prod <span className="hidden sm:inline">(Kg/HK)</span></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-700/50 text-[9px] xs:text-[10px] sm:text-sm">
                                    {filteredList.map((row, index) => (
                                        <tr key={`${row.ID}-${index}`} className="hover:bg-slate-700/40 transition-colors group">
                                            <td className="px-1 py-2 sm:px-6 sm:py-3 font-medium text-slate-400 group-hover:text-slate-300 transition-colors text-center">
                                                <span className={`${filterAFD !== 'All' && row.AFD === filterAFD ? 'text-blue-400 font-bold' : ''}`}>
                                                    {row.AFD}
                                                </span>
                                            </td>
                                            <td className="px-1 py-2 sm:px-6 sm:py-3 font-medium text-slate-300 group-hover:text-white transition-colors whitespace-normal break-words leading-tight">{row.NAMA}</td>
                                            <td className="px-1 py-2 sm:px-6 sm:py-3 text-center">
                                                <span className={`block sm:inline px-1 py-0.5 sm:px-2.5 sm:py-1 rounded-full font-bold uppercase tracking-wider border text-[8px] sm:text-[10px] whitespace-normal break-words leading-tight ${
                                                    row.Category === 'Star' ? 'bg-emerald-900/40 text-emerald-400 border-emerald-800/50' :
                                                    row.Category === 'Potential' ? 'bg-blue-900/40 text-blue-400 border-blue-800/50' :
                                                    row.Category === 'Workhorse' ? 'bg-amber-900/40 text-amber-400 border-amber-800/50' :
                                                    'bg-rose-900/40 text-rose-400 border-rose-800/50'
                                                }`}>{row.Category}</span>
                                            </td>
                                            <td className="px-1 py-2 sm:px-6 sm:py-3 text-center text-slate-400 font-mono group-hover:text-slate-300">{row.HK}</td>
                                            <td className="px-1 py-2 sm:px-6 sm:py-3 text-right text-slate-400 font-mono group-hover:text-slate-300">{row.JJG.toLocaleString('id-ID')}</td>
                                            <td className="px-1 py-2 sm:px-6 sm:py-3 text-right font-bold text-slate-300 font-mono group-hover:text-white">{Math.round(row.Kg_per_HK).toLocaleString('id-ID')}</td>
                                        </tr>
                                    ))}
                                    {filteredList.length === 0 && (
                                        <tr>
                                            <td colSpan="6" className="text-center py-12 text-slate-500 flex flex-col items-center justify-center">
                                                <Icons.Search />
                                                <span className="mt-2">Tidak ada data yang cocok</span>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                        <div className="p-4 bg-slate-900/30 border-t border-slate-700 text-xs text-slate-500 space-y-2">
                            <div className="flex justify-between font-medium">
                                <span>Total: {filteredForStats.length} data {filterAFD !== 'All' || searchTerm ? '(setelah filter AFD/Search)' : ''}</span>
                                <span>Menampilkan: {filteredList.length} data {filterCategory !== 'All' ? '(setelah filter kategori)' : ''}</span>
                            </div>
                            {(filterCategory !== 'All' || filterAFD !== 'All' || searchTerm) && (
                                <div className="flex flex-wrap gap-2 pt-2 border-t border-slate-700">
                                    <span className="text-slate-400">Filter Aktif:</span>
                                    {filterCategory !== 'All' && (
                                        <span className="px-2 py-1 bg-indigo-900/40 text-indigo-300 rounded text-xs border border-indigo-700">
                                            Kategori: {filterCategory}
                                        </span>
                                    )}
                                    {filterAFD !== 'All' && (
                                        <span className="px-2 py-1 bg-blue-900/40 text-blue-300 rounded text-xs border border-blue-700">
                                            AFD: {filterAFD}
                                        </span>
                                    )}
                                    {searchTerm && (
                                        <span className="px-2 py-1 bg-emerald-900/40 text-emerald-300 rounded text-xs border border-emerald-700">
                                            Cari: "{searchTerm}"
                                        </span>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);
</script>
@endverbatim

</body>
</html>

