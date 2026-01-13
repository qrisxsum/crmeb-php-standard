# CRMEB Windows 一键部署指南

## 前置要求

- **Docker Desktop** 已安装并运行
- Git 已安装

---

## 部署步骤

### 1. 克隆代码

```powershell
git clone <你的GitHub仓库地址>
cd crmeb-php
```

### 2. 创建环境配置文件

```powershell
# 复制环境配置模板
copy crmeb\.env.example crmeb\.env
```

### 3. 创建安装模板文件

在 `crmeb/public/install/` 目录下创建 `.env` 文件，内容如下：

```ini
APP_DEBUG = false

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = #DB_HOST#
HOSTPORT = #DB_PORT#
USERNAME = #DB_USER#
PASSWORD = #DB_PWD#
DATABASE = #DB_NAME#
PREFIX = #DB_PREFIX#
CHARSET = utf8mb4
DEBUG = true

[LANG]
default_lang = zh-cn

[CACHE]
DRIVER = #CACHE_TYPE#
CACHE_PREFIX = #CACHE_PREFIX#
CACHE_TAG_PREFIX = #CACHE_TAG_PREFIX#

[REDIS]
REDIS_HOSTNAME = #RB_HOST#
PORT = #RB_PORT#
REDIS_PASSWORD = #RB_PWD#
SELECT = #RB_SELECT#

[QUEUE]
QUEUE_NAME = #QUEUE_NAME#
```

### 4. 启动容器

```powershell
cd docker-compose\window
docker-compose up -d --build
```

> ⏱️ 首次构建需要 3-5 分钟（需要下载镜像和复制 vendor 目录）

### 5. 验证容器状态

```powershell
docker-compose ps
```

确认 4 个容器都是 `Up` 状态：
- crmeb_mysql
- crmeb_redis
- crmeb_php
- crmeb_nginx

### 6. 设置文件权限

```powershell
docker exec crmeb_php sh -c "chmod 777 /var/www/.env /var/www/.version /var/www/.constant && chmod -R 777 /var/www/runtime /var/www/backup /var/www/public"
```

### 7. 访问安装向导

浏览器打开：**http://localhost:8011/**

填写数据库配置：

| 配置项 | 值 |
|--------|-----|
| MySQL Host | `192.168.10.11` |
| MySQL Port | `3306` |
| MySQL 用户名 | `root` |
| MySQL 密码 | `123456` |
| 数据库名 | `crmeb` |
| Redis Host | `192.168.10.10` |
| Redis Port | `6379` |
| Redis 密码 | `123456` |

### 8. 启动后台服务

安装完成后执行：

```powershell
docker exec crmeb_php sh -c "cd /var/www && php think workerman start --d && php think timer start --d && nohup php think queue:listen --queue > /dev/null 2>&1 &"
```

### 9. 删除安装文件（安装完成后）

```powershell
docker exec crmeb_php rm -rf /var/www/public/install
```

---

## 访问地址

| 端 | URL |
|----|-----|
| 前台商城 | http://localhost:8011/ |
| 管理后台 | http://localhost:8011/admin/ |

---

## 常用命令

```powershell
# 查看容器状态
docker-compose ps

# 查看日志
docker-compose logs -f

# 重启容器
docker-compose restart

# 停止容器
docker-compose stop

# 停止并删除容器
docker-compose down

# 进入PHP容器
docker exec -it crmeb_php /bin/bash

# 重新启动后台服务（容器重启后需要执行）
docker exec crmeb_php sh -c "cd /var/www && php think workerman start --d && php think timer start --d && nohup php think queue:listen --queue > /dev/null 2>&1 &"
```

---

## 常见问题

### 端口被占用

修改 `docker-compose/window/docker-compose.yml` 中的端口映射：
```yaml
nginx:
  ports:
    - 8012:80  # 将 8011 改为其他端口
```

### 容器启动失败

```powershell
# 查看具体错误
docker-compose logs crmeb_php
docker-compose logs crmeb_mysql

# 重新构建
docker-compose down
docker-compose up -d --build
```

### 缺少必要的安装文件

确保 `crmeb/public/install/.env` 文件存在，如不存在请按照步骤 3 手动创建。

---

**更新时间：** 2026-01-06
