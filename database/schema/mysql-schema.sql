/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `delivery_zone` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_index` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `telegram_chat_id` varchar(255) DEFAULT NULL,
  `telegram_link_token` varchar(64) DEFAULT NULL,
  `telegram_linked_at` timestamp NULL DEFAULT NULL,
  `telegram_invited_at` timestamp NULL DEFAULT NULL,
  `telegram_notify_orders` tinyint(1) NOT NULL DEFAULT 1,
  `telegram_notify_payouts` tinyint(1) NOT NULL DEFAULT 1,
  `telegram_notify_sellers` tinyint(1) NOT NULL DEFAULT 1,
  `telegram_notify_reviews` tinyint(1) NOT NULL DEFAULT 1,
  `telegram_notify_deliveries` tinyint(1) NOT NULL DEFAULT 1,
  `telegram_notify_riders` tinyint(1) NOT NULL DEFAULT 1,
  `telegram_notify_system` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`),
  UNIQUE KEY `admins_telegram_chat_id_unique` (`telegram_chat_id`),
  UNIQUE KEY `admins_telegram_link_token_unique` (`telegram_link_token`),
  KEY `admins_email_index` (`email`),
  KEY `admins_role_id_index` (`role_id`),
  KEY `admins_is_active_index` (`is_active`),
  CONSTRAINT `admins_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_slug_unique` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `brands_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_slug` (`slug`),
  KEY `categories_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupon_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_usages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `discount_applied` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coupon_usages_user_id_foreign` (`user_id`),
  KEY `coupon_usages_order_id_foreign` (`order_id`),
  KEY `coupon_usages_coupon_id_user_id_index` (`coupon_id`,`user_id`),
  CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `max_uses` int(10) unsigned DEFAULT NULL,
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `max_uses_per_user` int(10) unsigned NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `coupons_product_id_foreign` (`product_id`),
  KEY `coupons_seller_id_is_active_index` (`seller_id`,`is_active`),
  KEY `coupons_expires_at_index` (`expires_at`),
  CONSTRAINT `coupons_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupons_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `rider_id` bigint(20) unsigned DEFAULT NULL,
  `assignment_method` varchar(255) DEFAULT NULL,
  `broadcast_id` bigint(20) unsigned DEFAULT NULL,
  `assignment_attempts` int(11) NOT NULL DEFAULT 0,
  `seller_id` bigint(20) unsigned NOT NULL,
  `bundle_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','assigned','picked_up','delivered','failed','cancelled') DEFAULT 'pending',
  `priority` int(11) NOT NULL DEFAULT 3,
  `pickup_address` text NOT NULL,
  `pickup_latitude` decimal(10,8) DEFAULT NULL,
  `pickup_longitude` decimal(11,8) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `delivery_latitude` decimal(10,8) DEFAULT NULL,
  `delivery_longitude` decimal(11,8) DEFAULT NULL,
  `package_weight` decimal(8,2) DEFAULT NULL,
  `package_notes` text DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `paid_to_rider_at` timestamp NULL DEFAULT NULL,
  `payout_batch_id` bigint(20) unsigned DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `failure_reason` varchar(255) DEFAULT NULL,
  `failure_notes` text DEFAULT NULL,
  `pickup_photo` varchar(255) DEFAULT NULL,
  `delivery_proof` varchar(255) DEFAULT NULL,
  `failure_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deliveries_order_id_foreign` (`order_id`),
  KEY `deliveries_rider_id_foreign` (`rider_id`),
  KEY `deliveries_seller_id_foreign` (`seller_id`),
  KEY `deliveries_status_rider_id_index` (`status`,`rider_id`),
  KEY `deliveries_payout_batch_id_foreign` (`payout_batch_id`),
  KEY `deliveries_broadcast_id_foreign` (`broadcast_id`),
  KEY `deliveries_assignment_method_index` (`assignment_method`),
  KEY `deliveries_priority_index` (`priority`),
  KEY `deliveries_bundle_id_foreign` (`bundle_id`),
  CONSTRAINT `deliveries_broadcast_id_foreign` FOREIGN KEY (`broadcast_id`) REFERENCES `delivery_broadcasts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deliveries_bundle_id_foreign` FOREIGN KEY (`bundle_id`) REFERENCES `delivery_bundles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliveries_payout_batch_id_foreign` FOREIGN KEY (`payout_batch_id`) REFERENCES `payout_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deliveries_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deliveries_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_broadcast_rider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_broadcast_rider` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_broadcast_id` bigint(20) unsigned NOT NULL,
  `rider_id` bigint(20) unsigned NOT NULL,
  `response` enum('pending','accepted','rejected','ignored') NOT NULL DEFAULT 'pending',
  `viewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_broadcast_rider_delivery_broadcast_id_foreign` (`delivery_broadcast_id`),
  KEY `delivery_broadcast_rider_rider_id_foreign` (`rider_id`),
  CONSTRAINT `delivery_broadcast_rider_delivery_broadcast_id_foreign` FOREIGN KEY (`delivery_broadcast_id`) REFERENCES `delivery_broadcasts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_broadcast_rider_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_broadcasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_broadcasts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_id` bigint(20) unsigned DEFAULT NULL,
  `bundle_id` bigint(20) unsigned DEFAULT NULL,
  `is_partial` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','accepted','expired') NOT NULL DEFAULT 'active',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `accepted_by_rider_id` bigint(20) unsigned DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_broadcasts_accepted_by_rider_id_foreign` (`accepted_by_rider_id`),
  KEY `delivery_broadcasts_status_expires_at_index` (`status`,`expires_at`),
  KEY `delivery_broadcasts_delivery_id_index` (`delivery_id`),
  KEY `delivery_broadcasts_bundle_id_foreign` (`bundle_id`),
  CONSTRAINT `delivery_broadcasts_accepted_by_rider_id_foreign` FOREIGN KEY (`accepted_by_rider_id`) REFERENCES `riders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `delivery_broadcasts_bundle_id_foreign` FOREIGN KEY (`bundle_id`) REFERENCES `delivery_bundles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `delivery_broadcasts_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_bundles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_bundles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `pickup_zone` varchar(255) NOT NULL,
  `status` enum('waiting','ready','partial','accepted','completed','growing') NOT NULL DEFAULT 'waiting',
  `expected_count` tinyint(3) unsigned NOT NULL,
  `ready_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `first_ready_at` timestamp NULL DEFAULT NULL,
  `broadcast_at` timestamp NULL DEFAULT NULL,
  `timeout_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_bundles_order_id_pickup_zone_index` (`order_id`,`pickup_zone`),
  KEY `delivery_bundles_status_timeout_at_index` (`status`,`timeout_at`),
  CONSTRAINT `delivery_bundles_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_items_delivery_id_foreign` (`delivery_id`),
  KEY `delivery_items_order_item_id_foreign` (`order_item_id`),
  CONSTRAINT `delivery_items_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_items_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_payouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_payouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rider_id` bigint(20) unsigned NOT NULL,
  `reference_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','paid','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `paid_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejected_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `deliveries_count` int(11) NOT NULL DEFAULT 0,
  `period_from` timestamp NULL DEFAULT NULL,
  `period_to` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_payouts_reference_number_unique` (`reference_number`),
  KEY `delivery_payouts_rider_id_index` (`rider_id`),
  KEY `delivery_payouts_status_index` (`status`),
  KEY `delivery_payouts_reference_number_index` (`reference_number`),
  KEY `delivery_payouts_requested_at_index` (`requested_at`),
  KEY `delivery_payouts_paid_at_index` (`paid_at`),
  KEY `delivery_payouts_approved_by_user_id_foreign` (`approved_by_user_id`),
  KEY `delivery_payouts_paid_by_user_id_foreign` (`paid_by_user_id`),
  KEY `delivery_payouts_rejected_by_user_id_foreign` (`rejected_by_user_id`),
  CONSTRAINT `delivery_payouts_approved_by_user_id_foreign` FOREIGN KEY (`approved_by_user_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `delivery_payouts_paid_by_user_id_foreign` FOREIGN KEY (`paid_by_user_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `delivery_payouts_rejected_by_user_id_foreign` FOREIGN KEY (`rejected_by_user_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `delivery_payouts_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_zones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pickup_zone` varchar(255) NOT NULL,
  `delivery_zone` varchar(255) NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_zones_pickup_zone_delivery_zone_index` (`pickup_zone`,`delivery_zone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_change_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `old_email` varchar(255) NOT NULL,
  `new_email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_change_requests_token_unique` (`token`),
  KEY `email_change_requests_token_index` (`token`),
  KEY `email_change_requests_user_id_confirmed_index` (`user_id`,`confirmed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `login_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'customer',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `successful` tinyint(1) NOT NULL DEFAULT 1,
  `failure_reason` varchar(100) DEFAULT NULL,
  `logged_in_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_activities_user_id_user_type_index` (`user_id`,`user_type`),
  KEY `login_activities_logged_in_at_index` (`logged_in_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `newsletter_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_subscriptions_email_unique` (`email`),
  UNIQUE KEY `newsletter_subscriptions_token_unique` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `seller_id` bigint(20) unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(255) DEFAULT NULL,
  `variant_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variant_options`)),
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `package_weight` decimal(8,2) DEFAULT NULL,
  `package_notes` text DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `expected_ready_by` date DEFAULT NULL COMMENT 'Date by which seller must mark this item ready.',
  `fulfillment_type` enum('in_stock','pre_order','made_to_order') NOT NULL DEFAULT 'in_stock',
  `status` enum('pending','processing','ready_for_pickup','picked_up','shipped','delivered','cancelled','delivery_failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_seller_id_status_index` (`seller_id`,`status`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nigeria',
  `shipping_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `est_delivery_days_min` smallint(5) unsigned DEFAULT NULL COMMENT 'Minimum estimated days from payment to doorstep.',
  `est_delivery_days_max` smallint(5) unsigned DEFAULT NULL COMMENT 'Maximum estimated days from payment to doorstep.',
  `has_preorder_items` tinyint(1) NOT NULL DEFAULT 0,
  `slowest_item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded','refund_pending','refund_rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paystack',
  `payment_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refund_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_payment_reference_unique` (`payment_reference`),
  KEY `orders_order_number_index` (`order_number`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_payment_reference_index` (`payment_reference`),
  KEY `orders_created_at_index` (`created_at`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payout_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payout_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_number` varchar(255) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `total_riders` int(11) NOT NULL,
  `total_deliveries` int(11) NOT NULL,
  `status` enum('pending','processing','completed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payout_batches_batch_number_unique` (`batch_number`),
  KEY `payout_batches_batch_number_index` (`batch_number`),
  KEY `payout_batches_status_index` (`status`),
  KEY `payout_batches_processed_by_foreign` (`processed_by`),
  CONSTRAINT `payout_batches_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payout_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payout_deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payout_id` bigint(20) unsigned NOT NULL,
  `delivery_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payout_deliveries_payout_id_delivery_id_unique` (`payout_id`,`delivery_id`),
  KEY `payout_deliveries_payout_id_index` (`payout_id`),
  KEY `payout_deliveries_delivery_id_index` (`delivery_id`),
  CONSTRAINT `payout_deliveries_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payout_deliveries_payout_id_foreign` FOREIGN KEY (`payout_id`) REFERENCES `delivery_payouts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payout_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payout_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rider_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','processing','completed','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payout_requests_approved_by_foreign` (`approved_by`),
  KEY `payout_requests_processed_by_foreign` (`processed_by`),
  KEY `payout_requests_rider_id_status_index` (`rider_id`,`status`),
  KEY `payout_requests_status_index` (`status`),
  CONSTRAINT `payout_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payout_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payout_requests_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `wallet_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `failure_reason` varchar(255) DEFAULT NULL,
  `payout_method` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payouts_seller_id_status_index` (`seller_id`,`status`),
  KEY `payouts_wallet_transaction_id_foreign` (`wallet_transaction_id`),
  CONSTRAINT `payouts_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payouts_wallet_transaction_id_foreign` FOREIGN KEY (`wallet_transaction_id`) REFERENCES `seller_wallet_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`),
  KEY `permissions_name_index` (`name`),
  KEY `permissions_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_primary` (`product_id`,`is_primary`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `variant_name` varchar(255) NOT NULL,
  `variant_value` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `price_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_index` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tags` text DEFAULT NULL,
  `search_keywords` text DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `use_cases` text DEFAULT NULL,
  `target_audience` varchar(255) DEFAULT NULL,
  `variants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variants`)),
  `search_vector` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `model_number` varchar(255) DEFAULT NULL,
  `condition` varchar(255) NOT NULL DEFAULT 'new',
  `fulfillment_type` enum('in_stock','pre_order','made_to_order') NOT NULL DEFAULT 'in_stock',
  `max_ready_days` smallint(5) unsigned DEFAULT NULL COMMENT 'Days after order for seller to mark ready. Null = in_stock.',
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `category_id` bigint(20) unsigned NOT NULL,
  `subcategory_id` bigint(20) unsigned DEFAULT NULL,
  `brand_id` bigint(20) unsigned DEFAULT NULL,
  `rating` double NOT NULL DEFAULT 0,
  `review_count` int(11) NOT NULL DEFAULT 0,
  `sold_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shop_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_is_active_is_featured_index` (`is_active`,`is_featured`),
  KEY `products_price_index` (`price`),
  KEY `products_category_id_index` (`category_id`),
  KEY `products_subcategory_id_index` (`subcategory_id`),
  KEY `idx_active_featured` (`is_active`,`is_featured`),
  KEY `idx_category_active` (`category_id`,`is_active`),
  KEY `idx_subcategory_active` (`subcategory_id`,`is_active`),
  KEY `idx_brand_active` (`brand_id`,`is_active`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_stock` (`stock`),
  KEY `idx_sale_price` (`sale_price`),
  KEY `idx_sold_count` (`sold_count`),
  KEY `idx_rating` (`rating`),
  KEY `idx_deals` (`sale_price`,`price`,`is_active`),
  KEY `products_name_index` (`name`),
  KEY `products_brand_id_index` (`brand_id`),
  KEY `products_shop_id_foreign` (`shop_id`),
  KEY `idx_products_target_audience` (`target_audience`),
  KEY `idx_products_condition` (`condition`),
  FULLTEXT KEY `fulltext_index` (`name`,`description`,`short_description`),
  FULLTEXT KEY `ft_product_search` (`name`,`description`,`short_description`),
  FULLTEXT KEY `ft_products_search_vector` (`search_vector`),
  FULLTEXT KEY `ft_products_name_desc` (`name`,`short_description`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `review_helpful_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `review_helpful_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `vote_type` enum('helpful','not_helpful') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `review_helpful_votes_review_id_user_id_unique` (`review_id`,`user_id`),
  KEY `review_helpful_votes_review_id_index` (`review_id`),
  KEY `review_helpful_votes_user_id_index` (`user_id`),
  CONSTRAINT `review_helpful_votes_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_helpful_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `review_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `review_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `review_images_review_id_index` (`review_id`),
  CONSTRAINT `review_images_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `seller_response` text DEFAULT NULL,
  `seller_responded_at` timestamp NULL DEFAULT NULL,
  `helpful_count` int(11) NOT NULL DEFAULT 0,
  `not_helpful_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_item_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_order_id_foreign` (`order_id`),
  KEY `reviews_product_id_is_approved_index` (`product_id`,`is_approved`),
  KEY `reviews_order_item_id_foreign` (`order_item_id`),
  CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `riders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `telegram_chat_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_link_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_linked_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vehicle_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('available','busy','offline') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline',
  `completed_deliveries` int(11) NOT NULL DEFAULT 0,
  `failed_deliveries` int(11) NOT NULL DEFAULT 0,
  `cancelled_deliveries` int(11) NOT NULL DEFAULT 0,
  `service_areas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `max_delivery_distance` decimal(8,2) NOT NULL DEFAULT 30.00,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `bank_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `riders_telegram_chat_id_unique` (`telegram_chat_id`),
  UNIQUE KEY `riders_telegram_link_token_unique` (`telegram_link_token`),
  KEY `riders_user_id_foreign` (`user_id`),
  KEY `riders_status_is_active_index` (`status`,`is_active`),
  CONSTRAINT `riders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  KEY `roles_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seller_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seller_analytics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `orders` int(11) NOT NULL DEFAULT 0,
  `revenue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `products_sold` int(11) NOT NULL DEFAULT 0,
  `avg_order_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_analytics_seller_id_date_unique` (`seller_id`,`date`),
  KEY `seller_analytics_seller_id_date_index` (`seller_id`,`date`),
  CONSTRAINT `seller_analytics_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seller_payout_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seller_payout_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `minimum_payout` decimal(10,2) NOT NULL DEFAULT 10.00,
  `preferred_method` varchar(255) NOT NULL DEFAULT 'bank_transfer',
  `payout_schedule` enum('manual','weekly','biweekly','monthly') NOT NULL DEFAULT 'manual',
  `payout_day` int(11) DEFAULT NULL,
  `auto_payout_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `auto_payout_threshold` decimal(10,2) DEFAULT NULL,
  `hold_period_days` int(11) NOT NULL DEFAULT 7,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_payout_settings_seller_id_unique` (`seller_id`),
  CONSTRAINT `seller_payout_settings_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seller_wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seller_wallet_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `wallet_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `transactable_type` varchar(255) DEFAULT NULL,
  `transactable_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_wallet_transactions_transaction_id_unique` (`transaction_id`),
  KEY `wallet_transactable_idx` (`transactable_type`,`transactable_id`),
  KEY `seller_wallet_transactions_seller_id_created_at_index` (`seller_id`,`created_at`),
  KEY `seller_wallet_transactions_wallet_id_type_index` (`wallet_id`,`type`),
  KEY `seller_wallet_transactions_transactable_type_index` (`transactable_type`),
  KEY `seller_wallet_transactions_status_index` (`status`),
  CONSTRAINT `seller_wallet_transactions_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_wallet_transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `seller_wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seller_wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seller_wallets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `pending_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_earned` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_withdrawn` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reserved_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `last_transaction_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_wallets_seller_id_unique` (`seller_id`),
  KEY `seller_wallets_seller_id_index` (`seller_id`),
  CONSTRAINT `seller_wallets_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sellers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sellers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `business_type` enum('individual','company','partnership') NOT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) NOT NULL,
  `bank_account` varchar(50) NOT NULL,
  `account_holder_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `telegram_chat_id` varchar(255) DEFAULT NULL,
  `telegram_link_token` varchar(64) DEFAULT NULL,
  `telegram_linked_at` timestamp NULL DEFAULT NULL,
  `telegram_notifications_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `verification_status` enum('pending','verified','rejected','suspended') NOT NULL DEFAULT 'pending',
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 10.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_id` (`tax_id`),
  UNIQUE KEY `sellers_telegram_chat_id_unique` (`telegram_chat_id`),
  UNIQUE KEY `sellers_telegram_link_token_unique` (`telegram_link_token`),
  KEY `fk_sellers_user` (`user_id`),
  CONSTRAINT `fk_sellers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shops` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned DEFAULT NULL,
  `shop_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `shop_description` text DEFAULT NULL,
  `shop_logo` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `website` varchar(500) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `delivery_zone` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `reviews_count` int(10) unsigned NOT NULL DEFAULT 0,
  `followers_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(50) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `total_products` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shops_slug_unique` (`slug`),
  KEY `shops_seller_id_is_active_index` (`seller_id`,`is_active`),
  CONSTRAINT `shops_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcategories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subcategories_slug_unique` (`slug`),
  KEY `subcategories_category_id_is_featured_index` (`category_id`,`is_featured`),
  KEY `idx_category_featured` (`category_id`,`is_featured`),
  KEY `idx_slug` (`slug`),
  KEY `idx_sort_order` (`sort_order`),
  CONSTRAINT `subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `user_type` enum('admin','customer','seller','rider') NOT NULL DEFAULT 'customer',
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `reactivation_deadline` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  UNIQUE KEY `users_facebook_id_unique` (`facebook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_12_08_090227_create_categories_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_12_08_090237_create_brands_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_12_08_090250_create_products_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_12_08_090331_create_product_images_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_12_08_091954_create_subcategories_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_12_08_092100_add_subcategory_id_to_products_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_12_08_215627_add_indexes_to_products_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_12_11_215423_add_two_factor_columns_to_users_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2024_01_01_create_fulltext_indexes',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2024_01_01_000001_create_sellers_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2024_01_01_000002_create_shops_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_12_18_235938_create_orders_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_12_19_070838_add_shop_id_to_products_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'[timestamp]_add_notifiable_to_notifications_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'[timestamp]_create_addresses_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_12_29_004222_create_orders_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_12_29_005629_update_order_item',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_01_04_011105_remove_images_column_from_products_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_01_06_115718_create_riders_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_01_06_115727_create_deliveries_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_01_06_120644_create_delivery_items_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_01_07_062648_add_user_id_to_notifications_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_01_07_151738_create_payout_requests_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_01_07_151912_create_payout_batches_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_01_07_152017_add_bank_details_to_riders',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_01_07_151637_add_payout_fields_to_deliveries',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_01_12_213734_create_seller_payout',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_01_13_065155_advanced_rider_assignment',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_01_13_065626_rider_assign',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_01_13_215510_create_roles_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_01_13_215512_create_admin_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_01_13_215611_create_permission_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_01_20_152444_add_soft_deletes_to_the_users_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_01_24_011848_add_sort_order_to_categories_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_01_26_233012_simplify_rider_company_system',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_01_27_071331_phase_3',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_01_27_074500_add_users_type',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_01_27_084653_delivery_payout',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_01_28_003444_fix_payout',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_01_29_070510_fix_delivery_payout_foreign_keys',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_01_29_090403_fix_batches_payout_foreign_keys',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_01_29_105620_fix_payout_coloumn',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_01_29_135512_order_item_id',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_01_30_005724_create_review_images_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_01_30_005857_create_review_helpful_votes_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_02_18_052412_create_delivery_zone',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_02_18_052714_create_delivery_zone',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_02_18_053023_create_delivery_zone',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_02_18_054412_create_deliverytoshop',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_02_18_055805_create_delivery_to_address',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_02_18_060555_create_delivery_to_order',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_02_22_085634_create_delivery_bundle',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_03_07_140638_social',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_03_09_201755_login_activities',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_03_09_201937_email_change_requests',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_03_10_215127_coupon_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_03_11_050704_add_refund_to_order',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_03_12_125203_add_search_colomn_to_product',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_03_19_115744_add_telegram_to_riders_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_03_22_225548_add_refund_to_order',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_03_25_192309_rider_pivot',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_03_27_204637_status_to_order',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_04_12_081837_fulfillment_to_product',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_04_12_081925_fufillment_to_order_item',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_04_12_081958_delivery_estimate_to_orders',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_04_13_161014_create_newsletter_subscriptions_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_04_18_095018_add_telegram_to_seller',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_04_18_095044_add_telegram_to_admin',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_05_14_000001_add_delivery_rider_admin_telegram_preferences',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_05_24_000001_sync_seller_payment_schema',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_05_24_000002_normalize_seller_commission_rate',57);
