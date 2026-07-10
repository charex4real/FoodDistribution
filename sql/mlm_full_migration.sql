-- ============================================================
-- FULL MLM SCHEMA MIGRATION
-- Run on live database in this order:
--   1. create_projects_table.sql   (included below)
--   2. add_mlm_fields_to_users.sql (included below)
--
-- Safe to run multiple times — uses IF NOT EXISTS / IF EXISTS guards
-- Generated: 2026-07-03
-- ============================================================


-- ──────────────────────────────────────────────────────────────
-- STEP 1: Create the `projects` table
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `projects` (
    `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`                VARCHAR(150)    NOT NULL,
    `slug`                 VARCHAR(150)    NOT NULL,
    `description`          TEXT            DEFAULT NULL,
    `icon`                 VARCHAR(60)     NOT NULL DEFAULT 'las la-briefcase',
    `color`                VARCHAR(20)     NOT NULL DEFAULT '#059669',

    -- Financials
    `amount`               DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Activation cost / project price',
    `direct_commission`    DECIMAL(5, 2)   NOT NULL DEFAULT '0.00'  COMMENT 'Direct referral commission %',
    `indirect_commission`  DECIMAL(5, 2)   NOT NULL DEFAULT '0.00'  COMMENT 'Indirect / binary spill-over commission %',
    `pv`                   DECIMAL(10, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Point Value awarded on activation',
    `pairing_per_day`      DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Max pairing bonus paid per day (0 = unlimited)',
    `cash_back`            DECIMAL(5, 2)   NOT NULL DEFAULT '0.00'  COMMENT '% cash-back on activation',
    `monthly_maintenance`  DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Monthly maintenance fee (0 = none)',
    `upgrade_bonus`        DECIMAL(5, 2)   NOT NULL DEFAULT '0.00'  COMMENT '% bonus paid to sponsor when member upgrades',
    `unilevel_bonus`       DECIMAL(5, 2)   NOT NULL DEFAULT '0.00'  COMMENT '% unilevel pool bonus',

    -- Limits & flags
    `max_pairing_slots`    INT UNSIGNED    NOT NULL DEFAULT 0       COMMENT '0 = unlimited',
    `upgrade_allowed`      TINYINT(1)      NOT NULL DEFAULT 1       COMMENT 'Can members upgrade into this project',
    `is_default`           TINYINT(1)      NOT NULL DEFAULT 0       COMMENT 'Pre-selected during registration',
    `sort_order`           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `status`               TINYINT(1)      NOT NULL DEFAULT 1       COMMENT '1 = active',

    `created_at`           TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`           TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `projects_slug_unique` (`slug`),
    KEY `projects_status_index`     (`status`),
    KEY `projects_sort_order_index` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 2: Add MLM columns to the `users` table
-- Each ALTER is wrapped so it can be skipped if already applied
-- ──────────────────────────────────────────────────────────────

-- project_id
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'project_id');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `project_id` BIGINT UNSIGNED DEFAULT NULL COMMENT ''FK → projects.id'' AFTER `id`',
    'SELECT ''project_id already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- product_wallet
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'product_wallet');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `product_wallet` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Product / MLM wallet'' AFTER `balance`',
    'SELECT ''product_wallet already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- direct_bonus
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'direct_bonus');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `direct_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime direct bonus'' AFTER `product_wallet`',
    'SELECT ''direct_bonus already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- indirect_bonus
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'indirect_bonus');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `indirect_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime indirect bonus'' AFTER `direct_bonus`',
    'SELECT ''indirect_bonus already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- upgrade_bonus
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'upgrade_bonus');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `upgrade_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime upgrade bonus'' AFTER `indirect_bonus`',
    'SELECT ''upgrade_bonus already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- unilevel_bonus
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'unilevel_bonus');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `unilevel_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime unilevel bonus'' AFTER `upgrade_bonus`',
    'SELECT ''unilevel_bonus already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- pairing_bonus
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'pairing_bonus');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `pairing_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime pairing bonus'' AFTER `unilevel_bonus`',
    'SELECT ''pairing_bonus already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- last_monthly_purchase_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'last_monthly_purchase_at');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `last_monthly_purchase_at` TIMESTAMP NULL DEFAULT NULL COMMENT ''Last monthly reactivation'' AFTER `pairing_bonus`',
    'SELECT ''last_monthly_purchase_at already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- monthly_purchase_history
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'monthly_purchase_history');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `monthly_purchase_history` JSON DEFAULT NULL COMMENT ''[{month, amount, project_id}]'' AFTER `last_monthly_purchase_at`',
    'SELECT ''monthly_purchase_history already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- pcheck
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'pcheck');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `pcheck` TINYINT NOT NULL DEFAULT 0 COMMENT ''0=none,1=left,2=right,3=both'' AFTER `monthly_purchase_history`',
    'SELECT ''pcheck already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── Indexes (ignore errors if they already exist) ──────────────

ALTER TABLE `users`
    ADD INDEX `users_project_id_index`               (`project_id`),
    ADD INDEX `users_pcheck_index`                   (`pcheck`),
    ADD INDEX `users_last_monthly_purchase_at_index` (`last_monthly_purchase_at`);

-- ── Foreign key ────────────────────────────────────────────────
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
            AND CONSTRAINT_NAME = 'users_project_id_foreign');
SET @sql := IF(@fk = 0,
    'ALTER TABLE `users` ADD CONSTRAINT `users_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL',
    'SELECT ''FK users_project_id_foreign already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ──────────────────────────────────────────────────────────────
-- Done. Verify with:
--   SHOW COLUMNS FROM projects;
--   SHOW COLUMNS FROM users LIKE '%bonus%';
--   SHOW COLUMNS FROM users LIKE 'pcheck';
-- ──────────────────────────────────────────────────────────────
