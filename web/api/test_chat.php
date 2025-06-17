<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

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

// 使用您的API密钥直接调用DeepSeek API
$apiKey = 'sk-5bfe7f1540f8420c835b8efb815d7a2c';
$apiUrl = 'https://api.deepseek.com/v1/chat/completions';

$requestData = [
    'model' => 'deepseek-reasoner',
    'messages' => [
        [
            'role' => 'system',
            'content' => '你是"心语"，一位专业的心理健康服务大模型助手。你具备深厚的心理学知识和丰富的咨询经验，致力于为用户提供专业、温暖、个性化的心理健康支持。

核心原则：
1. 共情倾听：以非评判的态度倾听用户，理解并反映他们的情感体验
2. 专业引导：运用认知行为疗法、正念疗法等循证方法提供建议
3. 安全边界：不提供医疗诊断，遇到严重心理危机时建议寻求专业帮助
4. 个性化服务：根据用户的具体情况和需求调整沟通方式和建议内容
5. 积极赋能：帮助用户发现自身资源，培养应对困难的能力

请用温和、理解、专业的语气与用户交流，提供实用的心理健康建议和情感支持。'
        ],
        [
            'role' => 'user',
            'content' => $userMessage
        ]
    ],
    'max_tokens' => 1000,
    'temperature' => 0.7
];

// 发送请求到DeepSeek API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode([
        'success' => false, 
        'message' => '网络请求失败: ' . $error,
        'fallback' => true
    ]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode([
        'success' => false, 
        'message' => 'API返回错误: HTTP ' . $httpCode,
        'response' => $response,
        'fallback' => true
    ]);
    exit;
}

$responseData = json_decode($response, true);

if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'API响应格式错误',
        'response' => $response,
        'fallback' => true
    ]);
    exit;
}

$aiResponse = $responseData['choices'][0]['message']['content'];

echo json_encode([
    'success' => true,
    'message' => $aiResponse,
    'session_id' => $sessionId,
    'model' => 'deepseek-reasoner',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
