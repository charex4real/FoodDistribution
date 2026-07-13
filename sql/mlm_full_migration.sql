-- ============================================================
-- FULL MLM SCHEMA MIGRATION
-- Covers all binary / MLM / unilevel / awards / ACB changes.
-- Safe to paste into phpMyAdmin and run on a live database —
-- every statement is guarded so it can be run more than once
-- without errors or data loss.
--
-- Generated : 2026-07-03
-- Updated   : 2026-07-13
-- ============================================================
-- EXECUTION ORDER
--   STEP  1 — Create `projects` table
--   STEP  2 — Add all MLM columns to `users`
--   STEP  3 — Widen decimal columns on `projects`
--   STEP  4 — Add PV / position fields to `matrices`
--   STEP  5 — Create `awards` table
--   STEP  6 — Create `user_awards` table
--   STEP  7 — Create `transfers` table
--   STEP  8 — Create `unilevel_generations` table
--   STEP  9 — Create `project_unilevel_generation` pivot
--   STEP 10 — Add `prb` column to `products`
--   STEP 11 — Create `repurchase_pvs` table
--   STEP 12 — Create `repurchase_awards` table
--   STEP 13 — Create `repurchase_award_credits` table
--   STEP 14 — Add `acb_processed` to `repurchase_award_credits`
--   STEP 15 — Indexes and foreign keys
--   STEP 16 — Create `pv_logs` table
--   STEP 17 — Create `bv_logs` table
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

    `amount`               DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Activation cost / project price',
    `direct_commission`    DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Direct referral commission amount',
    `indirect_commission`  DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Indirect / binary spill-over commission amount',
    `pv`                   DECIMAL(10, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Point Value awarded on activation',
    `pairing_per_day`      DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Max pairing bonus paid per day (0 = unlimited)',
    `cash_back`            DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Cash-back on activation (flat amount)',
    `monthly_maintenance`  DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Monthly maintenance fee (0 = none)',
    `upgrade_bonus`        DECIMAL(5, 2)   NOT NULL DEFAULT '0.00'  COMMENT '% bonus paid to sponsor when member upgrades',
    `unilevel_bonus`       DECIMAL(15, 2)  NOT NULL DEFAULT '0.00'  COMMENT 'Unilevel pool bonus (flat amount)',

    `max_pairing_slots`    INT UNSIGNED    NOT NULL DEFAULT 0       COMMENT '0 = unlimited',
    `upgrade_allowed`      TINYINT(1)      NOT NULL DEFAULT 1       COMMENT 'Can members upgrade into this project',
    `is_default`           TINYINT(1)      NOT NULL DEFAULT 0       COMMENT 'Pre-selected during registration',
    `sort_order`           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `status`               TINYINT(1)      NOT NULL DEFAULT 1       COMMENT '1 = active',

    `created_at`           TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`           TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `projects_slug_unique` (`slug`),
    KEY `projects_status_index`       (`status`),
    KEY `projects_sort_order_index`   (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 2: Add all MLM columns to `users`
-- Each ALTER is wrapped so it is skipped if already applied.
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
    'ALTER TABLE `users` ADD COLUMN `direct_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime direct referral bonus'' AFTER `product_wallet`',
    'SELECT ''direct_bonus already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- indirect_bonus
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'indirect_bonus');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `indirect_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime indirect / binary bonus'' AFTER `direct_bonus`',
    'SELECT ''indirect_bonus already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- upgrade_bonus
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'upgrade_bonus');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `upgrade_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime upgrade bonus earned'' AFTER `indirect_bonus`',
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

