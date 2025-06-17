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
    $userManager = new UserManager($db);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    switch ($method) {
        case 'GET':
            // 获取所有用户
            $users = $userManager->getAllUsers();
            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的请求数据'
                ]);
                exit;
            }
            
            $action = isset($input['action']) ? $input['action'] : '';
            
            if ($action === 'register') {
                // 用户注册
                $required_fields = ['username', 'email', 'password'];
                foreach ($required_fields as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        echo json_encode([
                            'success' => false,
                            'message' => "缺少必需字段: $field"
                        ]);
                        exit;
                    }
                }
                
                // 检查邮箱是否已存在
                if ($userManager->emailExists($input['email'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => '该邮箱已被注册'
                    ]);
                    exit;
                }
                
                $userData = [
                    'username' => $input['username'],
                    'email' => $input['email'],
                    'phone' => isset($input['phone']) ? $input['phone'] : '',
                    'password' => $input['password'], // 注意：实际应用中应该加密密码
                    'avatar' => isset($input['avatar']) ? $input['avatar'] : ''
                ];
                
                $userId = $userManager->register($userData);
                
                if ($userId) {
                    echo json_encode([
                        'success' => true,
                        'message' => '注册成功',
                        'user_id' => $userId
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => '注册失败'
                    ]);
                }
                
            } elseif ($action === 'login') {
                // 用户登录
                if (!isset($input['email']) || !isset($input['password'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => '邮箱和密码不能为空'
                    ]);
                    exit;
                }
                
                $user = $userManager->login($input['email'], $input['password']);
                
                if ($user) {
                    // 移除密码字段
                    unset($user['password']);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => '登录成功',
                        'user' => $user
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => '邮箱或密码错误'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '未知的操作'
                ]);
            }
            break;
            
        case 'DELETE':
            // 删除用户
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['user_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => '缺少用户ID'
                ]);
                exit;
            }
            
            if ($userManager->deleteUser($input['user_id'])) {
                echo json_encode([
                    'success' => true,
                    'message' => '用户删除成功'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '用户删除失败'
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
