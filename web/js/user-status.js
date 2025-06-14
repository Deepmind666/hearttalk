// 用户状态管理脚本
(function() {
    // 获取当前登录用户
    function getCurrentUser() {
        return JSON.parse(localStorage.getItem('currentUser') || 'null');
    }
    
    // 用户退出登录
    function logout() {
        if (confirm('确定要退出登录吗？')) {
            localStorage.removeItem('currentUser');
            localStorage.removeItem('rememberUser');
            window.location.reload();
        }
    }
    
    // 生成用户头像
    function generateUserAvatar(user) {
        if (user.avatar) {
            return `<img src="${user.avatar}" alt="${user.username}" class="user-avatar-img">`;
        } else {
            // 使用用户名首字母作为默认头像
            const initial = user.username.charAt(0).toUpperCase();
            return `<div class="user-avatar-default">${initial}</div>`;
        }
    }
    
    // 更新导航栏用户状态
    function updateUserStatus() {
        const currentUser = getCurrentUser();
        const userActions = document.querySelector('.user-actions');
        
        if (!userActions) return;
        
        if (currentUser) {
            // 用户已登录，显示用户信息
            userActions.innerHTML = `
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle d-flex align-items-center text-decoration-none" 
                            type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar me-2">
                            ${generateUserAvatar(currentUser)}
                        </div>
                        <span class="user-name">${currentUser.username}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><h6 class="dropdown-header">欢迎，${currentUser.username}</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="profile.html"><i class="fas fa-user me-2"></i>个人资料</a></li>
                        <li><a class="dropdown-item" href="assessment.html"><i class="fas fa-chart-bar me-2"></i>我的测评</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>退出登录</a></li>
                    </ul>
                </div>
            `;
        } else {
            // 用户未登录，显示登录注册按钮
            userActions.innerHTML = `
                <a href="login.html" class="nav-link text-dark px-2 py-2 text-decoration-none">登录</a>
                <a href="register.html" class="nav-link text-dark px-2 py-2 text-decoration-none">注册</a>
            `;
        }
    }
    
    // 添加用户头像样式
    function addUserAvatarStyles() {
        if (document.getElementById('userAvatarStyles')) return;
        
        const style = document.createElement('style');
        style.id = 'userAvatarStyles';
        style.textContent = `
            .user-avatar {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            
            .user-avatar-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .user-avatar-default {
                color: white;
                font-weight: bold;
                font-size: 14px;
            }
            
            .user-name {
                color: #333;
                font-weight: 500;
                max-width: 100px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .dropdown-toggle::after {
                margin-left: 0.5rem;
            }
            
            .dropdown-menu {
                border: none;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
                min-width: 200px;
            }
            
            .dropdown-item {
                padding: 0.5rem 1rem;
                border-radius: 5px;
                margin: 0 0.5rem;
            }
            
            .dropdown-item:hover {
                background-color: #f8f9fa;
            }
            
            @media (max-width: 768px) {
                .user-name {
                    display: none;
                }
                
                .user-avatar {
                    width: 28px;
                    height: 28px;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // 页面加载完成后初始化
    document.addEventListener('DOMContentLoaded', function() {
        addUserAvatarStyles();
        updateUserStatus();
    });
    
    // 将logout函数暴露到全局作用域
    window.logout = logout;
})();
