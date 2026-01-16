-- ============================================
-- CRMEB 用户表字段迁移脚本
-- 创建日期: 2026-01-14
-- 说明: 为邮箱验证码登录/注册补充字段（email、sex）并添加唯一索引
-- 兼容: MySQL 5.7/8.0（使用 information_schema + 动态 SQL，可重复执行）
-- ============================================

-- 1) 添加 email 字段
SET @email_col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_user'
    AND COLUMN_NAME = 'email'
);
SET @sql := IF(
  @email_col_exists = 0,
  'ALTER TABLE `eb_user` ADD COLUMN `email` varchar(128) NULL DEFAULT NULL COMMENT ''邮箱'' AFTER `phone`',
  'SELECT ''skip: eb_user.email already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) 添加 sex 字段
SET @sex_col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_user'
    AND COLUMN_NAME = 'sex'
);
SET @sql := IF(
  @sex_col_exists = 0,
  'ALTER TABLE `eb_user` ADD COLUMN `sex` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT ''性别(0未知1男2女)'' AFTER `email`',
  'SELECT ''skip: eb_user.sex already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) 添加 email 唯一索引（允许 NULL 多条）
SET @email_idx_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_user'
    AND INDEX_NAME = 'uniq_email'
);
SET @sql := IF(
  @email_idx_exists = 0,
  'ALTER TABLE `eb_user` ADD UNIQUE KEY `uniq_email` (`email`)',
  'SELECT ''skip: eb_user.uniq_email already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

