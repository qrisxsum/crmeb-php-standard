# Docker Compose（按操作系统）

本项目提供按平台划分的 `docker-compose` 配置，请根据你的运行环境进入对应目录启动：

- Mac（Apple Silicon / M 芯片）：`docker-compose/MacArm`
- Mac（Intel）：`docker-compose/MacIntel`
- Linux：`docker-compose/linux`
- Windows：`docker-compose/window`

## 启动步骤

以 MacIntel 为例：

```bash
cd ./docker-compose/MacIntel
docker-compose up -d
# 或：docker compose up -d
```

MacArm 可使用 `make run`（见 `docker-compose/MacArm/README.md`）。

## 运行依赖与目录结构

各平台配置会把项目代码挂载到容器中，默认使用 `../../crmeb`，因此请保持仓库目录结构不变：

- `crmeb/`：CRMEB 后端代码目录
- `docker-compose/<平台>/`：对应平台的 docker-compose 配置与 mysql/nginx/php/redis 配置

## 常用命令

进入 PHP 容器后可启动定时任务/长连接/队列（根据项目实际需要选择）：

```bash
docker exec -it crmeb_php /bin/bash
cd /var/www
php think timer start --d
php think workerman start --d
php think queue:listen --queue
```

## 访问与默认信息

- 后台地址：`http://localhost:8011/`
- MySQL：`192.168.10.11:3306`，用户 `root`，密码 `123456`，库 `crmeb`
- Redis：`192.168.10.10:6379`，密码 `123456`（DB `0`）

## 说明

为避免配置重复与误用，`docker-compose/` 根目录不再提供通用的 `docker-compose.yml` 与通用配置目录，请统一使用各平台子目录。
