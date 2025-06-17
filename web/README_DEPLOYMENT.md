# 智慧驿站·心语同行 - 部署说明

## 🚀 部署步骤

### 1. 数据库配置

#### 1.1 创建数据库
```sql
-- 在您的MySQL数据库中执行以下命令
CREATE DATABASE hearttalk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 1.2 执行初始化脚本
```bash
# 在MySQL中执行初始化脚本
mysql -u your_username -p hearttalk_db < setup/init_database.sql
```

#### 1.3 修改数据库配置
编辑 `config/database.php` 文件，修改以下配置：

```php
private $host = 'localhost';           // 数据库主机
private $db_name = 'hearttalk_db';     // 数据库名称
private $username = 'your_db_username'; // 数据库用户名
private $password = 'your_db_password'; // 数据库密码
```

### 2. 文件权限设置

确保以下目录具有写入权限：
```bash
chmod 755 api/
chmod 755 config/
chmod 644 config/database.php
```

### 3. API密钥配置

您的DeepSeek API密钥已经预设在系统中：
- API密钥：`sk-5bfe7f1540f8420c835b8efb815d7a2c`
- API地址：`https://api.deepseek.com/v1/chat/completions`
- 模型：`deepseek-reasoner`

如需修改，请登录管理后台进行配置。

### 4. 管理员账户

默认管理员账户：
- 用户名：`admin`
- 密码：`123456789`

### 5. 测试部署

#### 5.1 测试数据库连接
访问：`http://your-domain.com/api/config.php`
应该返回API配置信息（API密钥会被部分遮蔽）

#### 5.2 测试用户注册
1. 访问注册页面
2. 注册一个测试用户
3. 登录管理后台查看用户是否成功保存

#### 5.3 测试AI聊天
1. 访问在线咨询页面
2. 与智能助手对话
3. 确认返回的是AI回复而不是预设回复

## 🔧 故障排除

### 问题1：数据库连接失败
**解决方案：**
1. 检查数据库配置信息是否正确
2. 确认数据库服务是否运行
3. 检查数据库用户权限

### 问题2：API调用失败
**解决方案：**
1. 检查API密钥是否正确
2. 确认服务器可以访问外网
3. 检查PHP的curl扩展是否启用

### 问题3：用户数据不同步
**解决方案：**
1. 清除浏览器localStorage
2. 检查API接口是否正常工作
3. 确认数据库表结构正确

## 📁 文件结构

```
web/
├── api/                    # 后端API接口
│   ├── chat.php           # AI聊天接口
│   ├── config.php         # API配置接口
│   └── users.php          # 用户管理接口
├── config/                # 配置文件
│   └── database.php       # 数据库配置
├── setup/                 # 安装脚本
│   └── init_database.sql  # 数据库初始化脚本
├── js/                    # JavaScript文件
├── css/                   # 样式文件
├── images/                # 图片资源
└── *.html                 # 前端页面
```

## 🔒 安全建议

1. **修改默认密码**：部署后立即修改管理员密码
2. **数据库安全**：使用强密码，限制数据库访问权限
3. **API密钥保护**：定期更换API密钥
4. **HTTPS部署**：建议使用HTTPS协议
5. **定期备份**：定期备份数据库和重要文件

## 📞 技术支持

如遇到部署问题，请联系：
- 邮箱：1403073295@qq.com
- 技术负责人：李康锐
- 维护单位：广东工业大学自动化学院本科物联网党支部

## 🎉 部署完成

部署完成后，您的网站将具备以下功能：
- ✅ 跨浏览器用户注册登录
- ✅ 统一的API配置管理
- ✅ 智能AI心理健康助手
- ✅ 完整的管理后台功能
- ✅ 数据持久化存储

祝您使用愉快！🎊
