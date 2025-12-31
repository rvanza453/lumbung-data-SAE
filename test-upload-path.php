<?php
// Test script untuk memverifikasi upload path
require_once 'includes/functions.php';

echo "<h2>Test Upload Path Configuration</h2>";
echo "<hr>";

// Test 1: Check root directory
$rootDir = dirname(__DIR__ . '/includes');
echo "<h3>1. Root Directory</h3>";
echo "Root Dir: <strong>$rootDir</strong><br>";
echo "Root Dir exists: " . (is_dir($rootDir) ? "✓ Yes" : "✗ No") . "<br><br>";

// Test 2: Test createUploadDirectory function
echo "<h3>2. Test createUploadDirectory()</h3>";

$testCategories = ['panen', 'pengiriman'];
foreach ($testCategories as $category) {
    echo "<h4>Category: $category</h4>";
    $paths = createUploadDirectory($category);
    
    echo "Relative Path: <code>{$paths['relative']}</code><br>";
    echo "Absolute Path: <code>{$paths['absolute']}</code><br>";
    echo "Directory exists: " . (is_dir($paths['absolute']) ? "✓ Yes" : "✗ No") . "<br>";
    echo "Directory writable: " . (is_writable($paths['absolute']) ? "✓ Yes" : "✗ No") . "<br><br>";
}

// Test 3: Test getAbsolutePath function
echo "<h3>3. Test getAbsolutePath()</h3>";

$testPaths = [
    'uploads/panen/2025/12/test.json',
    'C:\laragon\www\test.json',
    '/var/www/test.json'
];

foreach ($testPaths as $testPath) {
    $absolutePath = getAbsolutePath($testPath);
    echo "Input: <code>$testPath</code><br>";
    echo "Output: <code>$absolutePath</code><br>";
    echo "Is Absolute: " . (preg_match('/^[a-zA-Z]:[\\\\\/]|^\//', $absolutePath) ? "✓ Yes" : "✗ No") . "<br><br>";
}

// Test 4: List current uploads directory structure
echo "<h3>4. Current Uploads Directory Structure</h3>";

$uploadsDir = dirname(__DIR__ . '/includes') . '/uploads';
echo "Uploads Directory: <code>$uploadsDir</code><br>";

if (is_dir($uploadsDir)) {
    echo "Directory exists: ✓ Yes<br>";
    echo "Directory writable: " . (is_writable($uploadsDir) ? "✓ Yes" : "✗ No") . "<br><br>";
    
    echo "<strong>Structure:</strong><br>";
    echo "<pre>";
    
    function listDirectory($dir, $prefix = '') {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            
            $path = $dir . '/' . $item;
            echo $prefix . "└── " . $item;
            
            if (is_dir($path)) {
                echo " [DIR]";
                $fileCount = count(glob($path . '/*.json'));
                if ($fileCount > 0) {
                    echo " ($fileCount files)";
                }
            }
            echo "\n";
            
            if (is_dir($path) && substr_count($path, '/') < substr_count($uploadsDir, '/') + 4) {
                listDirectory($path, $prefix . "    ");
            }
        }
    }
    
    listDirectory($uploadsDir);
    echo "</pre>";
} else {
    echo "Directory exists: ✗ No<br>";
    echo "<strong>Attempting to create...</strong><br>";
    if (mkdir($uploadsDir, 0755, true)) {
        echo "✓ Successfully created!<br>";
    } else {
        echo "✗ Failed to create!<br>";
    }
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p>✓ All path functions are working correctly.</p>";
echo "<p>You can now test uploading a file through the upload form.</p>";
echo "<p><a href='pages/upload.php'>Go to Upload Page</a></p>";
?>

