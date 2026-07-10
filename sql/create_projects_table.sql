-- ============================================================
-- CREATE TABLE: projects
-- Binary MLM subscription plans / activation packages
-- Run this BEFORE add_mlm_fields_to_users.sql
-- ============================================================

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
