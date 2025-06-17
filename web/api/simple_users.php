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
        // 获取所有用户
        $stmt = $pdo->prepare("SELECT * FROM users ORDER BY register_time DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
        
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => '无效的请求数据']);
            exit;
        }
        
        $action = $input['action'] ?? '';
        
        if ($action === 'register') {
            // 用户注册
            $required_fields = ['username', 'email', 'password'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    echo json_encode(['success' => false, 'message' => "缺少必需字段: $field"]);
                    exit;
                }
            }
            
            // 检查邮箱是否已存在
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$input['email']]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => '该邮箱已被注册']);
                exit;
            }
            
            // 插入新用户
            $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password, avatar, register_time) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([
                $input['username'],
                $input['email'],
                $input['phone'] ?? '',
                $input['password'], // 注意：实际应用中应该加密密码
                $input['avatar'] ?? ''
            ]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => '注册成功',
                    'user_id' => $pdo->lastInsertId()
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => '注册失败']);
            }
            
        } elseif ($action === 'login') {
            // 用户登录
            if (!isset($input['email']) || !isset($input['password'])) {
                echo json_encode(['success' => false, 'message' => '邮箱和密码不能为空']);
                exit;
            }
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
            $stmt->execute([$input['email'], $input['password']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // 更新最后登录时间
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // 移除密码字段
                unset($user['password']);
                
                echo json_encode([
                    'success' => true,
                    'message' => '登录成功',
                    'user' => $user
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => '邮箱或密码错误']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '未知的操作']);
        }
        
    } elseif ($method === 'DELETE') {
        // 删除用户
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['user_id'])) {
            echo json_encode(['success' => false, 'message' => '缺少用户ID']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$input['user_id']]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => '用户删除成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '用户删除失败']);
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
