-- =============================================================================
-- Affiliate Marketing System — Live Server SQL
-- =============================================================================
-- Run this once against the production database as an alternative to
-- `php artisan migrate` (this app's convention is to apply new schema directly
-- on the live server rather than running migrations there). This file mirrors
-- exactly what the Laravel migrations in
-- core/database/migrations/2026_08_23_0000*_*.php produce — dumped from a
-- migrated database via SHOW CREATE TABLE / SHOW COLUMNS for accuracy.
--
-- Safe to run once. Wrap in a transaction where your MySQL setup supports DDL
-- transactions, or take a backup first as a precaution.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. affiliate_settings — singleton config row (cash-on-pickup toggle, cookie
--    window, pending-order expiry, stockist pickup fee)
-- -----------------------------------------------------------------------------
CREATE TABLE `affiliate_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cash_on_pickup_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `paystack_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `cookie_days` int(10) unsigned NOT NULL DEFAULT 30,
  `stockist_pickup_fee_type` varchar(20) DEFAULT NULL,
  `stockist_pickup_fee_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `pending_order_expiry_days` int(10) unsigned NOT NULL DEFAULT 7,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `affiliate_settings`
  (`cash_on_pickup_enabled`, `paystack_enabled`, `cookie_days`, `stockist_pickup_fee_type`, `stockist_pickup_fee_value`, `pending_order_expiry_days`, `created_at`, `updated_at`)
VALUES
  (1, 1, 30, 'fixed', 0.00, 7, NOW(), NOW());

-- -----------------------------------------------------------------------------
-- 2. affiliate_clicks — funnel/impression tracking, one row per ?ref= click-through
-- -----------------------------------------------------------------------------
CREATE TABLE `affiliate_clicks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_user_id` bigint(20) unsigned NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer` varchar(500) DEFAULT NULL,
  `landing_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_clicks_affiliate_user_id_index` (`affiliate_user_id`),
  KEY `affiliate_clicks_session_token_index` (`session_token`),
  KEY `affiliate_clicks_created_at_index` (`created_at`),
  CONSTRAINT `affiliate_clicks_affiliate_user_id_foreign` FOREIGN KEY (`affiliate_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. affiliate_orders — the /shop sale record + invoice + redemption state
-- -----------------------------------------------------------------------------
CREATE TABLE `affiliate_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) NOT NULL,
  `affiliate_user_id` bigint(20) unsigned DEFAULT NULL,
  `affiliate_click_id` bigint(20) unsigned DEFAULT NULL,
  `buyer_name` varchar(191) NOT NULL,
  `buyer_email` varchar(191) NOT NULL,
  `buyer_phone` varchar(30) DEFAULT NULL,
  `state_id` bigint(20) unsigned NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `subtotal` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paystack_reference` varchar(100) DEFAULT NULL,
  `bonus_credited` tinyint(1) NOT NULL DEFAULT 0,
  `redeemed_by_stockist_id` bigint(20) unsigned DEFAULT NULL,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliate_orders_order_code_unique` (`order_code`),
  KEY `affiliate_orders_affiliate_click_id_foreign` (`affiliate_click_id`),
  KEY `affiliate_orders_redeemed_by_stockist_id_foreign` (`redeemed_by_stockist_id`),
  KEY `affiliate_orders_affiliate_user_id_status_index` (`affiliate_user_id`,`status`),
  KEY `affiliate_orders_status_index` (`status`),
  KEY `affiliate_orders_state_id_index` (`state_id`),
  KEY `affiliate_orders_paystack_reference_index` (`paystack_reference`),
  CONSTRAINT `affiliate_orders_affiliate_click_id_foreign` FOREIGN KEY (`affiliate_click_id`) REFERENCES `affiliate_clicks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `affiliate_orders_affiliate_user_id_foreign` FOREIGN KEY (`affiliate_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `affiliate_orders_redeemed_by_stockist_id_foreign` FOREIGN KEY (`redeemed_by_stockist_id`) REFERENCES `stockists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `affiliate_orders_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. affiliate_order_items — line items, snapshotted at time of sale
-- -----------------------------------------------------------------------------
CREATE TABLE `affiliate_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_name` varchar(191) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `line_total` decimal(15,2) NOT NULL,
  `bonus_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_order_items_affiliate_order_id_index` (`affiliate_order_id`),
  KEY `affiliate_order_items_product_id_index` (`product_id`),
  CONSTRAINT `affiliate_order_items_affiliate_order_id_foreign` FOREIGN KEY (`affiliate_order_id`) REFERENCES `affiliate_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliate_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. users — affiliate link code + affiliate bonus wallet
-- -----------------------------------------------------------------------------
ALTER TABLE `users`
  ADD COLUMN `affiliate_code` varchar(20) NULL DEFAULT NULL AFTER `id`,
  ADD COLUMN `affiliate_bonus` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `affiliate_code`,
  ADD UNIQUE KEY `users_affiliate_code_unique` (`affiliate_code`);

-- -----------------------------------------------------------------------------
-- 6. products — per-product affiliate bonus configuration
-- -----------------------------------------------------------------------------
ALTER TABLE `products`
  ADD COLUMN `affiliate_bonus_type` varchar(20) NULL DEFAULT NULL AFTER `bv`,
  ADD COLUMN `affiliate_bonus_value` decimal(15,2) NULL DEFAULT NULL AFTER `affiliate_bonus_type`;

-- =============================================================================
-- End of affiliate.sql
-- =============================================================================
