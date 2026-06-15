# GovCore 政务公开与应急指挥平台 (GovCore CMS)

## 🛠 技术栈
- Frontend: [Bootstrap 5, Native JS]
- Backend: [PHP 7.4 (Native)]
- Database: [MySQL 8.0]

## 🚀 启动指南 (How to Run)
1. 确保 Docker Desktop 已启动。
2. 在根目录执行：`docker compose up --build -d`
3. 等待容器启动完成...

## 🔗 服务地址 (Services)
- Web Frontend/Backend: http://localhost:3928
- Database: localhost:3306 (user: root / pass: root) (内部服务名: `db`)

## 🧪 测试账号
- Admin: admin / admin888

## 🛡️ 漏洞靶场说明 (Vulnerability Playground)
本项目为网络安全应急响应演练专用靶场，包含以下实战漏洞（请在封闭环境测试）：
1. **SQL 注入**: /index.php?keyword= (Union Injection, supports '--' comment)
2. **命令执行 (RCE)**: /admin/net_tool.php (Ping Input: `127.0.0.1 | whoami`)
3. **任意文件上传**: /admin/upload.php (Content-Type Bypass: application/pdf -> shell.php)
4. **反序列化 (RCE/File Write)**: /index.php (Cookie: user_config, triggers CacheManager Destruct)

---

## 🐳 Docker 镜像源配置 (Docker Registry Configuration)

### 推荐配置（基于实际项目验证）

#### 1. Docker 镜像源
**使用官方 Docker Hub 镜像**（已验证稳定可用）

```yaml
# docker-compose.yml 示例
services:
  db:
    image: mysql:8.0                    # MySQL 数据库
  
  web:
    build: ./backend
    # Dockerfile 中使用：
    # - PHP: php:7.4-apache (运行)
```

#### 2. Maven/Npm 依赖源
本项目主要基于原生 PHP + Apache，对于前端依赖使用了 CDN (Bootstrap 5)，无需配置 npm/maven 镜像源。
如果要在后续开发中引入 npm 构建流程，建议参考以下配置：

```dockerfile
RUN npm config set registry https://registry.npmmirror.com
```
