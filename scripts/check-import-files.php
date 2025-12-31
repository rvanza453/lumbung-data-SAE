<?php
include_once '../includes/functions.php';
requireLogin();

$database = new Database();
$conn = $database->getConnection();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Available Files for Import</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .json { background: #e8f5e8; }
        .non-json { background: #ffe8e8; }
        .test-btn { padding: 5px 10px; margin: 2px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Files Available for Import</h1>
    
    <?php
    try {
        // Get all files
        $stmt = $conn->prepare("SELECT * FROM uploads ORDER BY upload_date DESC LIMIT 20");
        $stmt->execute();
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($files) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Filename</th><th>Extension</th><th>Path</th><th>Exists</th><th>Size</th><th>User</th><th>Actions</th></tr>";
            
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file['original_filename'], PATHINFO_EXTENSION));
                $path = getAbsolutePath($file['file_path']);
                $exists = file_exists($path);
                $size = $exists ? filesize($path) : 'N/A';
                
                $rowClass = ($ext === 'json') ? 'json' : 'non-json';
                
                echo "<tr class='$rowClass'>";
                echo "<td>" . $file['id'] . "</td>";
                echo "<td>" . htmlspecialchars($file['original_filename']) . "</td>";
                echo "<td>" . $ext . "</td>";
                echo "<td>" . htmlspecialchars($path) . "</td>";
                echo "<td>" . ($exists ? 'Yes' : 'No') . "</td>";
                echo "<td>" . $size . "</td>";
                echo "<td>" . $file['uploaded_by'] . "</td>";
                echo "<td>";
                if ($ext === 'json' && $exists) {
                    echo "<button class='test-btn' onclick='testImportFile(" . $file['id'] . ")'>Test Import</button>";
                    echo "<button class='test-btn' onclick='viewFileContent(" . $file['id'] . ")'>View Content</button>";
                }
                echo "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No files found in database.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <h2>Test Results</h2>
    <div id="testResults"></div>
    
    <script>
        function testImportFile(fileId) {
            document.getElementById('testResults').innerHTML = '<p>Testing file ID ' + fileId + '...</p>';
            
            fetch('import-to-monitoring-clean.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({fileIds: [fileId]})
            })
            .then(response => response.text())
            .then(text => {
                let html = '<h3>Test Result for File ID ' + fileId + '</h3>';
                html += '<pre>' + text + '</pre>';
                
                try {
                    const data = JSON.parse(text);
                    html += '<h4>Parsed Response:</h4>';
                    html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    
                    if (data.success && data.fileData && data.fileData.length > 0) {
                        html += '<p style="color: green;">SUCCESS! File can be imported.</p>';
                        
                        // Test monitoring URL
                        const fileDataJson = JSON.stringify(data.fileData);
                        const monitoringUrl = `monitoring.html?import=true&files=${encodeURIComponent(fileDataJson)}`;
                        html += '<p><a href="' + monitoringUrl + '" target="_blank">Open in Monitoring</a></p>';
                    } else {
                        html += '<p style="color: red;">FAILED: ' + (data.message || 'Unknown error') + '</p>';
                    }
                } catch (e) {
                    html += '<p style="color: red;">JSON Parse Error: ' + e.message + '</p>';
                }
                
                document.getElementById('testResults').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('testResults').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            });
        }
        
        function viewFileContent(fileId) {
            // Simple AJAX to view file content
            fetch('view-file-content.php?id=' + fileId)
            .then(response => response.text())
            .then(text => {
                const popup = window.open('', '_blank', 'width=800,height=600');
                popup.document.write('<pre>' + text + '</pre>');
            })
            .catch(error => {
                alert('Error viewing file: ' + error.message);
            });
        }
    </script>
</body>
</html>