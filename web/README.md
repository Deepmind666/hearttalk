# 智慧驿站·心语同行 - 网站文件

这个文件夹包含了完整的网站文件，可以直接上传到域名空间。

## 📁 文件结构
```
web/
├── index.html          # 首页
├── about.html          # 关于我们
├── assessment.html     # 心理测评
├── activities.html     # 活动中心
├── community.html      # 互动社区
├── consultation.html   # 在线咨询
├── login.html          # 登录页面
├── register.html       # 注册页面
├── css/
│   └── style.css       # 主样式文件
├── js/
│   └── main.js         # 主JavaScript文件
└── README.md           # 说明文档
```

## 🎯 导航栏统一标准

### 8个按钮位置（固定顺序）：
1. **首页** - index.html
2. **关于我们** - about.html
3. **心理测评** - assessment.html
4. **活动中心** - activities.html
5. **互动社区** - community.html
6. **在线咨询** - consultation.html
7. **登录** - login.html
8. **注册** - register.html

### 样式规范：
- **当前页面**：`class="btn btn-primary px-3 py-2"` (蓝色实心按钮)
- **其他页面**：`class="nav-link text-dark px-2 py-2 text-decoration-none"` (深色文字链接)

## 📱 移动端适配
- 响应式设计，支持手机、平板、桌面设备
- 导航栏在小屏幕上自动调整字体大小和间距
- 所有按钮和链接都有适当的触摸区域

## 🚀 部署说明

### 方法一：直接上传
1. 将web文件夹内的所有文件上传到域名空间根目录
2. 确保index.html作为默认首页
3. 检查所有链接和资源路径是否正确

### 方法二：FTP上传
1. 使用FTP客户端连接到您的域名空间
2. 将web文件夹内容上传到public_html或www目录
3. 设置index.html为默认首页

### 方法三：控制面板上传
1. 登录域名空间控制面板
2. 找到文件管理器
3. 上传web文件夹内的所有文件

## ✅ 功能特点
- 统一的导航栏设计
- 响应式布局
- 现代化UI设计
- 兼容主流浏览器
- 优化的移动端体验

## 🔧 技术栈
- HTML5
- CSS3 (Bootstrap 5.1.3)
- JavaScript (ES6+)
- Font Awesome 6.0.0
- 响应式设计

## 📞 支持
如有问题，请联系技术支持。
