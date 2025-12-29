<?php

require_once 'BaseAPI.php';

class PengirimanAPI extends BaseAPI {
    
    /**
     * Handle pengiriman data requests
     */
    public function handleRequest() {
        // Verify authentication
        $user = $this->verifyToken();
        
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['PATH_INFO'] ?? '';
        
        switch ($path) {
            case '':
            case '/':
                if ($method === 'GET') {
                    $this->getPengirimanData();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            case '/statistics':
                if ($method === 'GET') {
                    $this->getPengirimanStatistics();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            case '/summary':
                if ($method === 'GET') {
                    $this->getPengirimanSummary();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            default:
                // Check if it's a specific ID
                if (preg_match('/^\/(\d+)$/', $path, $matches)) {
                    $id = $matches[1];
                    $this->getPengirimanById($id);
                } else {
                    $this->sendError("Endpoint not found", 404);
                }
        }
    }
    
    /**
     * Get pengiriman data with filtering and pagination
     */
    private function getPengirimanData() {
        try {
            $pagination = $this->getPaginationParams();
            $filters = $this->getFilters();
            $sort = $this->getSortParams();
            
            // Build query
            $where_conditions = [];
            $params = [];
            
            // Date range filter
            if (!empty($filters['date_from'])) {
                $where_conditions[] = "dp.tanggal >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where_conditions[] = "dp.tanggal <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            // Afdeling filter
            if (!empty($filters['afdeling'])) {
                $where_conditions[] = "dp.afdeling LIKE :afdeling";
                $params[':afdeling'] = '%' . $this->sanitizeLike($filters['afdeling']) . '%';
            }
            
            // Blok filter
            if (!empty($filters['blok'])) {
                $where_conditions[] = "dp.blok LIKE :blok";
                $params[':blok'] = '%' . $this->sanitizeLike($filters['blok']) . '%';
            }
            
            // Nopol filter
            if (!empty($filters['nopol'])) {
                $where_conditions[] = "(dp.nopol LIKE :nopol OR dp.nomor_kendaraan LIKE :nopol2)";
                $params[':nopol'] = '%' . $this->sanitizeLike($filters['nopol']) . '%';
                $params[':nopol2'] = '%' . $this->sanitizeLike($filters['nopol']) . '%';
            }
            
            // Kerani filter
            if (!empty($filters['kerani'])) {
                $where_conditions[] = "(dp.nama_kerani LIKE :kerani OR dp.nik_kerani LIKE :kerani_nik)";
                $params[':kerani'] = '%' . $this->sanitizeLike($filters['kerani']) . '%';
                $params[':kerani_nik'] = '%' . $this->sanitizeLike($filters['kerani']) . '%';
            }
            
            // Minimum janjang filter
            if (!empty($filters['min_janjang'])) {
                $where_conditions[] = "dp.jumlah_janjang >= :min_janjang";
                $params[':min_janjang'] = (int)$filters['min_janjang'];
            }
            
            // Maximum janjang filter
            if (!empty($filters['max_janjang'])) {
                $where_conditions[] = "dp.jumlah_janjang <= :max_janjang";
                $params[':max_janjang'] = (int)$filters['max_janjang'];
            }
            
            // Weight range filters (using kg_total)
            if (!empty($filters['min_kg'])) {
                $where_conditions[] = "dp.kg_total >= :min_kg";
                $params[':min_kg'] = (float)$filters['min_kg'];
            }
            
            if (!empty($filters['max_kg'])) {
                $where_conditions[] = "dp.kg_total <= :max_kg";
                $params[':max_kg'] = (float)$filters['max_kg'];
            }
            
            // Application type filter
            if (!empty($filters['tipe_aplikasi'])) {
                $where_conditions[] = "dp.tipe_aplikasi LIKE :tipe_aplikasi";
                $params[':tipe_aplikasi'] = '%' . $this->sanitizeLike($filters['tipe_aplikasi']) . '%';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Count total records
            $count_query = "SELECT COUNT(*) as total 
                           FROM data_pengiriman dp 
                           JOIN uploads u ON dp.upload_id = u.id 
                           $where_clause";
            
            $count_stmt = $this->conn->prepare($count_query);
            foreach ($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Main query - UPDATED TO INCLUDE kg_brd
            $query = "SELECT dp.*, 
                            u.filename as upload_filename,
                            u.original_filename,
                            u.upload_date,
                            users.full_name as uploaded_by_name
                     FROM data_pengiriman dp 
                     JOIN uploads u ON dp.upload_id = u.id 
                     LEFT JOIN users ON u.uploaded_by = users.id
                     $where_clause
                     ORDER BY {$sort['field']} {$sort['direction']}
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind filter parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind pagination parameters
            $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format data
            $formatted_data = array_map([$this, 'formatPengirimanRecord'], $data);
            
            $response = $this->createPaginationResponse(
                $formatted_data, 
                $total, 
                $pagination['page'], 
                $pagination['limit']
            );
            
            $this->sendResponse($response, "Data pengiriman retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve pengiriman data: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get pengiriman data by ID
     */
    private function getPengirimanById($id) {
        try {
            $query = "SELECT dp.*, 
                            u.filename as upload_filename,
                            u.original_filename,
                            u.upload_date,
                            users.full_name as uploaded_by_name
                     FROM data_pengiriman dp 
                     JOIN uploads u ON dp.upload_id = u.id 
                     LEFT JOIN users ON u.uploaded_by = users.id
                     WHERE dp.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                $this->sendError("Pengiriman record not found", 404);
            }
            
            $formatted_data = $this->formatPengirimanRecord($data);
            
            $this->sendResponse($formatted_data, "Pengiriman record retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve pengiriman record: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get pengiriman statistics
     */
    private function getPengirimanStatistics() {
        try {
            $filters = $this->getFilters();
            $where_conditions = [];
            $params = [];
            
            // Apply same filters as main query
            if (!empty($filters['date_from'])) {
                $where_conditions[] = "dp.tanggal >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where_conditions[] = "dp.tanggal <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            if (!empty($filters['afdeling'])) {
                $where_conditions[] = "dp.afdeling LIKE :afdeling";
                $params[':afdeling'] = '%' . $this->sanitizeLike($filters['afdeling']) . '%';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Get statistics
            $query = "SELECT 
                        COUNT(*) as total_records,
                        SUM(dp.jumlah_janjang) as total_janjang,
                        AVG(dp.jumlah_janjang) as avg_janjang,
                        MIN(dp.jumlah_janjang) as min_janjang,
                        MAX(dp.jumlah_janjang) as max_janjang,
                        SUM(dp.kg_total) as total_kg,
                        AVG(dp.kg_total) as avg_kg,
                        MIN(COALESCE(dp.kg_total, dp.kg)) as min_kg,
                        MAX(COALESCE(dp.kg_total, dp.kg)) as max_kg,
                        COUNT(DISTINCT dp.afdeling) as total_afdeling,
                        COUNT(DISTINCT dp.blok) as total_blok,
                        COUNT(DISTINCT dp.nopol) as total_kendaraan,
                        COUNT(DISTINCT dp.nama_kerani) as total_kerani,
                        MIN(dp.tanggal) as earliest_date,
                        MAX(dp.tanggal) as latest_date
                     FROM data_pengiriman dp $where_clause";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get top afdelings
            $afdeling_query = "SELECT dp.afdeling, 
                                     COUNT(*) as record_count,
                                     SUM(dp.jumlah_janjang) as total_janjang,
                                     SUM(COALESCE(dp.kg_total, dp.kg)) as total_kg
                              FROM data_pengiriman dp $where_clause
                              GROUP BY dp.afdeling
                              ORDER BY total_kg DESC
                              LIMIT 10";
            
            $afdeling_stmt = $this->conn->prepare($afdeling_query);
            foreach ($params as $key => $value) {
                $afdeling_stmt->bindValue($key, $value);
            }
            $afdeling_stmt->execute();
            $top_afdelings = $afdeling_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get top vehicles
            $vehicle_query = "SELECT CONCAT(dp.nopol, ' - ', dp.nomor_kendaraan) as vehicle,
                                    COUNT(*) as record_count,
                                    SUM(dp.jumlah_janjang) as total_janjang,
                                    SUM(COALESCE(dp.kg_total, dp.kg)) as total_kg
                             FROM data_pengiriman dp $where_clause
                             GROUP BY dp.nopol, dp.nomor_kendaraan
                             ORDER BY total_kg DESC
                             LIMIT 10";
            
            $vehicle_stmt = $this->conn->prepare($vehicle_query);
            foreach ($params as $key => $value) {
                $vehicle_stmt->bindValue($key, $value);
            }
            $vehicle_stmt->execute();
            $top_vehicles = $vehicle_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get top kerani
            $kerani_query = "SELECT dp.nama_kerani, dp.nik_kerani,
                                   COUNT(*) as record_count,
                                   SUM(dp.jumlah_janjang) as total_janjang,
                                   SUM(COALESCE(dp.kg_total, dp.kg)) as total_kg
                            FROM data_pengiriman dp $where_clause
                            GROUP BY dp.nama_kerani, dp.nik_kerani
                            ORDER BY total_kg DESC
                            LIMIT 10";
            
            $kerani_stmt = $this->conn->prepare($kerani_query);
            foreach ($params as $key => $value) {
                $kerani_stmt->bindValue($key, $value);
            }
            $kerani_stmt->execute();
            $top_kerani = $kerani_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'overview' => [
                    'total_records' => (int)$stats['total_records'],
                    'total_janjang' => (int)$stats['total_janjang'],
                    'avg_janjang' => round((float)$stats['avg_janjang'], 2),
                    'min_janjang' => (int)$stats['min_janjang'],
                    'max_janjang' => (int)$stats['max_janjang'],
                    'total_kg' => round((float)$stats['total_kg'], 2),
                    'avg_kg' => round((float)$stats['avg_kg'], 2),
                    'min_kg' => round((float)$stats['min_kg'], 2),
                    'max_kg' => round((float)$stats['max_kg'], 2),
                    'total_afdeling' => (int)$stats['total_afdeling'],
                    'total_blok' => (int)$stats['total_blok'],
                    'total_kendaraan' => (int)$stats['total_kendaraan'],
                    'total_kerani' => (int)$stats['total_kerani'],
                    'earliest_date' => $stats['earliest_date'],
                    'latest_date' => $stats['latest_date']
                ],
                'top_afdelings' => $top_afdelings,
                'top_vehicles' => $top_vehicles,
                'top_kerani' => $top_kerani
            ];
            
            $this->sendResponse($response, "Pengiriman statistics retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve pengiriman statistics: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get pengiriman summary by date/afdeling/vehicle
     */
    private function getPengirimanSummary() {
        try {
            $group_by = $_GET['group_by'] ?? 'date'; // date, afdeling, vehicle, kerani
            $filters = $this->getFilters();
            $where_conditions = [];
            $params = [];
            
            // Apply filters
            if (!empty($filters['date_from'])) {
                $where_conditions[] = "dp.tanggal >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where_conditions[] = "dp.tanggal <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            if (!empty($filters['afdeling'])) {
                $where_conditions[] = "dp.afdeling LIKE :afdeling";
                $params[':afdeling'] = '%' . $this->sanitizeLike($filters['afdeling']) . '%';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            switch ($group_by) {
                case 'date':
                    $select_field = 'dp.tanggal as group_field';
                    $group_field = 'dp.tanggal';
                    break;
                case 'afdeling':
                    $select_field = 'dp.afdeling as group_field';
                    $group_field = 'dp.afdeling';
                    break;
                case 'vehicle':
                    $select_field = 'CONCAT(dp.nopol, " - ", dp.nomor_kendaraan) as group_field';
                    $group_field = 'dp.nopol, dp.nomor_kendaraan';
                    break;
                case 'kerani':
                    $select_field = 'dp.nama_kerani as group_field';
                    $group_field = 'dp.nama_kerani';
                    break;
                default:
                    $this->sendError("Invalid group_by parameter", 400);
            }
            
            $query = "SELECT $select_field,
                            COUNT(*) as record_count,
                            SUM(dp.jumlah_janjang) as total_janjang,
                            AVG(dp.jumlah_janjang) as avg_janjang,
                            MIN(dp.jumlah_janjang) as min_janjang,
                            MAX(dp.jumlah_janjang) as max_janjang,
                            SUM(COALESCE(dp.kg_total, dp.kg)) as total_kg,
                            AVG(COALESCE(dp.kg_total, dp.kg)) as avg_kg,
                            MIN(COALESCE(dp.kg_total, dp.kg)) as min_kg,
                            MAX(COALESCE(dp.kg_total, dp.kg)) as max_kg
                     FROM data_pengiriman dp $where_clause
                     GROUP BY $group_field
                     ORDER BY total_kg DESC
                     LIMIT 50";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the summary
            $formatted_summary = array_map(function($row) {
                return [
                    'group_field' => $row['group_field'],
                    'record_count' => (int)$row['record_count'],
                    'total_janjang' => (int)$row['total_janjang'],
                    'avg_janjang' => round((float)$row['avg_janjang'], 2),
                    'min_janjang' => (int)$row['min_janjang'],
                    'max_janjang' => (int)$row['max_janjang'],
                    'total_kg' => round((float)$row['total_kg'], 2),
                    'avg_kg' => round((float)$row['avg_kg'], 2),
                    'min_kg' => round((float)$row['min_kg'], 2),
                    'max_kg' => round((float)$row['max_kg'], 2)
                ];
            }, $summary);
            
            $response = [
                'group_by' => $group_by,
                'summary' => $formatted_summary
            ];
            
            $this->sendResponse($response, "Pengiriman summary retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve pengiriman summary: " . $e->getMessage(), 500);
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
        
        if (isset($_GET['nopol']) && !empty(trim($_GET['nopol']))) {
            $filters['nopol'] = trim($_GET['nopol']);
        }
        
        if (isset($_GET['kerani']) && !empty(trim($_GET['kerani']))) {
            $filters['kerani'] = trim($_GET['kerani']);
        }
        
        if (isset($_GET['min_janjang']) && is_numeric($_GET['min_janjang'])) {
            $filters['min_janjang'] = $_GET['min_janjang'];
        }
        
        if (isset($_GET['max_janjang']) && is_numeric($_GET['max_janjang'])) {
            $filters['max_janjang'] = $_GET['max_janjang'];
        }
        
        if (isset($_GET['min_kg']) && is_numeric($_GET['min_kg'])) {
            $filters['min_kg'] = $_GET['min_kg'];
        }
        
        if (isset($_GET['max_kg']) && is_numeric($_GET['max_kg'])) {
            $filters['max_kg'] = $_GET['max_kg'];
        }
        
        if (isset($_GET['tipe_aplikasi']) && !empty(trim($_GET['tipe_aplikasi']))) {
            $filters['tipe_aplikasi'] = trim($_GET['tipe_aplikasi']);
        }
        
        return $filters;
    }
    
    /**
     * Get sort parameters
     */
    private function getSortParams() {
        $allowed_fields = [
            'tanggal', 'afdeling', 'nama_kerani', 'blok', 'nopol', 
            'nomor_kendaraan', 'no_tph', 'jumlah_janjang', 'kg', 
            'waktu', 'created_at', 'tipe_aplikasi'
        ];
        
        $field = $_GET['sort_by'] ?? 'tanggal';
        $direction = strtoupper($_GET['sort_direction'] ?? 'DESC');
        
        if (!in_array($field, $allowed_fields)) {
            $field = 'tanggal';
        }
        
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }
        
        return [
            'field' => 'dp.' . $field,
            'direction' => $direction
        ];
    }
    
    /**
     * Format pengiriman record - UPDATED TO INCLUDE kg_total and kg_berondolan
     */
    private function formatPengirimanRecord($record) {
        return [
            'id' => (int)$record['id'],
            'upload_id' => (int)$record['upload_id'],
            'tipe_aplikasi' => $record['tipe_aplikasi'],
            'nama_kerani' => $record['nama_kerani'],
            'nik_kerani' => $record['nik_kerani'],
            'tanggal' => $record['tanggal'],
            'afdeling' => $record['afdeling'],
            'nopol' => $record['nopol'],
            'nomor_kendaraan' => $record['nomor_kendaraan'],
            'blok' => $record['blok'],
            'no_tph' => $record['no_tph'],
            'jumlah_janjang' => (int)$record['jumlah_janjang'],
            'waktu' => $record['waktu'],
            'koordinat' => $record['koordinat'] ? json_decode($record['koordinat'], true) : null,
            'bjr' => $record['bjr'] !== null ? round((float)$record['bjr'], 2) : null,
            'kg_total' => $record['kg_total'] !== null ? round((float)$record['kg_total'], 2) : null,
            'kg_berondolan' => $record['kg_brd'] !== null ? round((float)$record['kg_brd'], 2) : null,
            'kg_brd' => $record['kg_brd'] !== null ? round((float)$record['kg_brd'], 2) : null, // alias for compatibility
            'koreksi_kirim' => $record['koreksi_kirim'] ? (int) $record['koreksi_kirim'] : 0,
            'created_at' => $record['created_at'],
            'upload_info' => [
                'filename' => $record['upload_filename'],
                'original_filename' => $record['original_filename'],
                'upload_date' => $record['upload_date'],
                'uploaded_by' => $record['uploaded_by_name']
            ]
        ];
    }
}

// Initialize and handle request only if file accessed directly
if (basename($_SERVER['SCRIPT_NAME'] ?? __FILE__) === 'pengiriman.php') {
    $api = new PengirimanAPI();
    $api->handleRequest();
}
?>