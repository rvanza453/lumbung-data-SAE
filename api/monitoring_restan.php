<?php
require_once 'BaseAPI.php';

class MonitoringRestanAPI extends BaseAPI {
    
    /**
     * Handle monitoring restan requests
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Parse PATH_INFO dengan fallback yang robust
        $path = $_SERVER['PATH_INFO'] ?? '';
        
        if (empty($path)) {
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $requestUri = $_SERVER['REQUEST_URI'];
            
            if ($pos = strpos($requestUri, '?')) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            
            if (strpos($requestUri, $scriptName) === 0) {
                $path = substr($requestUri, strlen($scriptName));
            }
        }
        
        if (!empty($path) && strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }
        
        // Default ke recap jika tidak ada path
        if (empty($path) || $path === '/') {
            $path = '/recap';
        }
        
        switch ($path) {
            case '/recap':
                if ($method === 'GET') {
                    $this->getRecapData();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            case '/statistics':
                if ($method === 'GET') {
                    $this->getStatistics();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            case '/summary':
                if ($method === 'GET') {
                    $this->getSummary();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            default:
                $this->sendError("Endpoint not found: $path", 404);
        }
    }
    
    /**
     * Get recap data with matching logic like monitoring.html
     */
    private function getRecapData() {
        try {
            // Get filters
            $filters = $this->getFilters();
            
            // Get raw data with filters
            $panenData = $this->getRawPanenData($filters);
            $transportData = $this->getRawTransportData($filters);
            
            // Apply normalization and aggregation logic
            $aggregatedPanen = $this->aggregateData($panenData, 'panen');
            $aggregatedTransport = $this->aggregateData($transportData, 'transport');
            
            // Apply matching logic from monitoring.html
            $recapData = $this->performMatching($aggregatedPanen, $aggregatedTransport);
            
            // Apply final filters if needed
            $filteredData = $this->applyFinalFilters($recapData, $filters);
            
            // Sort data by location (afdeling, blok, tph)
            $sortedData = $this->sortRecapData($filteredData);
            
            $this->sendResponse([
                'recap_data' => $sortedData,
                'summary' => $this->generateSummary($sortedData),
                'total_records' => count($sortedData)
            ], "Recap data retrieved successfully");
            
        } catch (Exception $e) {
            error_log("Error in getRecapData: " . $e->getMessage());
            $this->sendError("Failed to retrieve recap data: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get raw panen data with filters
     */
    private function getRawPanenData($filters) {
        $where_conditions = ['1=1'];
        $params = [];
        
        // Date filter
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "dp.tanggal_pemeriksaan >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "dp.tanggal_pemeriksaan <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        // Afdeling filter
        if (!empty($filters['afdeling'])) {
            $where_conditions[] = "dp.afdeling = :afdeling";
            $params[':afdeling'] = $filters['afdeling'];
        }
        
        // Blok filter
        if (!empty($filters['blok'])) {
            $where_conditions[] = "dp.blok = :blok";
            $params[':blok'] = $filters['blok'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT 
                    dp.id,
                    dp.tanggal_pemeriksaan as date,
                    dp.afdeling,
                    dp.blok,
                    dp.no_tph as noTPH,
                    dp.no_ancak as noAncak,
                    dp.nama_pemanen as namaPemanen,
                    dp.nik_pemanen as nikPemanen,
                    dp.nama_kerani as namaKerani,
                    COALESCE(dp.jumlah_janjang, 0) + COALESCE(dp.koreksi_panen, 0) as jmlhJanjang,
                    COALESCE(dp.koreksi_panen, 0) as koreksiPanen,
                    COALESCE(dp.bjr, 15) as avgBjr,
                    COALESCE(dp.kg_total, 0) as totalKg,
                    COALESCE(dp.kg_brd, 0) as kgBerondolan,
                    dp.jam,
                    dp.koordinat,
                    dp.last_modified as lastModified,
                    dp.matang,
                    dp.mengkal,
                    dp.mentah,
                    dp.lewat_matang as lewatMatang,
                    dp.abnormal,
                    dp.serangan_hama as seranganHama,
                    dp.tangkai_panjang as tangkaiPanjang,
                    dp.janjang_kosong as janjangKosong,
                    'database' as tipeAplikasi
                  FROM data_panen dp
                  WHERE $where_clause
                  ORDER BY dp.tanggal_pemeriksaan ASC, dp.jam ASC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get raw transport data with filters
     */
    private function getRawTransportData($filters) {
        $where_conditions = ['1=1'];
        $params = [];
        
        // Date filter
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "dg.tanggal >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "dg.tanggal <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        // Afdeling filter
        if (!empty($filters['afdeling'])) {
            $where_conditions[] = "dg.afdeling = :afdeling";
            $params[':afdeling'] = $filters['afdeling'];
        }
        
        // Blok filter
        if (!empty($filters['blok'])) {
            $where_conditions[] = "dg.blok = :blok";
            $params[':blok'] = $filters['blok'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT 
                    dg.id,
                    dg.tanggal as date,
                    dg.afdeling,
                    dg.blok,
                    dg.no_tph as noTPH,
                    dg.nama_kerani as namaKerani,
                    dg.nik_kerani as nikKerani,
                    dg.nomor_kendaraan as noKend,
                    dg.nopol,
                    COALESCE(dg.jumlah_janjang, 0) + COALESCE(dg.koreksi_kirim, 0) as jmlhJanjang,
                    COALESCE(dg.koreksi_kirim, 0) as koreksiKirim,
                    COALESCE(dg.kg_brd, 0) as kgBrd,
                    COALESCE(dg.kg_total, 0) as totalKg,
                    COALESCE(dg.bjr, 15) as bjr,
                    dg.waktu,
                    dg.koordinat,
                    dg.tipe_aplikasi as tipeAplikasi
                  FROM data_pengiriman dg
                  WHERE $where_clause
                  ORDER BY dg.tanggal ASC, dg.waktu ASC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Aggregate data by location and date
     * Implements same logic as monitoring.html
     */
    private function aggregateData($rawData, $type) {
        $aggregated = [];
        
        foreach ($rawData as $row) {
            // Normalize data like in monitoring.html
            $afdeling = $this->normalizeAfdeling($row['afdeling']);
            $blok = trim(strtoupper($row['blok']));
            $noTPH = strval($row['noTPH']);
            $date = $row['date'];
            
            $key = $date . '_' . $afdeling . '_' . $blok . '_' . $noTPH;
            
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'date' => $date,
                    'afdeling' => $afdeling,
                    'blok' => $blok,
                    'noTPH' => $noTPH,
                    'jmlhJanjang' => 0,
                    'totalKg' => 0.0,
                    'kgBrd' => 0.0,
                    'bjr' => 0.0,
                    'bjr_count' => 0,
                    'bjr_total' => 0.0,
                    'originalRecords' => []
                ];
            }
            
            // Aggregate values
            $aggregated[$key]['jmlhJanjang'] += intval($row['jmlhJanjang']);
            $aggregated[$key]['totalKg'] += floatval($row['totalKg']);
            $aggregated[$key]['kgBrd'] += floatval($row['kgBrd'] ?? 0);
            
            // Calculate weighted average BJR
            $bjr = floatval($row[$type === 'panen' ? 'avgBjr' : 'bjr']);
            $weight = floatval($row['totalKg']);
            
            if ($bjr > 0 && $weight > 0) {
                $aggregated[$key]['bjr_total'] += ($bjr * $weight);
                $aggregated[$key]['bjr_count'] += $weight;
            }
            
            $aggregated[$key]['originalRecords'][] = $row;
        }
        
        // Calculate final BJR averages
        foreach ($aggregated as $key => &$item) {
            if ($item['bjr_count'] > 0) {
                $item['bjr'] = $item['bjr_total'] / $item['bjr_count'];
            } else {
                $item['bjr'] = 15.0; // Default BJR
            }
            
            // Clean up temporary calculation fields
            unset($item['bjr_count'], $item['bjr_total']);
        }
        
        return $aggregated;
    }
    
    /**
     * Perform FIFO matching logic like monitoring.html
     */
    private function performMatching($aggregatedPanen, $aggregatedTransport) {
        $recapData = [];
        
        // Group by location (afdeling_blok_tph)
        $panenByLocation = [];
        $transportByLocation = [];
        
        foreach ($aggregatedPanen as $key => $panen) {
            $locationKey = $panen['afdeling'] . '_' . $panen['blok'] . '_' . $panen['noTPH'];
            if (!isset($panenByLocation[$locationKey])) {
                $panenByLocation[$locationKey] = [];
            }
            $panenByLocation[$locationKey][] = $panen;
        }
        
        foreach ($aggregatedTransport as $key => $transport) {
            $locationKey = $transport['afdeling'] . '_' . $transport['blok'] . '_' . $transport['noTPH'];
            if (!isset($transportByLocation[$locationKey])) {
                $transportByLocation[$locationKey] = [];
            }
            $transportByLocation[$locationKey][] = $transport;
        }
        
        // Get all unique locations
        $allLocations = array_unique(array_merge(
            array_keys($panenByLocation),
            array_keys($transportByLocation)
        ));
        
        foreach ($allLocations as $location) {
            $panenList = $panenByLocation[$location] ?? [];
            $transportList = $transportByLocation[$location] ?? [];
            
            // Sort by date (FIFO - First In First Out)
            usort($panenList, function($a, $b) {
                return strcmp($a['date'], $b['date']);
            });
            
            usort($transportList, function($a, $b) {
                return strcmp($a['date'], $b['date']);
            });
            
            // FIFO Matching Algorithm (same as monitoring.html)
            $usedTransport = [];
            
            foreach ($panenList as $panen) {
                $matchedTransport = null;
                $matchIndex = -1;
                
                // Find first available transport that comes on or after panen date
                for ($i = 0; $i < count($transportList); $i++) {
                    if (in_array($i, $usedTransport)) continue;
                    
                    $transport = $transportList[$i];
                    if ($transport['date'] >= $panen['date']) {
                        $matchedTransport = $transport;
                        $matchIndex = $i;
                        break;
                    }
                }
                
                if ($matchedTransport) {
                    $usedTransport[] = $matchIndex;
                    $recapItem = $this->createRecapItem($panen, $matchedTransport);
                } else {
                    // No transport found - create item with zero transport
                    $recapItem = $this->createRecapItem($panen, null);
                }
                
                $recapData[] = $recapItem;
            }
            
            // Add unmatched transport (kelebihan tanpa panen)
            for ($i = 0; $i < count($transportList); $i++) {
                if (!in_array($i, $usedTransport)) {
                    $transport = $transportList[$i];
                    $recapItem = $this->createRecapItem(null, $transport);
                    $recapData[] = $recapItem;
                }
            }
        }
        
        return $recapData;
    }
    
    /**
     * Create recap item with status calculation
     */
    private function createRecapItem($panen, $transport) {
        // Initialize with default values
        $item = [
            'date' => $panen['date'] ?? $transport['date'] ?? date('Y-m-d'),
            'afdeling' => $panen['afdeling'] ?? $transport['afdeling'] ?? '',
            'blok' => $panen['blok'] ?? $transport['blok'] ?? '',
            'noTPH' => $panen['noTPH'] ?? $transport['noTPH'] ?? '',
            'jjgPanen' => $panen ? $panen['jmlhJanjang'] : 0,
            'jjgAngkut' => $transport ? $transport['jmlhJanjang'] : 0,
            'kgPanen' => $panen ? $panen['totalKg'] : 0.0,
            'kgAngkut' => $transport ? $transport['totalKg'] : 0.0,
            'kgBrd' => ($panen ? $panen['kgBrd'] : 0) + ($transport ? $transport['kgBrd'] : 0),
            'bjr' => $panen ? $panen['bjr'] : ($transport ? $transport['bjr'] : 15.0),
            'tanggalPanen' => $panen ? $panen['date'] : '',
            'tanggalAngkut' => $transport ? $transport['date'] : ''
        ];
        
        // Calculate selisih (difference)
        $item['selisihJjg'] = $item['jjgPanen'] - $item['jjgAngkut'];
        $item['selisihKg'] = $item['kgPanen'] - $item['kgAngkut'];
        
        // Calculate delay
        $currentDate = new DateTime();
        $item['delayHari'] = 0;
        
        if ($panen && $transport) {
            // Both exist - calculate delay between transport and panen
            $panenDate = new DateTime($panen['date']);
            $transportDate = new DateTime($transport['date']);
            $item['delayHari'] = $transportDate->diff($panenDate)->days;
        } elseif ($panen && !$transport) {
            // Only panen exists - calculate delay from panen to today
            $panenDate = new DateTime($panen['date']);
            $item['delayHari'] = $currentDate->diff($panenDate)->days;
        }
        
        // Calculate kg restan (only for positive selisih - panen > angkut)
        if ($item['selisihJjg'] > 0) {
            $item['kgRestan'] = $item['selisihJjg'] * $item['bjr'];
        } else {
            $item['kgRestan'] = 0.0;
        }
        
        // Determine status based on business logic
        $item['status'] = $this->determineStatus($item);
        $item['statusColor'] = $this->getStatusColor($item['status']);
        $item['delayColor'] = $this->getDelayColor($item['delayHari']);
        
        return $item;
    }
    
    /**
     * Determine status based on business rules (same as monitoring.html)
     */
    private function determineStatus($item) {
        $selisih = $item['selisihJjg'];
        $delay = $item['delayHari'];
        $hasTransport = $item['jjgAngkut'] > 0;
        $hasPanen = $item['jjgPanen'] > 0;
        
        if (!$hasPanen && $hasTransport) {
            return 'Kelebihan (Tanpa Data Panen)';
        }
        
        if (!$hasTransport && $hasPanen) {
            return $delay <= 1 ? 'Restan' : 'Restan (Delay)';
        }
        
        // Both panen and transport exist
        if ($selisih == 0) {
            return $delay <= 1 ? 'Sesuai' : 'Restan (Delay)';
        } elseif ($selisih > 0) {
            return $delay <= 1 ? 'Restan (Kurang)' : 'Restan (Kurang + Delay)';
        } else {
            return $delay <= 1 ? 'Kelebihan' : 'Kelebihan (Delay)';
        }
    }
    
    /**
     * Get status color
     */
    private function getStatusColor($status) {
        if (strpos($status, 'Sesuai') !== false) {
            return 'green';
        } elseif (strpos($status, 'Restan') !== false) {
            return 'red';
        } elseif (strpos($status, 'Kelebihan') !== false) {
            return 'blue';
        } elseif (strpos($status, 'Delay') !== false) {
            return 'orange';
        }
        return 'gray';
    }
    
    /**
     * Get delay color based on days
     */
    private function getDelayColor($days) {
        if ($days <= 1) return 'green';
        if ($days <= 3) return 'orange';
        return 'red';
    }
    
    /**
     * Apply final filters to recap data
     */
    private function applyFinalFilters($recapData, $filters) {
        $filtered = $recapData;
        
        // Status filter
        if (!empty($filters['status'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return strpos(strtolower($item['status']), strtolower($filters['status'])) !== false;
            });
        }
        
        // Minimum restan filter
        if (isset($filters['min_restan']) && $filters['min_restan'] > 0) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return $item['selisihJjg'] >= $filters['min_restan'];
            });
        }
        
        return array_values($filtered);
    }
    
    /**
     * Sort recap data by location
     */
    private function sortRecapData($recapData) {
        usort($recapData, function($a, $b) {
            // Sort by afdeling, then blok, then noTPH, then date
            $cmp = strcmp($a['afdeling'], $b['afdeling']);
            if ($cmp !== 0) return $cmp;
            
            $cmp = strcmp($a['blok'], $b['blok']);
            if ($cmp !== 0) return $cmp;
            
            $cmp = strcmp($a['noTPH'], $b['noTPH']);
            if ($cmp !== 0) return $cmp;
            
            return strcmp($a['date'], $b['date']);
        });
        
        return $recapData;
    }
    
    /**
     * Generate summary statistics
     */
    private function generateSummary($recapData) {
        $summary = [
            'total_records' => count($recapData),
            'total_panen_jjg' => 0,
            'total_angkut_jjg' => 0,
            'total_restan_jjg' => 0,
            'total_kelebihan_jjg' => 0,
            'total_panen_kg' => 0.0,
            'total_angkut_kg' => 0.0,
            'total_restan_kg' => 0.0,
            'total_sesuai' => 0,
            'total_restan' => 0,
            'total_kelebihan' => 0,
            'total_delay' => 0,
            'status_breakdown' => []
        ];
        
        $statusCounts = [];
        
        foreach ($recapData as $item) {
            $summary['total_panen_jjg'] += $item['jjgPanen'];
            $summary['total_angkut_jjg'] += $item['jjgAngkut'];
            $summary['total_panen_kg'] += $item['kgPanen'];
            $summary['total_angkut_kg'] += $item['kgAngkut'];
            
            if ($item['selisihJjg'] > 0) {
                $summary['total_restan_jjg'] += $item['selisihJjg'];
                $summary['total_restan_kg'] += $item['kgRestan'];
            } elseif ($item['selisihJjg'] < 0) {
                $summary['total_kelebihan_jjg'] += abs($item['selisihJjg']);
            }
            
            // Count by status
            $status = $item['status'];
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
            
            // Count by category
            if (strpos($status, 'Sesuai') !== false) {
                $summary['total_sesuai']++;
            } elseif (strpos($status, 'Restan') !== false) {
                $summary['total_restan']++;
            } elseif (strpos($status, 'Kelebihan') !== false) {
                $summary['total_kelebihan']++;
            }
            
            if (strpos($status, 'Delay') !== false) {
                $summary['total_delay']++;
            }
        }
        
        $summary['status_breakdown'] = $statusCounts;
        
        return $summary;
    }
    
    /**
     * Get statistics endpoint
     */
    private function getStatistics() {
        try {
            $filters = $this->getFilters();
            
            // Get basic data
            $panenData = $this->getRawPanenData($filters);
            $transportData = $this->getRawTransportData($filters);
            
            // Calculate statistics
            $stats = [
                'total_panen' => count($panenData),
                'total_transport' => count($transportData),
                'panen_stats' => $this->calculatePanenStats($panenData),
                'transport_stats' => $this->calculateTransportStats($transportData),
                'location_stats' => $this->calculateLocationStats($panenData, $transportData)
            ];
            
            $this->sendResponse($stats, "Statistics retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve statistics: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Calculate panen statistics
     */
    private function calculatePanenStats($panenData) {
        $totalJjg = array_sum(array_column($panenData, 'jmlhJanjang'));
        $totalKg = array_sum(array_column($panenData, 'totalKg'));
        $avgBjr = $totalJjg > 0 ? $totalKg / $totalJjg : 0;
        
        return [
            'total_jjg' => $totalJjg,
            'total_kg' => $totalKg,
            'avg_bjr' => round($avgBjr, 2),
            'unique_locations' => $this->countUniqueLocations($panenData)
        ];
    }
    
    /**
     * Calculate transport statistics  
     */
    private function calculateTransportStats($transportData) {
        $totalJjg = array_sum(array_column($transportData, 'jmlhJanjang'));
        $totalKg = array_sum(array_column($transportData, 'totalKg'));
        $avgBjr = $totalJjg > 0 ? $totalKg / $totalJjg : 0;
        
        return [
            'total_jjg' => $totalJjg,
            'total_kg' => $totalKg,
            'avg_bjr' => round($avgBjr, 2),
            'unique_locations' => $this->countUniqueLocations($transportData),
            'unique_vehicles' => count(array_unique(array_column($transportData, 'noKend')))
        ];
    }
    
    /**
     * Calculate location statistics
     */
    private function calculateLocationStats($panenData, $transportData) {
        $panenLocations = [];
        $transportLocations = [];
        
        foreach ($panenData as $item) {
            $key = $item['afdeling'] . '_' . $item['blok'] . '_' . $item['noTPH'];
            $panenLocations[$key] = true;
        }
        
        foreach ($transportData as $item) {
            $key = $item['afdeling'] . '_' . $item['blok'] . '_' . $item['noTPH'];
            $transportLocations[$key] = true;
        }
        
        $allLocations = array_unique(array_merge(
            array_keys($panenLocations),
            array_keys($transportLocations)
        ));
        
        return [
            'total_locations' => count($allLocations),
            'panen_only_locations' => count(array_diff(array_keys($panenLocations), array_keys($transportLocations))),
            'transport_only_locations' => count(array_diff(array_keys($transportLocations), array_keys($panenLocations))),
            'matched_locations' => count(array_intersect(array_keys($panenLocations), array_keys($transportLocations)))
        ];
    }
    
    /**
     * Count unique locations in data
     */
    private function countUniqueLocations($data) {
        $locations = [];
        foreach ($data as $item) {
            $key = $item['afdeling'] . '_' . $item['blok'] . '_' . $item['noTPH'];
            $locations[$key] = true;
        }
        return count($locations);
    }
    
    private function getSummary() {
        try {
            $filters = $this->getFilters();
            
            // Get recap data
            $panenData = $this->getRawPanenData($filters);
            $transportData = $this->getRawTransportData($filters);
            $aggregatedPanen = $this->aggregateData($panenData, 'panen');
            $aggregatedTransport = $this->aggregateData($transportData, 'transport');
            
            // Data ini sudah memiliki field 'status' yang akurat (termasuk logika Delay)
            $recapData = $this->performMatching($aggregatedPanen, $aggregatedTransport);
            
            // Group by afdeling for summary
            $summary = [];
            foreach ($recapData as $item) {
                $afdeling = $item['afdeling'];
                if (!isset($summary[$afdeling])) {
                    $summary[$afdeling] = [
                        'afdeling' => $afdeling,
                        'total_locations' => 0,
                        'total_panen_jjg' => 0,
                        'total_angkut_jjg' => 0,
                        'total_restan_jjg' => 0,
                        'total_kelebihan_jjg' => 0,
                        'total_restan_kg' => 0.0,
                        'sesuai_count' => 0,
                        'restan_count' => 0,
                        'kelebihan_count' => 0
                    ];
                }
                
                $summary[$afdeling]['total_locations']++;
                $summary[$afdeling]['total_panen_jjg'] += $item['jjgPanen'];
                $summary[$afdeling]['total_angkut_jjg'] += $item['jjgAngkut'];
                
                $status = $item['status'];

                // 1. Hitung Janjang & Kg
                if ($item['selisihJjg'] > 0) {
                    $summary[$afdeling]['total_restan_jjg'] += $item['selisihJjg'];
                    $summary[$afdeling]['total_restan_kg'] += $item['kgRestan'];
                } elseif ($item['selisihJjg'] < 0) {
                    $summary[$afdeling]['total_kelebihan_jjg'] += abs($item['selisihJjg']);
                }

                // 2. Hitung Count berdasarkan KATEGORI Status
                if (strpos($status, 'Sesuai') !== false) {
                    $summary[$afdeling]['sesuai_count']++;
                } 
                // Cek 'Restan' (mencakup 'Restan', 'Restan (Delay)', 'Restan (Kurang)')
                elseif (strpos($status, 'Restan') !== false) {
                    $summary[$afdeling]['restan_count']++;
                } 
                // Cek 'Kelebihan' (mencakup 'Kelebihan', 'Kelebihan (Delay)')
                elseif (strpos($status, 'Kelebihan') !== false) {
                    $summary[$afdeling]['kelebihan_count']++;
                }
            }
            
            $this->sendResponse([
                'summary_by_afdeling' => array_values($summary),
                'grand_total' => $this->generateSummary($recapData) // Fungsi ini sudah benar
            ], "Summary retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve summary: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get filters from query parameters
     */
    private function getFilters() {
        $filters = [];
        
        if (isset($_GET['date_from']) && $this->validateDate($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        
        if (isset($_GET['date_to']) && $this->validateDate($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        
        if (isset($_GET['afdeling']) && !empty(trim($_GET['afdeling']))) {
            $filters['afdeling'] = trim($_GET['afdeling']);
        }
        
        if (isset($_GET['blok']) && !empty(trim($_GET['blok']))) {
            $filters['blok'] = trim($_GET['blok']);
        }
        
        if (isset($_GET['status']) && !empty(trim($_GET['status']))) {
            $filters['status'] = trim($_GET['status']);
        }
        
        if (isset($_GET['min_restan']) && is_numeric($_GET['min_restan'])) {
            $filters['min_restan'] = intval($_GET['min_restan']);
        }
        
        return $filters;
    }
    
    /**
     * Normalize afdeling like monitoring.html (convert roman to numbers)
     */
    private function normalizeAfdeling($afdeling) {
        $normalized = strtoupper(trim($afdeling));
        
        // Convert Roman numerals to numbers
        $romanMap = [
            'VIII' => '8', 'VII' => '7', 'VI' => '6', 'V' => '5',
            'IV' => '4', 'III' => '3', 'II' => '2', 'I' => '1'
        ];
        
        foreach ($romanMap as $roman => $number) {
            if ($normalized === $roman) {
                return $number;
            }
        }
        
        return $normalized;
    }
}

// Initialize and handle request only if file accessed directly
if (basename($_SERVER['SCRIPT_NAME'] ?? __FILE__) === 'monitoring_restan.php') {
    $api = new MonitoringRestanAPI();
    $api->handleRequest();
}
?>