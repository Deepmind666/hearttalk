<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>服务器环境检查</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>服务器环境检查</h1>
    
    <h2>PHP基本信息</h2>
    <div class="info">
        <strong>PHP版本:</strong> <?php echo PHP_VERSION; ?><br>
        <strong>服务器时间:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
        <strong>服务器软件:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? '未知'; ?>
    </div>
    
    <h2>扩展检查</h2>
    <ul>
        <li>cURL: <?php echo extension_loaded('curl') ? '<span class="success">✓ 已安装</span>' : '<span class="error">✗ 未安装</span>'; ?></li>
        <li>PDO: <?php echo extension_loaded('pdo') ? '<span class="success">✓ 已安装</span>' : '<span class="error">✗ 未安装</span>'; ?></li>
        <li>PDO MySQL: <?php echo extension_loaded('pdo_mysql') ? '<span class="success">✓ 已安装</span>' : '<span class="error">✗ 未安装</span>'; ?></li>
        <li>JSON: <?php echo extension_loaded('json') ? '<span class="success">✓ 已安装</span>' : '<span class="error">✗ 未安装</span>'; ?></li>
        <li>OpenSSL: <?php echo extension_loaded('openssl') ? '<span class="success">✓ 已安装</span>' : '<span class="error">✗ 未安装</span>'; ?></li>
    </ul>
    
    <h2>文件权限检查</h2>
    <ul>
        <?php
        $dirs = ['api/', 'config/'];
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    echo "<li>$dir: <span class='success'>✓ 可写</span></li>";
                } else {
                    echo "<li>$dir: <span class='warning'>⚠ 只读</span></li>";
                }
            } else {
                echo "<li>$dir: <span class='error'>✗ 不存在</span></li>";
            }
        }
        ?>
    </ul>
    
    <h2>API测试</h2>
    <div id="apiTest">
        <button onclick="testApi()">测试API配置</button>
        <div id="apiResult"></div>
    </div>
    
    <h2>网络连接测试</h2>
    <div id="networkTest">
        <button onclick="testNetwork()">测试DeepSeek连接</button>
        <div id="networkResult"></div>
    </div>
    
    <script>
        async function testApi() {
            const result = document.getElementById('apiResult');
            result.innerHTML = '测试中...';
            
            try {
                const response = await fetch('api/test_config.php');
                const data = await response.json();
                
                if (data.success) {
                    result.innerHTML = '<span class="success">✓ API配置正常</span><br><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                } else {
                    result.innerHTML = '<span class="error">✗ API配置失败</span><br>' + data.message;
                }
            } catch (error) {
                result.innerHTML = '<span class="error">✗ 请求失败: ' + error.message + '</span>';
            }
        }
        
        async function testNetwork() {
            const result = document.getElementById('networkResult');
            result.innerHTML = '测试中...';
            
            try {
                const response = await fetch('api/test_chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: '你好，这是一个测试消息',
                        session_id: 'test_' + Date.now()
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    result.innerHTML = '<span class="success">✓ DeepSeek API连接正常</span><br><strong>AI回复:</strong> ' + data.message;
                } else {
                    result.innerHTML = '<span class="error">✗ DeepSeek API连接失败</span><br>' + data.message;
                }
            } catch (error) {
                result.innerHTML = '<span class="error">✗ 网络请求失败: ' + error.message + '</span>';
            }
        }
    </script>
</body>
</html>
