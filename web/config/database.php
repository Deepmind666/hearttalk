<?php
// 数据库配置文件
class Database {
    private $host = 'localhost';
    private $db_name = 'hearttalk_db';
    private $username = 'hearttalk_user';  // 请根据您的实际数据库用户名修改
    private $password = 'hearttalk_pass';  // 请根据您的实际数据库密码修改
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "连接失败: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// API配置管理类
class ApiConfig {
    private $conn;
    private $table_name = "api_settings";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 获取API配置
    public function getConfig() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $row;
        } else {
            // 返回默认配置
            return [
                'api_key' => 'sk-5bfe7f1540f8420c835b8efb815d7a2c',
                'api_url' => 'https://api.deepseek.com/v1/chat/completions',
                'model_name' => 'deepseek-reasoner',
                'max_tokens' => 1000,
                'temperature' => 0.7,
                'system_prompt' => '你是"心语"，一位专业的心理健康服务大模型助手。你具备深厚的心理学知识和丰富的咨询经验，致力于为用户提供专业、温暖、个性化的心理健康支持。'
            ];
        }
    }

    // 更新API配置
    public function updateConfig($config) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id, api_key, api_url, model_name, max_tokens, temperature, system_prompt, updated_at) 
                  VALUES (1, :api_key, :api_url, :model_name, :max_tokens, :temperature, :system_prompt, NOW())
                  ON DUPLICATE KEY UPDATE 
                  api_key = :api_key, 
                  api_url = :api_url, 
                  model_name = :model_name, 
                  max_tokens = :max_tokens, 
                  temperature = :temperature, 
                  system_prompt = :system_prompt,
                  updated_at = NOW()";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':api_key', $config['api_key']);
        $stmt->bindParam(':api_url', $config['api_url']);
        $stmt->bindParam(':model_name', $config['model_name']);
        $stmt->bindParam(':max_tokens', $config['max_tokens']);
        $stmt->bindParam(':temperature', $config['temperature']);
        $stmt->bindParam(':system_prompt', $config['system_prompt']);
        
        return $stmt->execute();
    }
}

// 用户管理类
class UserManager {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 注册用户
    public function register($userData) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, phone, password, avatar, register_time) 
                  VALUES (:username, :email, :phone, :password, :avatar, NOW())";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $userData['username']);
        $stmt->bindParam(':email', $userData['email']);
        $stmt->bindParam(':phone', $userData['phone']);
        $stmt->bindParam(':password', $userData['password']);
        $stmt->bindParam(':avatar', $userData['avatar']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // 用户登录
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email AND password = :password";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // 更新最后登录时间
            $updateQuery = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':id', $user['id']);
            $updateStmt->execute();
            
            return $user;
        }
        return false;
    }

    // 获取所有用户
    public function getAllUsers() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY register_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 删除用户
    public function deleteUser($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    // 检查邮箱是否已存在
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>
