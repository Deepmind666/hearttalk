// 用户数据同步脚本
// 用于在不同浏览器之间同步用户数据

class UserSync {
    constructor() {
        this.storageKey = 'hearttalk_users';
        this.syncEndpoint = 'save_user.php';
        this.lastSyncKey = 'last_sync_time';
    }

    // 获取本地用户数据
    getLocalUsers() {
        try {
            return JSON.parse(localStorage.getItem(this.storageKey) || '[]');
        } catch (error) {
            console.error('获取本地用户数据失败:', error);
            return [];
        }
    }

    // 保存本地用户数据
    saveLocalUsers(users) {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(users));
            localStorage.setItem(this.lastSyncKey, new Date().toISOString());
            return true;
        } catch (error) {
            console.error('保存本地用户数据失败:', error);
            return false;
        }
    }

    // 同步用户数据到服务器
    async syncToServer(userData) {
        try {
            const response = await fetch(this.syncEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();
            
            if (result.success) {
                console.log('用户数据已同步到服务器');
                return true;
            } else {
                console.error('同步到服务器失败:', result.message);
                return false;
            }
        } catch (error) {
            console.error('同步到服务器时发生错误:', error);
            return false;
        }
    }

    // 从服务器获取用户数据
    async syncFromServer() {
        try {
            const response = await fetch(this.syncEndpoint);
            const result = await response.json();
            
            if (result.success) {
                const serverUsers = result.data || [];
                const localUsers = this.getLocalUsers();
                
                // 合并本地和服务器数据
                const mergedUsers = this.mergeUserData(localUsers, serverUsers);
                
                // 保存合并后的数据
                this.saveLocalUsers(mergedUsers);
                
                console.log('从服务器同步了用户数据');
                return mergedUsers;
            } else {
                console.error('从服务器获取数据失败:', result.message);
                return this.getLocalUsers();
            }
        } catch (error) {
            console.error('从服务器同步数据时发生错误:', error);
            return this.getLocalUsers();
        }
    }

    // 合并用户数据
    mergeUserData(localUsers, serverUsers) {
        const merged = [...serverUsers];
        
        // 添加本地独有的用户
        localUsers.forEach(localUser => {
            const existsOnServer = serverUsers.some(serverUser => 
                serverUser.email === localUser.email
            );
            
            if (!existsOnServer) {
                merged.push(localUser);
                // 同步到服务器
                this.syncToServer(localUser);
            }
        });
        
        return merged;
    }

    // 注册新用户
    async registerUser(userData) {
        // 添加到本地存储
        const users = this.getLocalUsers();
        
        // 检查邮箱是否已存在
        if (users.some(user => user.email === userData.email)) {
            throw new Error('该邮箱已被注册');
        }
        
        // 添加用户
        users.push(userData);
        this.saveLocalUsers(users);
        
        // 同步到服务器
        await this.syncToServer(userData);
        
        return userData;
    }

    // 用户登录
    loginUser(email, password) {
        const users = this.getLocalUsers();
        const user = users.find(u => u.email === email && u.password === password);
        
        if (user) {
            // 更新最后登录时间
            user.last_login = new Date().toISOString();
            this.updateUser(user);
            return user;
        }
        
        return null;
    }

    // 更新用户信息
    updateUser(updatedUser) {
        const users = this.getLocalUsers();
        const index = users.findIndex(u => u.id === updatedUser.id);
        
        if (index !== -1) {
            users[index] = updatedUser;
            this.saveLocalUsers(users);
            
            // 同步到服务器
            this.syncToServer(updatedUser);
            
            return true;
        }
        
        return false;
    }

    // 删除用户
    deleteUser(userId) {
        const users = this.getLocalUsers();
        const filteredUsers = users.filter(user => user.id !== userId);
        
        this.saveLocalUsers(filteredUsers);
        
        // 这里可以添加服务器删除逻辑
        console.log('用户已删除:', userId);
        
        return true;
    }

    // 自动同步（页面加载时调用）
    async autoSync() {
        const lastSync = localStorage.getItem(this.lastSyncKey);
        const now = new Date();
        const lastSyncTime = lastSync ? new Date(lastSync) : new Date(0);

        // 降低同步间隔到5分钟，确保数据及时同步
        if (now - lastSyncTime > 5 * 60 * 1000) {
            console.log('执行自动同步...');
            await this.syncFromServer();
        }
    }

    // 强制同步（管理后台使用）
    async forceSync() {
        console.log('执行强制同步...');
        return await this.syncFromServer();
    }

    // 初始化（页面加载时调用）
    async init() {
        // 总是尝试从服务器同步最新数据
        console.log('初始化用户同步系统...');
        await this.syncFromServer();

        // 如果同步失败且本地没有数据，显示提示
        const localUsers = this.getLocalUsers();
        if (localUsers.length === 0) {
            console.log('没有用户数据，可能是首次访问');
        } else {
            console.log(`已加载 ${localUsers.length} 个用户`);
        }
    }
}

// 创建全局实例
window.userSync = new UserSync();

// 页面加载时初始化
document.addEventListener('DOMContentLoaded', function() {
    window.userSync.init();
});

// 兼容性函数（保持与现有代码的兼容性）
function getUsers() {
    return window.userSync.getLocalUsers();
}

function saveUser(userData) {
    return window.userSync.registerUser(userData);
}

function updateUser(userData) {
    return window.userSync.updateUser(userData);
}

function deleteUser(userId) {
    return window.userSync.deleteUser(userId);
}
