-- ============================================================
-- ALTER TABLE: users  –  Add Binary MLM fields
-- Run AFTER create_projects_table.sql
-- ============================================================

-- project_id foreign key
ALTER TABLE `users`
    ADD COLUMN `project_id`               BIGINT UNSIGNED  DEFAULT NULL                COMMENT 'FK → projects.id'         AFTER `id`,
    ADD COLUMN `product_wallet`           DECIMAL(15, 2)   NOT NULL DEFAULT '0.00'     COMMENT 'Product / MLM wallet'     AFTER `balance`,
    ADD COLUMN `direct_bonus`             DECIMAL(15, 2)   NOT NULL DEFAULT '0.00'     COMMENT 'Lifetime direct bonus'    AFTER `product_wallet`,
    ADD COLUMN `indirect_bonus`           DECIMAL(15, 2)   NOT NULL DEFAULT '0.00'     COMMENT 'Lifetime indirect bonus'  AFTER `direct_bonus`,
    ADD COLUMN `upgrade_bonus`            DECIMAL(15, 2)   NOT NULL DEFAULT '0.00'     COMMENT 'Lifetime upgrade bonus'   AFTER `indirect_bonus`,
    ADD COLUMN `unilevel_bonus`           DECIMAL(15, 2)   NOT NULL DEFAULT '0.00'     COMMENT 'Lifetime unilevel bonus'  AFTER `upgrade_bonus`,
    ADD COLUMN `pairing_bonus`            DECIMAL(15, 2)   NOT NULL DEFAULT '0.00'     COMMENT 'Lifetime pairing bonus'   AFTER `unilevel_bonus`,
    ADD COLUMN `last_monthly_purchase_at` TIMESTAMP        NULL     DEFAULT NULL        COMMENT 'Last monthly reactivation' AFTER `pairing_bonus`,
    ADD COLUMN `monthly_purchase_history` JSON             DEFAULT  NULL                COMMENT '[{month, amount, project_id}]' AFTER `last_monthly_purchase_at`,
    ADD COLUMN `pcheck`                   TINYINT          NOT NULL DEFAULT 0           COMMENT '0=none,1=left,2=right,3=both' AFTER `monthly_purchase_history`;

-- Indexes
ALTER TABLE `users`
    ADD INDEX `users_project_id_index`               (`project_id`),
    ADD INDEX `users_pcheck_index`                   (`pcheck`),
    ADD INDEX `users_last_monthly_purchase_at_index` (`last_monthly_purchase_at`);

-- Foreign key (add last so indexes are already in place)
ALTER TABLE `users`
    ADD CONSTRAINT `users_project_id_foreign`
        FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;
