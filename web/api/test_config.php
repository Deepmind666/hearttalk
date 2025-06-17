<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// 直接返回API配置，用于测试
try {
    // 模拟API配置数据
    $config = [
        'id' => 1,
        'api_key' => 'sk-5bfe7f1540f8420c835b8efb815d7a2c',
        'api_url' => 'https://api.deepseek.com/v1/chat/completions',
        'model_name' => 'deepseek-reasoner',
        'max_tokens' => 1000,
        'temperature' => 0.7,
        'system_prompt' => '你是"心语"，一位专业的心理健康服务大模型助手。',
        'api_key_masked' => 'sk-5bfe7****7a2c'
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $config,
        'message' => 'API配置获取成功',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '服务器错误: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
