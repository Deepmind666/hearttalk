<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => '只支持POST请求'
        ]);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['message'])) {
        echo json_encode([
            'success' => false,
            'message' => '缺少消息内容'
        ]);
        exit;
    }
    
    $userMessage = $input['message'];
    $sessionId = isset($input['session_id']) ? $input['session_id'] : uniqid();
    
    // 获取API配置
    $config = $apiConfig->getConfig();
    
    if (!$config['api_key']) {
        echo json_encode([
            'success' => false,
            'message' => '未配置API密钥'
        ]);
        exit;
    }
    
    // 准备请求数据
    $requestData = [
        'model' => $config['model_name'],
        'messages' => [
            [
                'role' => 'system',
                'content' => $config['system_prompt']
            ],
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ],
        'max_tokens' => (int)$config['max_tokens'],
        'temperature' => (float)$config['temperature']
    ];
    
    // 发送请求到DeepSeek API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['api_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['api_key']
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode([
            'success' => false,
            'message' => 'API请求失败: ' . $error
        ]);
        exit;
    }
    
    if ($httpCode !== 200) {
        echo json_encode([
            'success' => false,
            'message' => 'API返回错误: HTTP ' . $httpCode
        ]);
        exit;
    }
    
    $responseData = json_decode($response, true);
    
    if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
        echo json_encode([
            'success' => false,
            'message' => 'API响应格式错误'
        ]);
        exit;
    }
    
    $aiResponse = $responseData['choices'][0]['message']['content'];
    
    // 保存聊天记录到数据库（可选）
    try {
        $query = "INSERT INTO chat_messages (session_id, user_message, ai_response) VALUES (:session_id, :user_message, :ai_response)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->bindParam(':user_message', $userMessage);
        $stmt->bindParam(':ai_response', $aiResponse);
        $stmt->execute();
    } catch (Exception $e) {
        // 记录日志但不影响响应
        error_log('保存聊天记录失败: ' . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => $aiResponse,
        'session_id' => $sessionId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '服务器错误: ' . $e->getMessage()
    ]);
}
?>
