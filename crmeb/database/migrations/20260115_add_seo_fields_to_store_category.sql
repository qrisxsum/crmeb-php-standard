-- ============================================
-- CRMEB 分类SEO功能数据库迁移脚本
-- 创建日期: 2026-01-15
-- 说明: 为商品分类表添加SEO优化字段
-- ============================================

-- ============================================
-- 1. 商品分类表添加SEO字段
-- ============================================

-- 检查并添加SEO标题字段 seo_title
SET @seo_title_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_store_category'
    AND COLUMN_NAME = 'seo_title'
);
SET @sql := IF(
  @seo_title_exists = 0,
  'ALTER TABLE `eb_store_category` ADD COLUMN `seo_title` varchar(255) NOT NULL DEFAULT '''' COMMENT ''SEO标题'' AFTER `big_pic`',
  'SELECT ''skip: eb_store_category.seo_title already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加SEO关键词字段 seo_keywords
SET @seo_keywords_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_store_category'
    AND COLUMN_NAME = 'seo_keywords'
);
SET @sql := IF(
  @seo_keywords_exists = 0,
  'ALTER TABLE `eb_store_category` ADD COLUMN `seo_keywords` varchar(255) NOT NULL DEFAULT '''' COMMENT ''SEO关键词'' AFTER `seo_title`',
  'SELECT ''skip: eb_store_category.seo_keywords already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加SEO描述字段 seo_description
SET @seo_description_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eb_store_category'
    AND COLUMN_NAME = 'seo_description'
);
SET @sql := IF(
  @seo_description_exists = 0,
  'ALTER TABLE `eb_store_category` ADD COLUMN `seo_description` text COMMENT ''SEO描述'' AFTER `seo_keywords`',
  'SELECT ''skip: eb_store_category.seo_description already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 迁移完成
-- ============================================
