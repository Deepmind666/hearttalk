-- 心语同行数据库初始化脚本
-- 请在您的MySQL数据库中执行此脚本

-- 创建数据库（如果不存在）
CREATE DATABASE IF NOT EXISTS hearttalk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE hearttalk_db;

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    avatar TEXT,
    register_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- API设置表
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

-- 测评结果表
CREATE TABLE IF NOT EXISTS assessment_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(50),
    assessment_type VARCHAR(50) NOT NULL,
    score INT NOT NULL,
    risk_level VARCHAR(20),
    answers JSON,
    duration INT, -- 完成时长（秒）
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 咨询预约表
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    type VARCHAR(50) NOT NULL,
    preferred_date DATETIME NOT NULL,
    urgency VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    submit_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 聊天记录表
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    user_message TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 插入默认API配置
INSERT INTO api_settings (id, api_key, api_url, model_name, max_tokens, temperature, system_prompt) 
VALUES (1, 'sk-5bfe7f1540f8420c835b8efb815d7a2c', 'https://api.deepseek.com/v1/chat/completions', 'deepseek-reasoner', 1000, 0.7, 
'你是"心语"，一位专业的心理健康服务大模型助手。你具备深厚的心理学知识和丰富的咨询经验，致力于为用户提供专业、温暖、个性化的心理健康支持。

核心原则：
1. 共情倾听：以非评判的态度倾听用户，理解并反映他们的情感体验
2. 专业引导：运用认知行为疗法、正念疗法等循证方法提供建议
3. 安全边界：不提供医疗诊断，遇到严重心理危机时建议寻求专业帮助
4. 个性化服务：根据用户的具体情况和需求调整沟通方式和建议内容
5. 积极赋能：帮助用户发现自身资源，培养应对困难的能力

请用温和、理解、专业的语气与用户交流，提供实用的心理健康建议和情感支持。')
ON DUPLICATE KEY UPDATE 
api_key = VALUES(api_key),
system_prompt = VALUES(system_prompt);

-- 创建索引以提高查询性能
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_assessment_user_id ON assessment_results(user_id);
CREATE INDEX idx_assessment_type ON assessment_results(assessment_type);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_chat_session ON chat_messages(session_id);
