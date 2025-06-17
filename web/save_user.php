<?php
// 错误报告（调试用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// 简单的用户数据存储文件
$dataFile = 'users_data.json';

// 确保文件存在
if (!file_exists($dataFile)) {
    $result = file_put_contents($dataFile, '[]');
    if ($result === false) {
        echo json_encode([
            'success' => false,
            'message' => '无法创建数据文件，请检查目录权限'
        ]);
        exit;
    }
}

// 检查目录权限
$dir = dirname($dataFile);
if (!is_writable($dir)) {
    echo json_encode([
        'success' => false,
        'message' => '目录不可写，请检查目录权限: ' . $dir
    ]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 处理POST请求
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(['success' => false, 'message' => '无效的数据']);
            exit;
        }

        // 读取现有数据
        $users = [];
        if (file_exists($dataFile)) {
            $existingData = file_get_contents($dataFile);
            $users = json_decode($existingData, true) ?: [];
        }

        // 检查请求类型
        if (isset($input['action']) && $input['action'] === 'register') {
            // 处理用户注册
            $userData = $input['userData'];

            // 验证必需字段
            if (!isset($userData['username']) || !isset($userData['email']) || !isset($userData['password'])) {
                echo json_encode(['success' => false, 'message' => '缺少必需字段']);
                exit;
            }

            // 检查邮箱是否已存在
            foreach ($users as $user) {
                if ($user['email'] === $userData['email']) {
                    echo json_encode(['success' => false, 'message' => '该邮箱已被注册']);
                    exit;
                }
            }

            // 添加新用户
            $users[] = $userData;

            // 保存数据
            if (file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT))) {
                echo json_encode([
                    'success' => true,
                    'message' => '用户注册成功',
                    'user_id' => $userData['id'],
                    'total_users' => count($users)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => '保存用户数据失败']);
            }

        } elseif (isset($input['action']) && $input['action'] === 'login') {
            // 处理用户登录
            $email = $input['email'];
            $password = $input['password'];

            // 验证必需字段
            if (!$email || !$password) {
                echo json_encode(['success' => false, 'message' => '邮箱和密码不能为空']);
                exit;
            }

            // 查找用户
            $foundUser = null;
            foreach ($users as $index => $user) {
                if ($user['email'] === $email && $user['password'] === $password) {
                    $foundUser = $user;

                    // 更新最后登录时间
                    $users[$index]['last_login'] = date('c'); // ISO 8601 格式
                    file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT));

                    break;
                }
            }

            if ($foundUser) {
                // 移除密码字段，不返回给前端
                unset($foundUser['password']);

                echo json_encode([
                    'success' => true,
                    'message' => '登录成功',
                    'user' => $foundUser
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => '邮箱或密码错误']);
            }

        } elseif (isset($input['action']) && $input['action'] === 'delete') {
            // 处理删除用户
            $userId = $input['user_id'];

            if (!$userId) {
                echo json_encode(['success' => false, 'message' => '用户ID不能为空']);
                exit;
            }

            // 查找并删除用户
            $userDeleted = false;
            foreach ($users as $index => $user) {
                if ($user['id'] == $userId) {
                    array_splice($users, $index, 1);
                    $userDeleted = true;
                    break;
                }
            }

            if ($userDeleted) {
                // 保存更新后的数据
                file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT));

                echo json_encode([
                    'success' => true,
                    'message' => '用户已删除',
                    'total_users' => count($users)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => '用户不存在']);
            }

        } else {
            // 处理普通的用户数据保存/更新
            $userData = $input;

            // 检查用户是否已存在
            $userExists = false;
            foreach ($users as $index => $user) {
                if ($user['email'] === $userData['email']) {
                    // 更新现有用户
                    $users[$index] = $userData;
                    $userExists = true;
                    break;
                }
            }

            // 如果用户不存在，添加新用户
            if (!$userExists) {
                $users[] = $userData;
            }

            // 保存数据
            file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT));

            echo json_encode([
                'success' => true,
                'message' => '用户数据已保存',
                'total_users' => count($users)
            ]);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // 获取用户数据
        $users = [];
        if (file_exists($dataFile)) {
            $existingData = file_get_contents($dataFile);
            $users = json_decode($existingData, true) ?: [];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $users,
            'total_users' => count($users)
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '服务器错误: ' . $e->getMessage()
    ]);
}
?>
