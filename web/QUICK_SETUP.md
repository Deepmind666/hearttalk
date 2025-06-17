# 快速部署指南

## 🚀 立即解决问题的步骤

### 第1步：修改数据库配置

编辑以下文件中的数据库连接信息：

1. `test_db.php` (第7-10行)
2. `api/simple_config.php` (第12-15行)  
3. `api/simple_users.php` (第12-15行)
4. `api/simple_chat.php` (第12-15行)

将以下信息替换为您的实际数据库信息：
```php
$host = 'localhost';           // 数据库主机
$dbname = 'hearttalk_db';      // 数据库名称  
$username = 'your_username';   // 您的数据库用户名
$password = 'your_password';   // 您的数据库密码
```

### 第2步：创建数据库和表

在您的MySQL数据库中执行以下SQL：

```sql
-- 创建数据库
CREATE DATABASE IF NOT EXISTS hearttalk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE hearttalk_db;

-- 创建用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    avatar TEXT,
    register_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
);

-- 创建API设置表
CREATE TABLE IF NOT EXISTS api_settings (
    id INT PRIMARY KEY DEFAULT 1,
    api_key VARCHAR(255) NOT NULL,
    api_url VARCHAR(255) DEFAULT 'https://api.deepseek.com/v1/chat/completions',
    model_name VARCHAR(100) DEFAULT 'deepseek-reasoner',
    max_tokens INT DEFAULT 1000,
    temperature DECIMAL(3,2) DEFAULT 0.70,
    system_prompt TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 创建聊天记录表
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    user_message TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 插入您的API配置
INSERT INTO api_settings (id, api_key, api_url, model_name, max_tokens, temperature, system_prompt) 
VALUES (1, 'sk-5bfe7f1540f8420c835b8efb815d7a2c', 'https://api.deepseek.com/v1/chat/completions', 'deepseek-reasoner', 1000, 0.7, 
'你是"心语"，一位专业的心理健康服务大模型助手。你具备深厚的心理学知识和丰富的咨询经验，致力于为用户提供专业、温暖、个性化的心理健康支持。')
ON DUPLICATE KEY UPDATE 
api_key = VALUES(api_key),
system_prompt = VALUES(system_prompt);
```

### 第3步：测试数据库连接

访问：`http://www.hearttalk.me/test_db.php`

如果看到类似以下内容说明数据库连接成功：
```json
{
  "success": true,
  "message": "数据库连接成功",
  "user_count": 0,
  "api_configured": true,
  "api_key_preview": "sk-5bfe7****"
}
```

### 第4步：测试功能

1. **测试用户注册**：访问注册页面，注册一个新用户
2. **测试AI聊天**：访问在线咨询页面，与智能助手对话
3. **检查管理后台**：登录管理后台查看用户数据

## 🔧 常见问题解决

### 问题1：数据库连接失败
- 检查数据库服务是否运行
- 确认数据库用户名和密码正确
- 确认数据库名称存在

### 问题2：API不工作
- 检查服务器是否支持curl
- 确认服务器可以访问外网
- 检查API密钥是否正确

### 问题3：注册失败
- 检查数据库表是否创建成功
- 确认邮箱格式正确
- 检查是否有重复邮箱

## 📞 需要帮助？

如果遇到问题，请提供以下信息：
1. 访问 `test_db.php` 的返回结果
2. 浏览器控制台的错误信息
3. 服务器错误日志

联系方式：1403073295@qq.com
