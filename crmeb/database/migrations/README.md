# 数据库迁移说明

本目录存放可重复执行（幂等）、兼容 MySQL 5.7/8.0 的迁移 SQL。

## 📦 迁移列表

- `20260113_add_tax_fields.sql`：税费功能（系统配置 + 订单/订单明细字段）
- `20260114_add_user_email_sex.sql`：用户字段扩展（`eb_user.email`、`eb_user.sex`）

---

## 20260113 税费功能

### 📋 迁移内容

1. 添加3个系统配置项（税费开关、税率10%、展示方式）
2. 订单表添加2个字段（tax_total, tax_rate）
3. 订单明细表添加2个字段（tax_amount, tax_unit_price）

---

### 🚀 Docker环境执行

#### 方式1：直接执行SQL（推荐）

```bash
# 在项目根目录执行
docker exec -i crmeb_mysql_arm mysql -uroot -p123456 crmeb < crmeb/database/migrations/20260113_add_tax_fields.sql
```

**说明**：
- `crmeb_mysql_arm` - MySQL容器名（根据实际情况修改）
- `123456` - MySQL密码（根据实际情况修改）
- `crmeb` - 数据库名（根据实际情况修改）

#### 方式2：进入容器执行

```bash
# 0. 拷贝 SQL 到 MySQL 容器
docker cp crmeb/database/migrations/20260113_add_tax_fields.sql crmeb_mysql_arm:/tmp/20260113_add_tax_fields.sql

# 1. 进入MySQL容器
docker exec -it crmeb_mysql_arm mysql -uroot -p123456 crmeb

# 2. 在MySQL命令行执行
source /tmp/20260113_add_tax_fields.sql;
```

---

### ✅ 验证迁移结果

```bash
# 运行验证脚本
php test_tax_calculation.php
```

**期望输出**：所有测试通过 ✅

---

### ⚠️ 注意事项

1. **备份数据库**（重要！）
   ```bash
   docker exec crmeb_mysql_arm mysqldump -uroot -p123456 crmeb > backup_$(date +%Y%m%d).sql
   ```

2. **表前缀**：SQL默认使用 `eb_` 前缀，如不同需手动替换

3. **幂等性/兼容性**：SQL支持重复执行；兼容 MySQL 5.7/8.0（不依赖 `ADD COLUMN IF NOT EXISTS`）

4. **清除缓存**（迁移后）
   ```bash
   rm -rf crmeb/runtime/temp/*.php
   docker exec crmeb_redis redis-cli -a 123456 DEL system_config_all
   ```

---

### 📞 问题排查

如遇问题：
1. 检查容器名和密码是否正确
2. 确认数据库名和表前缀
3. 运行验证脚本：`php test_tax_calculation.php`

---

**创建日期**: 2026-01-13
**版本**: v1.0

---

## 20260114 用户字段扩展（email/sex）

### 📋 迁移内容

1. `eb_user` 增加 `email` 字段（允许 `NULL`，并加唯一索引 `uniq_email`）
2. `eb_user` 增加 `sex` 字段（0/1/2：保密/男/女）

### 🚀 Docker环境执行（推荐）

```bash
docker exec -i crmeb_mysql_arm mysql -uroot -p123456 crmeb < crmeb/database/migrations/20260114_add_user_email_sex.sql
```

### ✅ 验证迁移结果

```sql
SHOW COLUMNS FROM eb_user LIKE 'email';
SHOW COLUMNS FROM eb_user LIKE 'sex';
SHOW INDEX FROM eb_user WHERE Key_name = 'uniq_email';
```
