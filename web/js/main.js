// 智慧驿站·心语同行 - 主JavaScript文件

// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    console.log('智慧驿站·心语同行 - 页面加载完成');
    
    // 初始化功能
    initializeFeatures();
});

// 初始化各种功能
function initializeFeatures() {
    // 平滑滚动
    initSmoothScroll();
    
    // 导航栏交互
    initNavbarInteractions();
}

// 平滑滚动
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
}

// 导航栏交互
function initNavbarInteractions() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// 通用提示函数
function showAlert(message, type = 'info') {
    alert(message);
}

// 功能开发中提示
function showDevelopmentAlert(feature) {
    showAlert(`${feature}功能正在开发中，敬请期待！`);
}
