<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => '只支持POST请求']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['message'])) {
        echo json_encode(['success' => false, 'message' => '缺少消息内容']);
        exit;
    }
    
    $userMessage = $input['message'];
    $sessionId = $input['session_id'] ?? uniqid();
    
    // 获取API配置
    $stmt = $pdo->prepare("SELECT * FROM api_settings WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config || !$config['api_key']) {
        echo json_encode(['success' => false, 'message' => '未配置API密钥']);
        exit;
    }
    
    // 准备请求数据
    $requestData = [
        'model' => $config['model_name'] ?? 'deepseek-reasoner',
        'messages' => [
            [
                'role' => 'system',
                'content' => $config['system_prompt'] ?? '你是"心语"，一位专业的心理健康服务大模型助手。'
            ],
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ],
        'max_tokens' => (int)($config['max_tokens'] ?? 1000),
        'temperature' => (float)($config['temperature'] ?? 0.7)
    ];
    
    // 发送请求到DeepSeek API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['api_url'] ?? 'https://api.deepseek.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['api_key']
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 如果有SSL问题可以添加这行
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode(['success' => false, 'message' => 'API请求失败: ' . $error]);
        exit;
    }
    
    if ($httpCode !== 200) {
        echo json_encode(['success' => false, 'message' => 'API返回错误: HTTP ' . $httpCode . ' Response: ' . $response]);
        exit;
    }
    
    $responseData = json_decode($response, true);
    
    if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
        echo json_encode(['success' => false, 'message' => 'API响应格式错误: ' . $response]);
        exit;
    }
    
    $aiResponse = $responseData['choices'][0]['message']['content'];
    
    // 保存聊天记录到数据库（可选）
    try {
        $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, user_message, ai_response) VALUES (?, ?, ?)");
        $stmt->execute([$sessionId, $userMessage, $aiResponse]);
    } catch (Exception $e) {
        // 记录日志但不影响响应
        error_log('保存聊天记录失败: ' . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => $aiResponse,
        'session_id' => $sessionId
    ]);
    
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
