<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $action = $_GET['action'] ?? 'all';
    
    switch ($action) {
        case 'panen':
            // Get panen data from database - optimized query with koreksi
            $query = "SELECT 
                        dp.id, dp.tanggal_pemeriksaan as date, dp.afdeling, dp.blok, 
                        dp.no_tph as noTPH, dp.no_ancak as noAncak, dp.nama_pemanen as namaPemanen, 
                        dp.nik_pemanen as nikPemanen, dp.nama_kerani as namaKerani, 
                        COALESCE(dp.jumlah_janjang, 0) as jmlhJanjang, 
                        COALESCE(dp.koreksi_panen, 0) as koreksiPanen,
                        COALESCE(dp.bjr, 0) as avgBjr, 
                        COALESCE(dp.kg_total, 0) as totalKg, 
                        COALESCE(dp.kg_brd, 0) as kgBerondolan, 
                        dp.jam, dp.koordinat, dp.last_modified as lastModified,
                        dp.matang, dp.mengkal, dp.mentah, dp.lewat_matang as lewatMatang,
                        dp.abnormal, dp.serangan_hama as seranganHama, dp.tangkai_panjang as tangkaiPanjang, 
                        dp.janjang_kosong as janjangKosong, dp.original_id, u.original_filename,
                        dp.koreksi_reason as koreksiReason, dp.koreksi_at as koreksiAt,
                        uc.full_name as koreksiByName
                     FROM data_panen dp 
                     LEFT JOIN uploads u ON dp.upload_id = u.id 
                     LEFT JOIN users uc ON dp.koreksi_by = uc.id
                     ORDER BY dp.tanggal_pemeriksaan DESC, dp.jam DESC
                     LIMIT 1000";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $panen = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transform to match frontend format
            // Data sudah dalam format yang benar dari query, minimal transformasi
            $transformedPanen = array_map(function($item) {
                // Konversi tipe data
                $item['jmlhJanjang'] = (int)($item['jmlhJanjang'] ?? 0);
                $item['koreksiPanen'] = (int)($item['koreksiPanen'] ?? 0);
                $item['avgBjr'] = (float)($item['avgBjr'] ?? 15);
                $item['totalKg'] = (float)($item['totalKg'] ?? 0);
                $item['kgBerondolan'] = (float)($item['kgBerondolan'] ?? 0);
                $item['matang'] = (int)($item['matang'] ?? 0);
                $item['mengkal'] = (int)($item['mengkal'] ?? 0);
                $item['mentah'] = (int)($item['mentah'] ?? 0);
                $item['lewatMatang'] = (int)($item['lewatMatang'] ?? 0);
                $item['abnormal'] = (int)($item['abnormal'] ?? 0);
                $item['seranganHama'] = (int)($item['seranganHama'] ?? 0);
                $item['tangkaiPanjang'] = (int)($item['tangkaiPanjang'] ?? 0);
                $item['janjangKosong'] = (int)($item['janjangKosong'] ?? 0);
                $item['tipeAplikasi'] = 'database';
                return $item;
            }, $panen);
            
            echo json_encode(['success' => true, 'data' => $transformedPanen]);
            break;
            
        case 'pengiriman':
            // Get pengiriman data from database - optimized query with koreksi
            $query = "SELECT 
                        dg.id, dg.tanggal as date, dg.afdeling, dg.blok, dg.no_tph as noTPH,
                        dg.nama_kerani as namaKerani, dg.nik_kerani as nikKerani, 
                        dg.nomor_kendaraan as noKend, dg.nopol, 
                        COALESCE(dg.jumlah_janjang, 0) as jmlhJanjang,
                        COALESCE(dg.koreksi_kirim, 0) as koreksiKirim,
                        COALESCE(dg.kg_brd, 0) as kgBrd, 
                        COALESCE(dg.kg_total, 0) as totalKg, 
                        COALESCE(dg.bjr, 0) as bjr, 
                        dg.waktu, dg.koordinat, dg.tipe_aplikasi, dg.original_id, u.original_filename,
                        dg.koreksi_reason as koreksiReason, dg.koreksi_at as koreksiAt,
                        uc.full_name as koreksiByName
                     FROM data_pengiriman dg 
                     LEFT JOIN uploads u ON dg.upload_id = u.id 
                     LEFT JOIN users uc ON dg.koreksi_by = uc.id
                     ORDER BY dg.tanggal DESC, dg.waktu DESC
                     LIMIT 1000";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $pengiriman = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transform to match frontend format
            $transformedPengiriman = array_map(function($item) {
                return [
                    'id' => $item['id'],
                    'date' => $item['tanggal'],
                    'afdeling' => $item['afdeling'],
                    'blok' => $item['blok'],
                    'noTPH' => $item['no_tph'],
                    'namaKerani' => $item['nama_kerani'],
                    'nikKerani' => $item['nik_kerani'] ?? '',
                    'noKend' => $item['nomor_kendaraan'],
                    'nopol' => $item['nopol'],
                    'jmlhJanjang' => (int)$item['jumlah_janjang'],
                    'koreksiKirim' => (int)($item['koreksiKirim'] ?? 0),
                    'kgBrd' => (float)($item['kg_brd'] ?? 0),  // Fix: use kg_brd instead of kg_berondolan
                    'totalKg' => (float)($item['kg_total'] ?? 0),
                    'waktu' => $item['waktu'],
                    'koordinat' => $item['koordinat'],
                    'tipeAplikasi' => $item['tipe_aplikasi'] ?? 'database',
                    'original_filename' => $item['original_filename'],
                    'original_id' => $item['original_id'],
                    // Add missing fields
                    'bjr' => (float)($item['bjr'] ?? 0),
                    'koreksiReason' => $item['koreksiReason'] ?? null,
                    'koreksiAt' => $item['koreksiAt'] ?? null,
                    'koreksiByName' => $item['koreksiByName'] ?? null
                ];
            }, $pengiriman);
            
            echo json_encode(['success' => true, 'data' => $transformedPengiriman]);
            break;
            
        case 'all':
        default:
            // Get both panen and pengiriman data - optimized queries with koreksi
            $panenQuery = "SELECT 
                            dp.id, dp.tanggal_pemeriksaan as date, dp.afdeling, dp.blok, 
                            dp.no_tph as noTPH, dp.no_ancak as noAncak, dp.nama_pemanen as namaPemanen, 
                            dp.nik_pemanen as nikPemanen, dp.nama_kerani as namaKerani, 
                            COALESCE(dp.jumlah_janjang, 0) as jmlhJanjang, 
                            COALESCE(dp.koreksi_panen, 0) as koreksiPanen,
                            COALESCE(dp.bjr, 0) as avgBjr, 
                            COALESCE(dp.kg_total, 0) as totalKg, 
                            COALESCE(dp.kg_brd, 0) as kgBerondolan, 
                            dp.jam, dp.koordinat, dp.last_modified as lastModified,
                            dp.matang, dp.mengkal, dp.mentah, dp.lewat_matang as lewatMatang,
                            dp.abnormal, dp.serangan_hama as seranganHama, dp.tangkai_panjang as tangkaiPanjang, 
                            dp.janjang_kosong as janjangKosong, dp.original_id, u.original_filename,
                            dp.koreksi_reason as koreksiReason, dp.koreksi_at as koreksiAt,
                            uc.full_name as koreksiByName
                          FROM data_panen dp 
                          LEFT JOIN uploads u ON dp.upload_id = u.id 
                          LEFT JOIN users uc ON dp.koreksi_by = uc.id
                          ORDER BY dp.tanggal_pemeriksaan DESC, dp.jam DESC";
            $stmt = $conn->prepare($panenQuery);
            $stmt->execute();
            $panenData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $pengirimanQuery = "SELECT 
                                dg.id, dg.tanggal as date, dg.afdeling, dg.blok, dg.no_tph as noTPH,
                                dg.nama_kerani as namaKerani, dg.nik_kerani as nikKerani, 
                                dg.nomor_kendaraan as noKend, dg.nopol, 
                                COALESCE(dg.jumlah_janjang, 0) as jmlhJanjang,
                                COALESCE(dg.koreksi_kirim, 0) as koreksiKirim,
                                COALESCE(dg.kg_brd, 0) as kgBrd, 
                                COALESCE(dg.kg_total, 0) as totalKg, 
                                COALESCE(dg.bjr, 0) as bjr, 
                                dg.waktu, dg.koordinat, dg.tipe_aplikasi, dg.original_id, u.original_filename,
                                dg.koreksi_reason as koreksiReason, dg.koreksi_at as koreksiAt,
                                uc.full_name as koreksiByName
                               FROM data_pengiriman dg 
                               LEFT JOIN uploads u ON dg.upload_id = u.id 
                               LEFT JOIN users uc ON dg.koreksi_by = uc.id
                               ORDER BY dg.tanggal DESC, dg.waktu DESC";
            $stmt = $conn->prepare($pengirimanQuery);
            $stmt->execute();
            $pengirimanData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transform panen data - minimal processing
            $transformedPanen = array_map(function($item) {
                return [
                    'id' => $item['id'],
                    'date' => $item['date'],
                    'afdeling' => $item['afdeling'],
                    'blok' => $item['blok'],
                    'noTPH' => $item['noTPH'],
                    'namaPemanen' => $item['namaPemanen'],
                    'nikPemanen' => $item['nikPemanen'],
                    'namaKerani' => $item['namaKerani'],
                    'jmlhJanjang' => (int)$item['jmlhJanjang'],
                    'koreksiPanen' => (int)($item['koreksiPanen'] ?? 0),
                    'avgBjr' => (float)($item['avgBjr'] ?? 15),
                    'totalKg' => (float)($item['totalKg'] ?? 0),
                    'jam' => $item['jam'],
                    'koordinat' => $item['koordinat'],
                    'tipeAplikasi' => 'database',
                    'original_filename' => $item['original_filename'],
                    'matang' => (int)($item['matang'] ?? 0),
                    'mengkal' => (int)($item['mengkal'] ?? 0),
                    'mentah' => (int)($item['mentah'] ?? 0),
                    'lewatMatang' => (int)($item['lewatMatang'] ?? 0),
                    'abnormal' => (int)($item['abnormal'] ?? 0),
                    'seranganHama' => (int)($item['seranganHama'] ?? 0),
                    'tangkaiPanjang' => (int)($item['tangkaiPanjang'] ?? 0),
                    'janjangKosong' => (int)($item['janjangKosong'] ?? 0),
                    'kgBerondolan' => (float)($item['kgBerondolan'] ?? 0),
                    'noAncak' => $item['noAncak'] ?? '',
                    'koreksiReason' => $item['koreksiReason'] ?? null,
                    'koreksiAt' => $item['koreksiAt'] ?? null,
                    'koreksiByName' => $item['koreksiByName'] ?? null
                ];
            }, $panenData);
            
            // Transform pengiriman data - minimal processing
            $transformedPengiriman = array_map(function($item) {
                return [
                    'id' => $item['id'],
                    'date' => $item['date'],
                    'afdeling' => $item['afdeling'],
                    'blok' => $item['blok'],
                    'noTPH' => $item['noTPH'],
                    'namaKerani' => $item['namaKerani'],
                    'nikKerani' => $item['nikKerani'] ?? '',
                    'noKend' => $item['noKend'],
                    'nopol' => $item['nopol'],
                    'jmlhJanjang' => (int)$item['jmlhJanjang'],
                    'koreksiKirim' => (int)($item['koreksiKirim'] ?? 0),
                    'kgBrd' => (float)($item['kgBrd'] ?? 0),
                    'totalKg' => (float)($item['totalKg'] ?? 0),
                    'waktu' => $item['waktu'],
                    'koordinat' => $item['koordinat'],
                    'tipeAplikasi' => $item['tipe_aplikasi'] ?? 'database',
                    'original_filename' => $item['original_filename'],
                    'bjr' => (float)($item['bjr'] ?? 0),
                    'koreksiReason' => $item['koreksiReason'] ?? null,
                    'koreksiAt' => $item['koreksiAt'] ?? null,
                    'koreksiByName' => $item['koreksiByName'] ?? null
                ];
            }, $pengirimanData);
            
            echo json_encode([
                'success' => true, 
                'data' => [
                    'panen' => $transformedPanen,
                    'pengiriman' => $transformedPengiriman,
                    'total_panen' => count($transformedPanen),
                    'total_pengiriman' => count($transformedPengiriman)
                ]
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>