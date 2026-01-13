-- ============================================
-- CRMEB 税费功能数据库迁移脚本
-- 创建日期: 2026-01-13
-- 说明: 添加日本消费税功能所需的配置和字段
-- ============================================

-- ============================================
-- 1. 添加系统配置项（税费功能配置）
-- ============================================

-- 税费功能开关
INSERT INTO `eb_system_config` (`menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `value`, `info`, `desc`, `sort`, `status`)
SELECT 'tax_enable', 'radio', 'input', 114, '0=>关闭\n1=>开启', '1', '税费功能开关', '是否启用税费计算和展示功能', 99, 1
WHERE NOT EXISTS (SELECT 1 FROM `eb_system_config` WHERE `menu_name` = 'tax_enable' LIMIT 1);

-- 全局税率配置
INSERT INTO `eb_system_config` (`menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `value`, `info`, `desc`, `sort`, `status`)
SELECT 'tax_rate_global', 'text', 'number', 114, '', '10', '全局税率', '统一税率百分比(如10代表10%),适用于所有商品', 98, 1
WHERE NOT EXISTS (SELECT 1 FROM `eb_system_config` WHERE `menu_name` = 'tax_rate_global' LIMIT 1);

-- 税费展示方式配置
INSERT INTO `eb_system_config` (`menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `value`, `info`, `desc`, `sort`, `status`)
SELECT 'tax_display_mode', 'radio', 'input', 114, '1=>内税(价格含税)\n2=>外税(价格不含税)', '1', '税费展示方式', '内税:显示价格已包含税费', 97, 1
WHERE NOT EXISTS (SELECT 1 FROM `eb_system_config` WHERE `menu_name` = 'tax_display_mode' LIMIT 1);

-- ============================================
-- 2. 订单表添加税费字段
-- ============================================

-- 兼容说明：
-- - MySQL 5.7 不支持 "ADD COLUMN IF NOT EXISTS"
-- - 使用 information_schema + 动态 SQL 实现幂等/可重复执行

-- 检查并添加订单总税额字段 tax_total
SET @tax_total_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_store_order'
    AND COLUMN_NAME = 'tax_total'
);
SET @sql := IF(
  @tax_total_exists = 0,
  'ALTER TABLE `eb_store_order` ADD COLUMN `tax_total` decimal(12,2) UNSIGNED NOT NULL DEFAULT ''0.00'' COMMENT ''订单总税额'' AFTER `pay_postage`',
  'SELECT ''skip: eb_store_order.tax_total already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加订单税率字段 tax_rate
SET @tax_rate_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_store_order'
    AND COLUMN_NAME = 'tax_rate'
);
SET @sql := IF(
  @tax_rate_exists = 0,
  'ALTER TABLE `eb_store_order` ADD COLUMN `tax_rate` decimal(5,2) NOT NULL DEFAULT ''0.00'' COMMENT ''订单税率(%)'' AFTER `tax_total`',
  'SELECT ''skip: eb_store_order.tax_rate already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 3. 订单商品明细表添加税费字段
-- ============================================

-- 检查并添加单品税额字段 tax_amount
SET @tax_amount_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_store_order_cart_info'
    AND COLUMN_NAME = 'tax_amount'
);
SET @sql := IF(
  @tax_amount_exists = 0,
  'ALTER TABLE `eb_store_order_cart_info` ADD COLUMN `tax_amount` decimal(12,2) NOT NULL DEFAULT ''0.00'' COMMENT ''单品税额(单价税额×数量)'' AFTER `cart_num`',
  'SELECT ''skip: eb_store_order_cart_info.tax_amount already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加单价税额字段 tax_unit_price
SET @tax_unit_price_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_store_order_cart_info'
    AND COLUMN_NAME = 'tax_unit_price'
);
SET @sql := IF(
  @tax_unit_price_exists = 0,
  'ALTER TABLE `eb_store_order_cart_info` ADD COLUMN `tax_unit_price` decimal(12,2) NOT NULL DEFAULT ''0.00'' COMMENT ''单价税额'' AFTER `tax_amount`',
  'SELECT ''skip: eb_store_order_cart_info.tax_unit_price already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 迁移完成
-- ============================================
