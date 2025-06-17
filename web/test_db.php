<?php
// 数据库连接测试文件
header('Content-Type: application/json; charset=utf-8');

// 简化的数据库连接测试
try {
    // 请根据您的实际数据库信息修改以下配置
    $host = 'localhost';
    $dbname = 'hearttalk_db';
    $username = 'hearttalk_user';  // 修改为您的数据库用户名
    $password = 'hearttalk_pass';  // 修改为您的数据库密码
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 测试查询
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt2 = $pdo->query("SELECT * FROM api_settings WHERE id = 1");
    $apiSettings = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => '数据库连接成功',
        'user_count' => $userCount['count'],
        'api_configured' => !empty($apiSettings['api_key']),
        'api_key_preview' => $apiSettings ? substr($apiSettings['api_key'], 0, 8) . '****' : 'Not set'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => '数据库连接失败: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '其他错误: ' . $e->getMessage()
    ]);
}
?>
