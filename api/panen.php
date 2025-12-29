<?php
require_once 'BaseAPI.php';

class PanenAPI extends BaseAPI {
    
    /**
     * Handle panen data requests
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
                    $this->getPanenData();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            case '/statistics':
                if ($method === 'GET') {
                    $this->getPanenStatistics();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            case '/summary':
                if ($method === 'GET') {
                    $this->getPanenSummary();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            default:
                // Check if it's a specific ID
                if (preg_match('/^\/(\d+)$/', $path, $matches)) {
                    $id = (int)$matches[1];
                    if ($method === 'GET') {
                        $this->getPanenById($id);
                    } else {
                        $this->sendError("Method not allowed", 405);
                    }
                } else {
                    $this->sendError("Endpoint not found", 404);
                }
        }
    }
    
    /**
     * Get panen data with filtering and pagination
     */
    private function getPanenData() {
        try {
            $pagination = $this->getPaginationParams();
            $filters = $this->getFilters();
            $sort = $this->getSortParams();
            
            // Build query
            $where_conditions = [];
            $params = [];
            
            // Date range filter
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
                $where_conditions[] = "dp.afdeling LIKE :afdeling";
                $params[':afdeling'] = '%' . $this->sanitizeLike($filters['afdeling']) . '%';
            }
            
            // Blok filter
            if (!empty($filters['blok'])) {
                $where_conditions[] = "dp.blok LIKE :blok";
                $params[':blok'] = '%' . $this->sanitizeLike($filters['blok']) . '%';
            }
            
            // Pemanen filter
            if (!empty($filters['pemanen'])) {
                $where_conditions[] = "(dp.nama_pemanen LIKE :pemanen OR dp.nik_pemanen LIKE :pemanen_nik)";
                $params[':pemanen'] = '%' . $this->sanitizeLike($filters['pemanen']) . '%';
                $params[':pemanen_nik'] = '%' . $this->sanitizeLike($filters['pemanen']) . '%';
            }
            
            // Kerani filter
            if (!empty($filters['kerani'])) {
                $where_conditions[] = "dp.nama_kerani LIKE :kerani";
                $params[':kerani'] = '%' . $this->sanitizeLike($filters['kerani']) . '%';
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
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Count total records
            $count_query = "SELECT COUNT(*) as total 
                           FROM data_panen dp 
                           JOIN uploads u ON dp.upload_id = u.id 
                           $where_clause";
            
            $count_stmt = $this->conn->prepare($count_query);
            foreach ($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Main query
            $query = "SELECT dp.*, 
                            u.filename as upload_filename,
                            u.original_filename,
                            u.upload_date,
                            users.full_name as uploaded_by_name
                     FROM data_panen dp 
                     JOIN uploads u ON dp.upload_id = u.id 
                     LEFT JOIN users ON u.uploaded_by = users.id
                     $where_clause
                     ORDER BY {$sort['field']} {$sort['direction']}, dp.id DESC
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
            $formatted_data = array_map([$this, 'formatPanenRecord'], $data);
            
            $response = $this->createPaginationResponse(
                $formatted_data, 
                $total, 
                $pagination['page'], 
                $pagination['limit']
            );
            
            $this->sendResponse($response, "Data panen retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve panen data: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get panen data by ID
     */
    private function getPanenById($id) {
        try {
            $query = "SELECT dp.*, 
                            u.filename as upload_filename,
                            u.original_filename,
                            u.upload_date,
                            users.full_name as uploaded_by_name
                     FROM data_panen dp 
                     JOIN uploads u ON dp.upload_id = u.id 
                     LEFT JOIN users ON u.uploaded_by = users.id
                     WHERE dp.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                $this->sendError("Panen record not found", 404);
            }
            
            $formatted_data = $this->formatPanenRecord($data);
            
            $this->sendResponse($formatted_data, "Panen record retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve panen record: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get panen statistics
     */
    private function getPanenStatistics() {
        try {
            $filters = $this->getFilters();
            $where_conditions = [];
            $params = [];
            
            // Apply same filters as main query
            if (!empty($filters['date_from'])) {
                $where_conditions[] = "dp.tanggal_pemeriksaan >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where_conditions[] = "dp.tanggal_pemeriksaan <= :date_to";
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
                        SUM(COALESCE(dp.kg_total, 0)) as total_kg,
                        AVG(COALESCE(dp.kg_total, 0)) as avg_kg,
                        SUM(COALESCE(dp.kg_brd, 0)) as total_kg_brd,
                        AVG(COALESCE(dp.kg_brd, 0)) as avg_kg_brd,
                        AVG(COALESCE(dp.bjr, 0)) as avg_bjr,
                        MIN(COALESCE(dp.bjr, 0)) as min_bjr,
                        MAX(COALESCE(dp.bjr, 0)) as max_bjr,
                        COUNT(DISTINCT dp.afdeling) as total_afdeling,
                        COUNT(DISTINCT dp.blok) as total_blok,
                        COUNT(DISTINCT dp.nama_pemanen) as total_pemanen,
                        MIN(dp.tanggal_pemeriksaan) as earliest_date,
                        MAX(dp.tanggal_pemeriksaan) as latest_date
                     FROM data_panen dp $where_clause";
            
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
                                     SUM(COALESCE(dp.kg_total, 0)) as total_kg,
                                     SUM(COALESCE(dp.kg_brd, 0)) as total_kg_brd,
                                     AVG(COALESCE(dp.bjr, 0)) as avg_bjr
                              FROM data_panen dp $where_clause
                              GROUP BY dp.afdeling
                              ORDER BY total_janjang DESC
                              LIMIT 10";
            
            $afdeling_stmt = $this->conn->prepare($afdeling_query);
            foreach ($params as $key => $value) {
                $afdeling_stmt->bindValue($key, $value);
            }
            $afdeling_stmt->execute();
            $top_afdelings = $afdeling_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get top pemanen
            $pemanen_query = "SELECT dp.nama_pemanen, dp.nik_pemanen,
                                    COUNT(*) as record_count,
                                    SUM(dp.jumlah_janjang) as total_janjang,
                                    SUM(COALESCE(dp.kg_total, 0)) as total_kg,
                                    SUM(COALESCE(dp.kg_brd, 0)) as total_kg_brd,
                                    AVG(COALESCE(dp.bjr, 0)) as avg_bjr
                             FROM data_panen dp $where_clause
                             GROUP BY dp.nama_pemanen, dp.nik_pemanen
                             ORDER BY total_janjang DESC
                             LIMIT 10";
            
            $pemanen_stmt = $this->conn->prepare($pemanen_query);
            foreach ($params as $key => $value) {
                $pemanen_stmt->bindValue($key, $value);
            }
            $pemanen_stmt->execute();
            $top_pemanen = $pemanen_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'overview' => [
                    'total_records' => (int)$stats['total_records'],
                    'total_janjang' => (int)$stats['total_janjang'],
                    'average_janjang' => round((float)$stats['avg_janjang'], 2),
                    'min_janjang' => (int)$stats['min_janjang'],
                    'max_janjang' => (int)$stats['max_janjang'],
                    'total_afdeling' => (int)$stats['total_afdeling'],
                    'total_blok' => (int)$stats['total_blok'],
                    'total_pemanen' => (int)$stats['total_pemanen'],
                    'date_range' => [
                        'from' => $stats['earliest_date'],
                        'to' => $stats['latest_date']
                    ]
                ],
                'top_afdelings' => $top_afdelings,
                'top_pemanen' => $top_pemanen
            ];
            
            $this->sendResponse($response, "Panen statistics retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve panen statistics: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get panen summary by date/afdeling
     */
    private function getPanenSummary() {
        try {
            $group_by = $_GET['group_by'] ?? 'date'; // date, afdeling, pemanen
            $filters = $this->getFilters();
            $where_conditions = [];
            $params = [];
            
            // Apply filters
            if (!empty($filters['date_from'])) {
                $where_conditions[] = "dp.tanggal_pemeriksaan >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where_conditions[] = "dp.tanggal_pemeriksaan <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            if (!empty($filters['afdeling'])) {
                $where_conditions[] = "dp.afdeling LIKE :afdeling";
                $params[':afdeling'] = '%' . $this->sanitizeLike($filters['afdeling']) . '%';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            switch ($group_by) {
                case 'date':
                    $group_field = 'dp.tanggal_pemeriksaan';
                    $select_field = 'dp.tanggal_pemeriksaan as group_key';
                    break;
                case 'afdeling':
                    $group_field = 'dp.afdeling';
                    $select_field = 'dp.afdeling as group_key';
                    break;
                case 'pemanen':
                    $group_field = 'dp.nama_pemanen, dp.nik_pemanen';
                    $select_field = 'CONCAT(dp.nama_pemanen, " (", dp.nik_pemanen, ")") as group_key';
                    break;
                default:
                    $this->sendError("Invalid group_by parameter. Use: date, afdeling, or pemanen", 400);
            }
            
            $query = "SELECT $select_field,
                            COUNT(*) as record_count,
                            SUM(dp.jumlah_janjang) as total_janjang,
                            AVG(dp.jumlah_janjang) as avg_janjang,
                            MIN(dp.jumlah_janjang) as min_janjang,
                            MAX(dp.jumlah_janjang) as max_janjang,
                            SUM(COALESCE(dp.kg_total, 0)) as total_kg,
                            AVG(COALESCE(dp.kg_total, 0)) as avg_kg,
                            SUM(COALESCE(dp.kg_brd, 0)) as total_kg_brd,
                            AVG(COALESCE(dp.kg_brd, 0)) as avg_kg_brd,
                            AVG(COALESCE(dp.bjr, 0)) as avg_bjr,
                            MIN(COALESCE(dp.bjr, 0)) as min_bjr,
                            MAX(COALESCE(dp.bjr, 0)) as max_bjr
                     FROM data_panen dp $where_clause
                     GROUP BY $group_field
                     ORDER BY total_janjang DESC
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
                    'group_key' => $row['group_key'],
                    'record_count' => (int)$row['record_count'],
                    'total_janjang' => (int)$row['total_janjang'],
                    'average_janjang' => round((float)$row['avg_janjang'], 2),
                    'min_janjang' => (int)$row['min_janjang'],
                    'max_janjang' => (int)$row['max_janjang']
                ];
            }, $summary);
            
            $response = [
                'group_by' => $group_by,
                'summary' => $formatted_summary
            ];
            
            $this->sendResponse($response, "Panen summary retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError("Failed to retrieve panen summary: " . $e->getMessage(), 500);
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
        
        if (isset($_GET['pemanen']) && !empty(trim($_GET['pemanen']))) {
            $filters['pemanen'] = trim($_GET['pemanen']);
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
        
        return $filters;
    }
    
    /**
     * Get sort parameters
     */
    private function getSortParams() {
        $allowed_fields = [
            'tanggal_pemeriksaan', 'afdeling', 'nama_pemanen', 'blok', 
            'no_ancak', 'no_tph', 'jumlah_janjang', 'jam', 'created_at'
        ];
        
        $field = $_GET['sort_by'] ?? 'tanggal_pemeriksaan';
        $direction = strtoupper($_GET['sort_direction'] ?? 'DESC');
        
        if (!in_array($field, $allowed_fields)) {
            $field = 'tanggal_pemeriksaan';
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
     * Format panen record - UPDATED TO INCLUDE bjr and kg_total
     */
    private function formatPanenRecord($record) {
        return [
            'id' => (int)$record['id'],
            'upload_id' => (int)$record['upload_id'],
            'nama_kerani' => $record['nama_kerani'],
            'tanggal_pemeriksaan' => $record['tanggal_pemeriksaan'],
            'afdeling' => $record['afdeling'],
            'nama_pemanen' => $record['nama_pemanen'],
            'nik_pemanen' => $record['nik_pemanen'],
            'blok' => $record['blok'],
            'no_ancak' => $record['no_ancak'],
            'no_tph' => $record['no_tph'],
            'jam' => $record['jam'],
            'last_modified' => $record['last_modified'],
            'koordinat' => $record['koordinat'] ? json_decode($record['koordinat'], true) : null,
            'jumlah_janjang' => (int)$record['jumlah_janjang'],
            'bjr' => $record['bjr'] !== null ? round((float)$record['bjr'], 2) : null,
            'kg_total' => $record['kg_total'] !== null ? round((float)$record['kg_total'], 2) : null,
            'kg_brd' => $record['kg_brd'] !== null ? round((float)$record['kg_brd'], 2) : null,
            'kg_berondolan' => $record['kg_brd'] !== null ? round((float)$record['kg_brd'], 2) : null, // alias for compatibility
            'koreksi_panen' => $record['koreksi_panen'] ? (int) $record['koreksi_panen'] : 0,
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
if (basename($_SERVER['SCRIPT_NAME'] ?? __FILE__) === 'panen.php') {
    $api = new PanenAPI();
    $api->handleRequest();
}
?>