-- =============================================================================
-- Stockist Inventory Accounting — Live Server SQL
-- =============================================================================
-- Run this once against the production database as an alternative to
-- `php artisan migrate` (this app's convention is to apply new schema directly
-- on the live server rather than running migrations there). This file mirrors
-- exactly what the Laravel migrations
-- core/database/migrations/2026_08_25_000001_create_stockist_redemptions_table.php
-- and 2026_08_25_000002_add_selling_price_to_products_table.php produce.
--
-- Safe to run once. Wrap in a transaction where your MySQL setup supports DDL
-- transactions, or take a backup first as a precaution.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. selling_price — public /shop price, independent of `price` (which still
--    drives commissions/PV/state pricing). Falls back to `price` when null via
--    Product::getShopPriceAttribute(). Existing rows are backfilled below.
--
--    NOTE: this column was found to already exist (and be fully backfilled)
--    on at least one environment's `products` table, applied directly and
--    never captured in a migration until now. Check first —
--    `SHOW COLUMNS FROM products LIKE 'selling_price';` — and skip the ALTER
--    below if it's already there; the UPDATE is always safe to run either way.
-- -----------------------------------------------------------------------------
ALTER TABLE `products`
  ADD COLUMN `selling_price` decimal(28,8) DEFAULT NULL AFTER `price`;

UPDATE `products` SET `selling_price` = `price` WHERE `selling_price` IS NULL;

-- -----------------------------------------------------------------------------
-- 2. stockist_redemptions — unified accounting ledger. Written alongside (not
--    instead of) the existing per-channel records (invoice_redemptions,
--    welcome_packages, affiliate_orders, sktransactions/stransactions) so
--    reporting has one table to sum across all four redemption channels:
--    cash, invoice_code, welcome_pack, affiliate_invoice.
-- -----------------------------------------------------------------------------
CREATE TABLE `stockist_redemptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stockist_id` bigint(20) unsigned NOT NULL,
  `type` varchar(30) NOT NULL,
  `trx` varchar(64) DEFAULT NULL,
  `reference_code` varchar(64) DEFAULT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `welcome_package_id` bigint(20) unsigned DEFAULT NULL,
  `affiliate_order_id` bigint(20) unsigned DEFAULT NULL,
  `customer_user_id` bigint(20) unsigned DEFAULT NULL,
  `buyer_name` varchar(191) DEFAULT NULL,
  `items` longtext DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stockist_redemptions_stockist_id_type_index` (`stockist_id`, `type`),
  KEY `stockist_redemptions_type_index` (`type`),
  KEY `stockist_redemptions_invoice_id_foreign` (`invoice_id`),
  KEY `stockist_redemptions_welcome_package_id_foreign` (`welcome_package_id`),
  KEY `stockist_redemptions_affiliate_order_id_foreign` (`affiliate_order_id`),
  KEY `stockist_redemptions_customer_user_id_foreign` (`customer_user_id`),
  CONSTRAINT `stockist_redemptions_stockist_id_foreign` FOREIGN KEY (`stockist_id`) REFERENCES `stockists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stockist_redemptions_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stockist_redemptions_welcome_package_id_foreign` FOREIGN KEY (`welcome_package_id`) REFERENCES `welcome_packages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stockist_redemptions_affiliate_order_id_foreign` FOREIGN KEY (`affiliate_order_id`) REFERENCES `affiliate_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stockist_redemptions_customer_user_id_foreign` FOREIGN KEY (`customer_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