-- awards  (wallet balance earned from binary PV awards)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'awards');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `awards` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime binary award wallet balance'' AFTER `pairing_bonus`',
    'SELECT ''awards already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- repurchase_award  (lifetime repurchase award balance)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'repurchase_award');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `repurchase_award` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime repurchase award balance'' AFTER `awards`',
    'SELECT ''repurchase_award already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- key_in_bonus  (bonus paid when another user keys-in under this account)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'key_in_bonus');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `key_in_bonus` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Lifetime key-in bonus earned'' AFTER `repurchase_award`',
    'SELECT ''key_in_bonus already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- acb  (Ambassador Cash Back — accumulated balance)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'acb');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `acb` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Ambassador Cash Back balance'' AFTER `key_in_bonus`',
    'SELECT ''acb already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ambassador  (flag — is this user an ambassador?)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'ambassador');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `ambassador` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 = user has ambassador status'' AFTER `acb`',
    'SELECT ''ambassador already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- keyed_in_by  (which user activated / keyed-in this account, FK to users)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'keyed_in_by');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `keyed_in_by` BIGINT UNSIGNED DEFAULT NULL COMMENT ''FK → users.id (who activated this account)'' AFTER `ambassador`',
    'SELECT ''keyed_in_by already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- last_monthly_purchase_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'last_monthly_purchase_at');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `last_monthly_purchase_at` TIMESTAMP NULL DEFAULT NULL COMMENT ''Last monthly reactivation timestamp'' AFTER `keyed_in_by`',
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


-- ──────────────────────────────────────────────────────────────
-- STEP 3: Widen decimal columns on `projects`
-- direct_commission, indirect_commission, cash_back, unilevel_bonus
-- were originally DECIMAL(5,2) (percentage). They now store flat
-- amounts, so they need DECIMAL(15,2). MODIFY COLUMN is a no-op
-- if the column is already the right size.
-- ──────────────────────────────────────────────────────────────

