<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// 数据库配置 - 请修改为您的实际数据库信息
$host = 'localhost';
$dbname = 'hearttalk_db';
$username = 'hearttalk_user';  // 修改为您的数据库用户名
$password = 'hearttalk_pass';  // 修改为您的数据库密码

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // 获取API配置
        $stmt = $pdo->prepare("SELECT * FROM api_settings WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config) {
            // 如果没有配置，插入默认配置
            $stmt = $pdo->prepare("INSERT INTO api_settings (id, api_key, api_url, model_name, max_tokens, temperature, system_prompt) VALUES (1, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                'sk-5bfe7f1540f8420c835b8efb815d7a2c',
                'https://api.deepseek.com/v1/chat/completions',
                'deepseek-reasoner',
                1000,
                0.7,
                '你是"心语"，一位专业的心理健康服务大模型助手。你具备深厚的心理学知识和丰富的咨询经验，致力于为用户提供专业、温暖、个性化的心理健康支持。'
            ]);
            
            // 重新获取配置
            $stmt = $pdo->prepare("SELECT * FROM api_settings WHERE id = 1");
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // 遮蔽API密钥
        if ($config && isset($config['api_key']) && strlen($config['api_key']) > 8) {
            $config['api_key_masked'] = substr($config['api_key'], 0, 8) . '****' . substr($config['api_key'], -4);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $config
        ]);
        
    } elseif ($method === 'POST') {
        // 更新API配置
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => '无效的请求数据']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO api_settings (id, api_key, api_url, model_name, max_tokens, temperature, system_prompt, updated_at) VALUES (1, ?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE api_key = VALUES(api_key), api_url = VALUES(api_url), model_name = VALUES(model_name), max_tokens = VALUES(max_tokens), temperature = VALUES(temperature), system_prompt = VALUES(system_prompt), updated_at = NOW()");
        
        $result = $stmt->execute([
            $input['api_key'] ?? 'sk-5bfe7f1540f8420c835b8efb815d7a2c',
            $input['api_url'] ?? 'https://api.deepseek.com/v1/chat/completions',
            $input['model_name'] ?? 'deepseek-reasoner',
            $input['max_tokens'] ?? 1000,
            $input['temperature'] ?? 0.7,
            $input['system_prompt'] ?? '你是"心语"，一位专业的心理健康服务大模型助手。'
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'API配置更新成功']);
        } else {
            echo json_encode(['success' => false, 'message' => 'API配置更新失败']);
        }
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => '数据库错误: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '服务器错误: ' . $e->getMessage()
    ]);
}
?>
