-- ============================================================
-- ALTER TABLE: projects
-- Widen direct_commission, indirect_commission, cash_back,
-- unilevel_bonus from decimal(5,2) → decimal(15,2)
-- Reason: these fields are now flat amounts, not percentages.
-- ============================================================

ALTER TABLE `projects`
    MODIFY COLUMN `direct_commission`   DECIMAL(15, 2) NOT NULL DEFAULT '0.00' COMMENT 'Direct commission flat amount',
    MODIFY COLUMN `indirect_commission` DECIMAL(15, 2) NOT NULL DEFAULT '0.00' COMMENT 'Indirect commission flat amount',
    MODIFY COLUMN `cash_back`           DECIMAL(15, 2) NOT NULL DEFAULT '0.00' COMMENT 'Cash-back flat amount',
    MODIFY COLUMN `unilevel_bonus`      DECIMAL(15, 2) NOT NULL DEFAULT '0.00' COMMENT 'Unilevel bonus flat amount';

-- SHOW COLUMNS FROM projects LIKE '%commission%';