ALTER TABLE `projects`
    MODIFY COLUMN `direct_commission`   DECIMAL(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Direct referral commission (flat amount)',
    MODIFY COLUMN `indirect_commission` DECIMAL(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Indirect / binary commission (flat amount)',
    MODIFY COLUMN `cash_back`           DECIMAL(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Cash-back on activation (flat amount)',
    MODIFY COLUMN `unilevel_bonus`      DECIMAL(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Unilevel pool bonus (flat amount)';


-- ──────────────────────────────────────────────────────────────
-- STEP 4: Add PV and position fields to `matrices`
-- ──────────────────────────────────────────────────────────────

-- pv_left
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'matrices' AND COLUMN_NAME = 'pv_left');
SET @sql := IF(@col = 0,
    'ALTER TABLE `matrices` ADD COLUMN `pv_left` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Cumulative PV accumulated on the left leg'' AFTER `right`',
    'SELECT ''pv_left already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- pv_right
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'matrices' AND COLUMN_NAME = 'pv_right');
SET @sql := IF(@col = 0,
    'ALTER TABLE `matrices` ADD COLUMN `pv_right` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Cumulative PV accumulated on the right leg'' AFTER `pv_left`',
    'SELECT ''pv_right already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- pv_left_pairing
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'matrices' AND COLUMN_NAME = 'pv_left_pairing');
SET @sql := IF(@col = 0,
    'ALTER TABLE `matrices` ADD COLUMN `pv_left_pairing` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''PV flushed/matched on the left leg'' AFTER `pv_right`',
    'SELECT ''pv_left_pairing already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- pv_right_pairing
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'matrices' AND COLUMN_NAME = 'pv_right_pairing');
SET @sql := IF(@col = 0,
    'ALTER TABLE `matrices` ADD COLUMN `pv_right_pairing` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''PV flushed/matched on the right leg'' AFTER `pv_left_pairing`',
    'SELECT ''pv_right_pairing already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- position
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'matrices' AND COLUMN_NAME = 'position');
SET @sql := IF(@col = 0,
    'ALTER TABLE `matrices` ADD COLUMN `position` ENUM(''left'',''right'',''root'') NULL COMMENT ''Position of this node in its parent binary tree'' AFTER `pv_right_pairing`',
    'SELECT ''position already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ──────────────────────────────────────────────────────────────
-- STEP 5: Create `awards` table  (binary PV award tiers)
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `awards` (
    `id`                    BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`                  VARCHAR(150)     NOT NULL,
    `description`           TEXT             DEFAULT NULL,
    `required_total_pv`     DECIMAL(12,2)    NOT NULL DEFAULT '0.00',
    `required_left_pv`      DECIMAL(12,2)    NOT NULL DEFAULT '0.00',
    `required_right_pv`     DECIMAL(12,2)    NOT NULL DEFAULT '0.00',
    `prerequisite_award_id` BIGINT UNSIGNED  DEFAULT NULL COMMENT 'Award that must be earned first',
    `payment_amount`        DECIMAL(12,2)    NOT NULL DEFAULT '0.00',
    `image`                 VARCHAR(255)     DEFAULT NULL,
    `sort_order`            INT UNSIGNED     NOT NULL DEFAULT 0,
    `status`                TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`            TIMESTAMP        NULL DEFAULT NULL,
    `updated_at`            TIMESTAMP        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `awards_name_unique` (`name`),
    KEY `awards_sort_order_index`  (`sort_order`),
    CONSTRAINT `awards_prerequisite_award_id_foreign`
        FOREIGN KEY (`prerequisite_award_id`) REFERENCES `awards` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 6: Create `user_awards` table
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `user_awards` (
    `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED  NOT NULL,
    `award_id`    BIGINT UNSIGNED  NOT NULL,
    `status`      TINYINT(1)       NOT NULL DEFAULT 0  COMMENT '0=earned/unpaid, 1=paid',
    `earned_at`   TIMESTAMP        NULL DEFAULT NULL,
    `paid_at`     TIMESTAMP        NULL DEFAULT NULL,
    `paid_by`     BIGINT UNSIGNED  DEFAULT NULL         COMMENT 'Admin user id',
    `paid_amount` DECIMAL(12,2)    NOT NULL DEFAULT '0.00',
    `note`        TEXT             DEFAULT NULL,
    `created_at`  TIMESTAMP        NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `user_awards_user_id_award_id_unique` (`user_id`, `award_id`),
    KEY `user_awards_user_id_index`  (`user_id`),
    KEY `user_awards_award_id_index` (`award_id`),
    CONSTRAINT `user_awards_user_id_foreign`
        FOREIGN KEY (`user_id`)  REFERENCES `users`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `user_awards_award_id_foreign`
        FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 7: Create `transfers` table  (bonus → main wallet transfers)
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `transfers` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      BIGINT UNSIGNED NOT NULL,
    `source_field` VARCHAR(60)     NOT NULL  COMMENT 'Column name of the source wallet on users',
    `source_label` VARCHAR(100)    NOT NULL  COMMENT 'Human-readable wallet label',
    `amount`       DECIMAL(15,2)   NOT NULL DEFAULT '0.00',
    `status`       TINYINT(1)      NOT NULL DEFAULT 1  COMMENT '1=pending, 2=completed, 3=failed',
    `created_at`   TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`   TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `transfers_user_id_created_at_index` (`user_id`, `created_at`),
    CONSTRAINT `transfers_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 8: Create `unilevel_generations` table
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `unilevel_generations` (
    `id`          BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `number`      TINYINT UNSIGNED  NOT NULL  COMMENT 'Generation level 1–15',
    `title`       VARCHAR(100)      NOT NULL,
    `percentage`  DECIMAL(8,4)      NOT NULL DEFAULT '0.0000' COMMENT '% of PRB allocated to this generation',
    `description` TEXT              DEFAULT NULL,
    `status`      TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP         NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP         NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `unilevel_generations_number_unique` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 9: Create `project_unilevel_generation` pivot table
-- Links projects to the unilevel generations they support
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `project_unilevel_generation` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id`    BIGINT UNSIGNED NOT NULL,
    `generation_id` BIGINT UNSIGNED NOT NULL,
    `created_at`    TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `pug_project_generation_unique` (`project_id`, `generation_id`),
    KEY `pug_project_id_index`    (`project_id`),
    KEY `pug_generation_id_index` (`generation_id`),
    CONSTRAINT `pug_project_id_foreign`
        FOREIGN KEY (`project_id`)    REFERENCES `projects`              (`id`) ON DELETE CASCADE,
    CONSTRAINT `pug_generation_id_foreign`
        FOREIGN KEY (`generation_id`) REFERENCES `unilevel_generations`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 10: Add `prb` column to `products`
-- Product Repurchase Bonus — base amount used for unilevel distribution
-- ──────────────────────────────────────────────────────────────

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'prb');
SET @sql := IF(@col = 0,
    'ALTER TABLE `products` ADD COLUMN `prb` DECIMAL(15,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Product Repurchase Bonus base amount'' AFTER `price`',
    'SELECT ''prb already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ──────────────────────────────────────────────────────────────
-- STEP 11: Create `repurchase_pvs` table
-- Tracks cumulative repurchase PV per user (one row per user)
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `repurchase_pvs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `total_pv`   DECIMAL(15,2)   NOT NULL DEFAULT '0.00',
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `repurchase_pvs_user_id_unique` (`user_id`),
    CONSTRAINT `repurchase_pvs_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 12: Create `repurchase_awards` table  (repurchase award tiers)
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `repurchase_awards` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`       VARCHAR(255)    NOT NULL,
    `required_pv` DECIMAL(15,2)  NOT NULL,
    `amount`      DECIMAL(15,2)  NOT NULL,
    `status`      TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP      NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP      NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `repurchase_awards_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 13: Create `repurchase_award_credits` table
-- Records each time an admin credits a repurchase award to a user
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `repurchase_award_credits` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`             BIGINT UNSIGNED NOT NULL,
    `repurchase_award_id` BIGINT UNSIGNED NOT NULL,
    `amount`              DECIMAL(15,2)   NOT NULL,
    `paid_by`             BIGINT UNSIGNED NOT NULL COMMENT 'Admin user id',
    `paid_at`             TIMESTAMP       NOT NULL,
    `acb_processed`       TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '1 = ACB upline bonus already distributed',
    `created_at`          TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `rac_user_award_unique` (`user_id`, `repurchase_award_id`),
    KEY `rac_user_id_index`             (`user_id`),
    KEY `rac_repurchase_award_id_index` (`repurchase_award_id`),
    KEY `rac_acb_processed_index`       (`acb_processed`),
    CONSTRAINT `rac_user_id_foreign`
        FOREIGN KEY (`user_id`)             REFERENCES `users`             (`id`) ON DELETE CASCADE,
    CONSTRAINT `rac_repurchase_award_id_foreign`
        FOREIGN KEY (`repurchase_award_id`) REFERENCES `repurchase_awards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 14: Add `acb_processed` to `repurchase_award_credits`
-- (already included in STEP 13 above for fresh installs;
--  this guard catches servers that ran the table creation before
--  this column was added)
-- ──────────────────────────────────────────────────────────────

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'repurchase_award_credits' AND COLUMN_NAME = 'acb_processed');
SET @sql := IF(@col = 0,
    'ALTER TABLE `repurchase_award_credits` ADD COLUMN `acb_processed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 = ACB upline bonus already distributed'' AFTER `paid_at`',
    'SELECT ''acb_processed already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ──────────────────────────────────────────────────────────────
-- STEP 15: Indexes and foreign keys
-- All wrapped with existence checks for idempotency.
-- ──────────────────────────────────────────────────────────────

-- Index: users.project_id
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'users_project_id_index');
SET @sql := IF(@idx = 0,
    'ALTER TABLE `users` ADD INDEX `users_project_id_index` (`project_id`)',
    'SELECT ''users_project_id_index already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index: users.pcheck
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'users_pcheck_index');
SET @sql := IF(@idx = 0,
    'ALTER TABLE `users` ADD INDEX `users_pcheck_index` (`pcheck`)',
    'SELECT ''users_pcheck_index already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index: users.last_monthly_purchase_at
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'users_last_monthly_purchase_at_index');
SET @sql := IF(@idx = 0,
    'ALTER TABLE `users` ADD INDEX `users_last_monthly_purchase_at_index` (`last_monthly_purchase_at`)',
    'SELECT ''users_last_monthly_purchase_at_index already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index: users.ambassador
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'users_ambassador_index');
SET @sql := IF(@idx = 0,
    'ALTER TABLE `users` ADD INDEX `users_ambassador_index` (`ambassador`)',
    'SELECT ''users_ambassador_index already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index: matrices.position
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'matrices' AND INDEX_NAME = 'matrices_position_index');
SET @sql := IF(@idx = 0,
    'ALTER TABLE `matrices` ADD INDEX `matrices_position_index` (`position`)',
    'SELECT ''matrices_position_index already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK: users.project_id → projects.id
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
            AND CONSTRAINT_NAME = 'users_project_id_foreign');
SET @sql := IF(@fk = 0,
    'ALTER TABLE `users` ADD CONSTRAINT `users_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL',
    'SELECT ''FK users_project_id_foreign already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK: users.keyed_in_by → users.id
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
            AND CONSTRAINT_NAME = 'users_keyed_in_by_foreign');
SET @sql := IF(@fk = 0,
    'ALTER TABLE `users` ADD CONSTRAINT `users_keyed_in_by_foreign` FOREIGN KEY (`keyed_in_by`) REFERENCES `users` (`id`) ON DELETE SET NULL',
    'SELECT ''FK users_keyed_in_by_foreign already exists, skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ──────────────────────────────────────────────────────────────
-- STEP 16: Create `pv_logs` table
-- Tracks Point Value (PV) movements on left/right legs per user.
-- Used by PvLog model — no dedicated Laravel migration exists;
-- this table was created manually on the live server.
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `pv_logs` (
    `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED  NOT NULL,
    `position`   TINYINT(1)       NOT NULL DEFAULT 0   COMMENT '1=left, 2=right',
    `amount`     DECIMAL(15,2)    NOT NULL DEFAULT '0.00',
    `trx_type`   VARCHAR(1)       NOT NULL              COMMENT '+ credit, - debit',
    `details`    TEXT             DEFAULT NULL,
    `created_at` TIMESTAMP        NULL DEFAULT NULL,
    `updated_at` TIMESTAMP        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `pv_logs_user_id_index`            (`user_id`),
    KEY `pv_logs_position_trx_type_index`  (`position`, `trx_type`),
    KEY `pv_logs_user_id_position_index`   (`user_id`, `position`),
    CONSTRAINT `pv_logs_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- STEP 17: Create `bv_logs` table
-- Tracks Business Volume (BV) movements — used for matching/pairing
-- bonus calculations. Mirrors pv_logs structure.
-- Used by BvLog model — no dedicated Laravel migration exists;
-- this table was created manually on the live server.
-- ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `bv_logs` (
    `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED  NOT NULL,
    `position`   TINYINT(1)       NOT NULL DEFAULT 0   COMMENT '1=left, 2=right',
    `amount`     DECIMAL(15,2)    NOT NULL DEFAULT '0.00',
    `trx_type`   VARCHAR(1)       NOT NULL              COMMENT '+ credit, - debit/flush',
    `details`    TEXT             DEFAULT NULL,
    `created_at` TIMESTAMP        NULL DEFAULT NULL,
    `updated_at` TIMESTAMP        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `bv_logs_user_id_index`            (`user_id`),
    KEY `bv_logs_position_trx_type_index`  (`position`, `trx_type`),
    KEY `bv_logs_user_id_position_index`   (`user_id`, `position`),
    CONSTRAINT `bv_logs_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
-- Done. Verify with:
--   SHOW COLUMNS FROM projects;
--   SHOW COLUMNS FROM users     LIKE 'acb';
--   SHOW COLUMNS FROM users     LIKE 'award%';
--   SHOW COLUMNS FROM users     LIKE 'key_in_bonus';
--   SHOW COLUMNS FROM users     LIKE 'ambassador';
--   SHOW COLUMNS FROM users     LIKE 'keyed_in_by';
--   SHOW COLUMNS FROM matrices  LIKE 'pv%';
--   SHOW COLUMNS FROM matrices  LIKE 'position';
--   SHOW TABLES LIKE 'repurchase%';
--   SHOW TABLES LIKE 'unilevel%';
--   SHOW TABLES LIKE 'awards';
--   SHOW TABLES LIKE 'transfers';
--   SHOW TABLES LIKE '%v_logs';
-- ──────────────────────────────────────────────────────────────
