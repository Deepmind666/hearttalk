<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $apiConfig = new ApiConfig($db);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // 获取API配置
            $config = $apiConfig->getConfig();
            
            // 为了安全，不返回完整的API密钥，只返回前几位和后几位
            if (isset($config['api_key']) && strlen($config['api_key']) > 8) {
                $config['api_key_masked'] = substr($config['api_key'], 0, 8) . '****' . substr($config['api_key'], -4);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $config
            ]);
            break;
            
        case 'POST':
            // 更新API配置
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的请求数据'
                ]);
                exit;
            }
            
            // 验证必需字段
            $required_fields = ['api_key', 'api_url', 'model_name'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    echo json_encode([
                        'success' => false,
                        'message' => "缺少必需字段: $field"
                    ]);
                    exit;
                }
            }
            
            // 设置默认值
            $config = [
                'api_key' => $input['api_key'],
                'api_url' => $input['api_url'],
                'model_name' => $input['model_name'],
                'max_tokens' => isset($input['max_tokens']) ? (int)$input['max_tokens'] : 1000,
                'temperature' => isset($input['temperature']) ? (float)$input['temperature'] : 0.7,
                'system_prompt' => isset($input['system_prompt']) ? $input['system_prompt'] : ''
            ];
            
            if ($apiConfig->updateConfig($config)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'API配置更新成功'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'API配置更新失败'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => '不支持的请求方法'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '服务器错误: ' . $e->getMessage()
    ]);
}
?>
