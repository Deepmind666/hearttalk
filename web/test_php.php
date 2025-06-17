<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$testResults = [];

// 测试PHP基本信息
$testResults['php_version'] = PHP_VERSION;
$testResults['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$testResults['document_root'] = $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown';
$testResults['current_dir'] = __DIR__;

// 测试文件操作权限
$testFile = 'test_write.txt';
$testResults['can_create_file'] = false;
$testResults['can_write_file'] = false;
$testResults['can_read_file'] = false;
$testResults['can_delete_file'] = false;

try {
    // 测试创建文件
    $result = file_put_contents($testFile, 'test content');
    if ($result !== false) {
        $testResults['can_create_file'] = true;
        $testResults['can_write_file'] = true;
        
        // 测试读取文件
        $content = file_get_contents($testFile);
        if ($content === 'test content') {
            $testResults['can_read_file'] = true;
        }
        
        // 测试删除文件
        if (unlink($testFile)) {
            $testResults['can_delete_file'] = true;
        }
    }
} catch (Exception $e) {
    $testResults['file_error'] = $e->getMessage();
}

// 测试JSON操作
$testResults['json_encode_works'] = false;
$testResults['json_decode_works'] = false;

try {
    $testArray = ['test' => 'value', 'number' => 123];
    $jsonString = json_encode($testArray);
    if ($jsonString !== false) {
        $testResults['json_encode_works'] = true;
        
        $decodedArray = json_decode($jsonString, true);
        if ($decodedArray && $decodedArray['test'] === 'value') {
            $testResults['json_decode_works'] = true;
        }
    }
} catch (Exception $e) {
    $testResults['json_error'] = $e->getMessage();
}

// 测试POST数据接收
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testResults['request_method'] = 'POST';
    $testResults['content_type'] = $_SERVER['CONTENT_TYPE'] ?? 'Not set';
    
    $input = file_get_contents('php://input');
    $testResults['raw_input'] = $input;
    $testResults['input_length'] = strlen($input);
    
    if ($input) {
        $decoded = json_decode($input, true);
        $testResults['json_decode_success'] = ($decoded !== null);
        if ($decoded) {
            $testResults['decoded_data'] = $decoded;
        } else {
            $testResults['json_last_error'] = json_last_error_msg();
        }
    }
} else {
    $testResults['request_method'] = $_SERVER['REQUEST_METHOD'];
}

// 测试users_data.json文件
$dataFile = 'users_data.json';
$testResults['data_file_exists'] = file_exists($dataFile);
$testResults['data_file_readable'] = is_readable($dataFile);
$testResults['data_file_writable'] = is_writable($dataFile);

if (file_exists($dataFile)) {
    $testResults['data_file_size'] = filesize($dataFile);
    $testResults['data_file_content'] = file_get_contents($dataFile);
} else {
    // 尝试创建文件
    $result = file_put_contents($dataFile, '[]');
    $testResults['data_file_create_result'] = ($result !== false);
    if ($result !== false) {
        $testResults['data_file_exists'] = true;
        $testResults['data_file_readable'] = is_readable($dataFile);
        $testResults['data_file_writable'] = is_writable($dataFile);
    }
}

// 测试目录权限
$testResults['current_dir_writable'] = is_writable('.');
$testResults['current_dir_readable'] = is_readable('.');

// 返回测试结果
echo json_encode([
    'success' => true,
    'message' => 'PHP环境测试完成',
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => $testResults
], JSON_PRETTY_PRINT);
?>
