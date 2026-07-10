-- ============================================================
-- ALTER TABLE: matrices  –  Add PV tracking & position fields
-- Run on live database after deploying the code
-- ============================================================

ALTER TABLE `matrices`
    ADD COLUMN `pv_left`          DECIMAL(15, 2) NOT NULL DEFAULT '0.00' COMMENT 'Cumulative PV on left leg'          AFTER `right`,
    ADD COLUMN `pv_right`         DECIMAL(15, 2) NOT NULL DEFAULT '0.00' COMMENT 'Cumulative PV on right leg'         AFTER `pv_left`,
    ADD COLUMN `pv_left_pairing`  DECIMAL(15, 2) NOT NULL DEFAULT '0.00' COMMENT 'PV flushed/matched on left leg'     AFTER `pv_right`,
    ADD COLUMN `pv_right_pairing` DECIMAL(15, 2) NOT NULL DEFAULT '0.00' COMMENT 'PV flushed/matched on right leg'    AFTER `pv_left_pairing`,
    ADD COLUMN `position`         ENUM('left','right','root') NULL       COMMENT 'Node position in parent binary tree' AFTER `pv_right_pairing`;

ALTER TABLE `matrices`
    ADD INDEX `matrices_position_index` (`position`);

-- ── Verify ────────────────────────────────────────────────────
-- SHOW COLUMNS FROM matrices;
