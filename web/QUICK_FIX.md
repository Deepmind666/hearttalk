# 🚀 快速修复API问题

## 立即测试步骤

### 1. 检查服务器环境
访问：`http://www.hearttalk.me/server_info.php`

这个页面会显示：
- PHP版本和扩展
- 文件权限
- API配置测试
- DeepSeek连接测试

### 2. 测试API配置
访问：`http://www.hearttalk.me/api/test_config.php`

应该返回：
```json
{
  "success": true,
  "data": {
    "api_key": "sk-5bfe7f1540f8420c835b8efb815d7a2c",
    "api_key_masked": "sk-5bfe7****7a2c"
  }
}
```

### 3. 测试AI聊天
使用POST请求访问：`http://www.hearttalk.me/api/test_chat.php`

请求数据：
```json
{
  "message": "你好",
  "session_id": "test123"
}
```

### 4. 在线咨询页面测试
访问：`http://www.hearttalk.me/consultation.html`

现在页面上有一个"检查API状态"按钮，点击它查看详细的调试信息。

## 🔧 可能的问题和解决方案

### 问题1：PHP扩展缺失
如果server_info.php显示cURL或其他扩展未安装：
- 联系主机提供商启用这些扩展
- 或在php.ini中取消注释相关扩展

### 问题2：文件权限问题
如果显示目录不可写：
```bash
chmod 755 api/
chmod 644 api/*.php
```

### 问题3：网络连接问题
如果DeepSeek API无法连接：
- 检查服务器是否允许外网访问
- 检查防火墙设置
- 确认API密钥正确

### 问题4：CORS问题
如果浏览器控制台显示CORS错误：
- 检查API文件是否包含正确的CORS头
- 确认文件路径正确

## 📋 调试检查清单

- [ ] 访问server_info.php检查环境
- [ ] 访问api/test_config.php检查配置
- [ ] 在consultation.html点击"检查API状态"
- [ ] 查看浏览器控制台的错误信息
- [ ] 测试AI聊天功能

## 🎯 预期结果

修复成功后，在线咨询页面应该显示：
**"已连接心理健康服务大模型，为您提供智能对话服务！"**

## 📞 如果仍有问题

请提供以下信息：
1. server_info.php的显示结果
2. api/test_config.php的返回结果
3. 浏览器控制台的错误信息
4. "检查API状态"按钮的调试信息

联系：1403073295@qq.com
