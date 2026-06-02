-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 28, 2026 at 07:18 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auto_delete_expired_sessions` (IN `p_max_lifetime_seconds` INT)   BEGIN
                DELETE FROM sessions
                WHERE last_activity < (UNIX_TIMESTAMP() - p_max_lifetime_seconds);
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auto_delete_notifications` (IN `p_user_id` INT, IN `p_days` INT)   BEGIN
                DELETE FROM notifications
                WHERE notifiable_id = p_user_id
                AND read_at IS NOT NULL
                AND created_at < DATE_SUB(NOW(), INTERVAL p_days DAY);
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auto_delete_notifications_wrapper` (IN `p_user_id` INT)   BEGIN
                DECLARE v_days INT DEFAULT 0;

                SELECT CAST(value AS UNSIGNED) INTO v_days
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = 'auto_delete_notifications_days'
                LIMIT 1;

                IF v_days > 0 THEN
                    CALL sp_auto_delete_notifications(p_user_id, v_days);
                END IF;
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auto_delete_orders` (IN `p_user_id` INT, IN `p_status` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_value` INT, IN `p_unit` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_is_seller` TINYINT)   BEGIN
                DECLARE v_threshold DATETIME;

                -- Calculate threshold date based on unit
                SET v_threshold = CASE
                    WHEN p_unit = 'minutes' THEN DATE_SUB(NOW(), INTERVAL p_value MINUTE)
                    WHEN p_unit = 'hours' THEN DATE_SUB(NOW(), INTERVAL p_value HOUR)
                    WHEN p_unit = 'days' THEN DATE_SUB(NOW(), INTERVAL p_value DAY)
                    WHEN p_unit = 'months' THEN DATE_SUB(NOW(), INTERVAL p_value MONTH)
                    WHEN p_unit = 'years' THEN DATE_SUB(NOW(), INTERVAL p_value YEAR)
                    ELSE DATE_SUB(NOW(), INTERVAL p_value MONTH)
                END;

                -- Create temporary table to store IDs and file paths for cleanup
                DROP TEMPORARY TABLE IF EXISTS tmp_orders_to_delete;
                CREATE TEMPORARY TABLE tmp_orders_to_delete (
                    order_id BIGINT UNSIGNED,
                    delivery_id BIGINT UNSIGNED,
                    delivery_pickup_image VARCHAR(255),
                    delivery_delivery_image VARCHAR(255)
                );

                -- Insert matching orders into temp table
                INSERT INTO tmp_orders_to_delete (order_id, delivery_id, delivery_pickup_image, delivery_delivery_image)
                SELECT
                    o.id,
                    od.id, od.pickup_image, od.delivery_image
                FROM orders o
                LEFT JOIN order_deliveries od ON o.id = od.order_id
                WHERE o.status = p_status
                AND o.updated_at < v_threshold
                AND (
                    (p_is_seller = 1 AND JSON_CONTAINS(o.items_json, JSON_OBJECT('seller_id', CAST(p_user_id AS UNSIGNED))))
                    OR (p_is_seller = 0 AND o.admin_id = p_user_id)
                );

                -- Return file paths so PHP can delete them from disk
                SELECT * FROM tmp_orders_to_delete;

                -- Delete related order_deliveries first (FK constraint)
                DELETE od FROM order_deliveries od
                INNER JOIN tmp_orders_to_delete t ON od.id = t.delivery_id
                WHERE t.delivery_id IS NOT NULL;

                -- Delete the orders themselves
                DELETE o FROM orders o
                INNER JOIN tmp_orders_to_delete t ON o.id = t.order_id;

                -- Cleanup temp table
                DROP TEMPORARY TABLE IF EXISTS tmp_orders_to_delete;
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auto_delete_orders_wrapper` (IN `p_user_id` INT, IN `p_status` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_role` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)   BEGIN
                DECLARE v_value INT DEFAULT 0;
                DECLARE v_unit VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'months';

                -- Read retention value from site_settings
                SELECT CAST(value AS UNSIGNED) INTO v_value
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = CONCAT('auto_delete_', p_status, '_value')
                LIMIT 1;

                -- Read retention unit from site_settings
                SELECT value INTO v_unit
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = CONCAT('auto_delete_', p_status, '_unit')
                LIMIT 1;

                -- Only run if value > 0
                IF v_value > 0 THEN
                    CALL sp_auto_delete_orders(p_user_id, p_status, v_value, v_unit, IF(p_role='seller', 1, 0));
                END IF;
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auto_delete_order_returns` (IN `p_user_id` INT, IN `p_value` INT, IN `p_unit` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_is_seller` TINYINT)   BEGIN
                DECLARE v_threshold DATETIME;

                SET v_threshold = CASE
                    WHEN p_unit = 'minutes' THEN DATE_SUB(NOW(), INTERVAL p_value MINUTE)
                    WHEN p_unit = 'hours' THEN DATE_SUB(NOW(), INTERVAL p_value HOUR)
                    WHEN p_unit = 'days' THEN DATE_SUB(NOW(), INTERVAL p_value DAY)
                    WHEN p_unit = 'months' THEN DATE_SUB(NOW(), INTERVAL p_value MONTH)
                    WHEN p_unit = 'years' THEN DATE_SUB(NOW(), INTERVAL p_value YEAR)
                    ELSE DATE_SUB(NOW(), INTERVAL p_value MONTH)
                END;

                -- Create temporary table to store return IDs to delete
                DROP TEMPORARY TABLE IF EXISTS tmp_returns_to_delete;
                CREATE TEMPORARY TABLE tmp_returns_to_delete (
                    return_id BIGINT UNSIGNED,
                    pickup_img VARCHAR(255),
                    store_img VARCHAR(255)
                );

                -- Insert matching return requests by joining with orders table to match admin/seller ownership
                INSERT INTO tmp_returns_to_delete (return_id, pickup_img, store_img)
                SELECT r.id, r.pickup_image, r.store_image
                FROM order_returns r
                INNER JOIN orders o ON r.order_id = o.id
                WHERE r.status IN ('completed', 'rejected')
                AND r.updated_at < v_threshold
                AND (
                    (p_is_seller = 1 AND JSON_CONTAINS(o.items_json, JSON_OBJECT('seller_id', CAST(p_user_id AS UNSIGNED))))
                    OR (p_is_seller = 0 AND o.admin_id = p_user_id)
                );

                -- Return file paths so php can delete files from disk
                SELECT return_id AS id, pickup_img AS pickup_image, store_img AS store_image FROM tmp_returns_to_delete;

                -- Delete records
                DELETE FROM order_returns
                WHERE id IN (SELECT return_id FROM tmp_returns_to_delete);

                DROP TEMPORARY TABLE IF EXISTS tmp_returns_to_delete;
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auto_delete_order_returns_wrapper` (IN `p_user_id` INT, IN `p_role` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)   BEGIN
                DECLARE v_value INT DEFAULT 0;
                DECLARE v_unit VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'months';

                -- Read retention value from site_settings
                SELECT CAST(value AS UNSIGNED) INTO v_value
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = 'auto_delete_returns_value'
                LIMIT 1;

                -- Read retention unit from site_settings
                SELECT value INTO v_unit
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = 'auto_delete_returns_unit'
                LIMIT 1;

                -- Only run if value > 0
                IF v_value > 0 THEN
                    CALL sp_auto_delete_order_returns(p_user_id, v_value, v_unit, IF(p_role='seller', 1, 0));
                END IF;
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_run_auto_cleanup_all` ()   BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE v_user_id INT;
                DECLARE v_role VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
                DECLARE cur1 CURSOR FOR
                    SELECT id, role FROM users WHERE role IN ('admin', 'seller');
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                OPEN cur1;

                read_loop: LOOP
                    FETCH cur1 INTO v_user_id, v_role;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;

                    -- Auto-delete orders by status
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'delivered', v_role);
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'cancelled', v_role);
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'refunded', v_role);

                    -- Auto-delete read notifications
                    CALL sp_auto_delete_notifications_wrapper(v_user_id);

                    -- Auto-delete completed or rejected order returns
                    CALL sp_auto_delete_order_returns_wrapper(v_user_id, v_role);
                END LOOP;

                CLOSE cur1;

                -- Cleanup expired sessions (older than 24 hours = 86400 seconds)
                CALL sp_auto_delete_expired_sessions(86400);
            END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `badge_text` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `button_text` varchar(255) NOT NULL DEFAULT 'Shop Now',
  `button_link` varchar(255) NOT NULL DEFAULT '#',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `order` int(11) NOT NULL DEFAULT 0,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `image_url`, `badge_text`, `title`, `subtitle`, `button_text`, `button_link`, `is_active`, `order`, `admin_id`, `created_at`, `updated_at`) VALUES
(1, 'media/banners/asdds_5905.jpg', 'Shoes', 'Shoes', 'Effective shoe product descriptions should be detailed, using strong adjectives to highlight style, comfort, and durability while focusing on the benefits to the wearer.', 'Shop', 'product/3', 1, 12, 11, '2026-05-04 09:19:51', '2026-05-12 05:47:30'),
(2, 'media/banners/asdadsa_3465.jpg', 'sdsadsadadsas', 'asdadsa', 'sdfsdfsdcfhewiufnceuwncfguc\r\necfuewifguiergnirnhuivhguchuierhfgisdhfvh', 'hcgfe', 'cghewufg', 1, 12, 11, '2026-05-04 09:20:33', '2026-05-04 09:20:33');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('e-shop-cache-site_currency_symbol', 's:3:\"Rs.\";', 1779948799);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `dp_img_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `admin_id`, `name`, `dp_img_url`, `created_at`, `updated_at`) VALUES
(1, 11, 'Footwear', 'media/categories/img/footwear29.jpg', '2026-05-02 08:49:50', '2026-05-03 11:01:09'),
(2, 11, 'Accessories', 'media/categories/img/accessories60.jpg', '2026-05-02 08:50:33', '2026-05-03 11:01:09'),
(3, 11, 'Electronics', 'media/categories/img/electronics59.jpg', '2026-05-02 08:51:04', '2026-05-03 11:01:09'),
(4, 11, 'asd', 'media/categories/img/asd94.jpeg', '2026-05-02 23:53:52', '2026-05-03 11:01:09');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_applications`
--

CREATE TABLE `delivery_applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_boy_id` bigint(20) UNSIGNED NOT NULL,
  `store_owner_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `delivery_fee` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fire_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_applications`
--

INSERT INTO `delivery_applications` (`id`, `delivery_boy_id`, `store_owner_id`, `status`, `delivery_fee`, `created_at`, `updated_at`, `fire_reason`) VALUES
(4, 12, 11, 'approved', 3204.78, '2026-05-03 04:38:29', '2026-05-09 06:56:53', NULL),
(5, 13, 11, 'rejected', 3204.78, '2026-05-03 06:38:46', '2026-05-22 03:50:02', 'he want'),
(7, 15, 11, 'rejected', 0.00, '2026-05-03 09:39:38', '2026-05-03 09:42:09', 'He worked aother store');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"8d489519-433e-4532-aaec-7f09d203c7ee\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:6:\\\"review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"02ce99f7-10ec-4647-b0c0-f81b0eb3bbc1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1779523159,\"delay\":null}', 0, NULL, 1779523159, 1779523159),
(2, 'default', '{\"uuid\":\"324cbde3-0355-41cf-9f4e-aad06ebb7ca5\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:6:\\\"review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:4;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"aaf55e0c-4c35-4dc9-aebf-c5156a070a39\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1779523194,\"delay\":null}', 0, NULL, 1779523194, 1779523194),
(3, 'default', '{\"uuid\":\"bb467bae-e85c-4cb0-8c65-c5125b33fd27\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:6:\\\"review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"47a6a97b-2bbe-48f7-9129-a3cdba774e19\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1779523432,\"delay\":null}', 0, NULL, 1779523432, 1779523432),
(4, 'default', '{\"uuid\":\"a21489d1-6ed5-4186-885e-d5495cb33b18\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:54:\\\"\\u0000App\\\\Notifications\\\\NewProductReviewNotification\\u0000review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"product\\\";i:1;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"24eb124b-8fc0-4b8f-abae-d0b63871eb31\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1779942797,\"delay\":null}', 0, NULL, 1779942797, 1779942797),
(5, 'default', '{\"uuid\":\"b4c2e3c3-d0db-4342-bfdd-251070e8a6cf\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:54:\\\"\\u0000App\\\\Notifications\\\\NewProductReviewNotification\\u0000review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"product\\\";i:1;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"24eb124b-8fc0-4b8f-abae-d0b63871eb31\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1779942797,\"delay\":null}', 0, NULL, 1779942797, 1779942797),
(6, 'default', '{\"uuid\":\"cf37c934-575a-4442-afc6-be48350a6b79\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:54:\\\"\\u0000App\\\\Notifications\\\\NewProductReviewNotification\\u0000review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:8;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"product\\\";i:1;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"c9166520-b1c8-49c6-9716-f0be17e93922\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1779942992,\"delay\":null}', 0, NULL, 1779942992, 1779942992),
(7, 'default', '{\"uuid\":\"e8babac9-a8a8-47b8-8eba-4641e626a013\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:54:\\\"\\u0000App\\\\Notifications\\\\NewProductReviewNotification\\u0000review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:8;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"product\\\";i:1;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"c9166520-b1c8-49c6-9716-f0be17e93922\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1779942992,\"delay\":null}', 0, NULL, 1779942992, 1779942992),
(8, 'default', '{\"uuid\":\"99cb2105-e918-4427-ae1d-6a58b365a43b\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:54:\\\"\\u0000App\\\\Notifications\\\\NewProductReviewNotification\\u0000review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"product\\\";i:1;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"25c96592-543a-4acd-a996-ebbf40c26de1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1779943351,\"delay\":null}', 0, NULL, 1779943351, 1779943351),
(9, 'default', '{\"uuid\":\"3acbd024-ee01-4e45-8be0-2e02b79cfcc0\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:54:\\\"\\u0000App\\\\Notifications\\\\NewProductReviewNotification\\u0000review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"product\\\";i:1;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"25c96592-543a-4acd-a996-ebbf40c26de1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1779943351,\"delay\":null}', 0, NULL, 1779943351, 1779943351),
(10, 'default', '{\"uuid\":\"6bdcb405-7e0c-4a26-9871-e6ddfc8ae6ab\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:54:\\\"\\u0000App\\\\Notifications\\\\NewProductReviewNotification\\u0000review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:10;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"product\\\";i:1;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"ef914f33-f54f-4b30-a127-479d43778f96\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1779943694,\"delay\":null}', 0, NULL, 1779943694, 1779943694),
(11, 'default', '{\"uuid\":\"a56622c6-52f5-4e1b-a777-9942b5cdd9d6\",\"displayName\":\"App\\\\Notifications\\\\NewProductReviewNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:11;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\NewProductReviewNotification\\\":2:{s:54:\\\"\\u0000App\\\\Notifications\\\\NewProductReviewNotification\\u0000review\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\ProductReview\\\";s:2:\\\"id\\\";i:10;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"product\\\";i:1;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"ef914f33-f54f-4b30-a127-479d43778f96\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1779943694,\"delay\":null}', 0, NULL, 1779943694, 1779943694);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

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
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_02_094231_add_role_to_users_table', 2),
(5, '2026_05_02_104204_move_user_details_to_user_info_table', 3),
(6, '2026_05_02_194500_create_categories_table', 4),
(7, '2026_05_02_201000_create_products_table', 5),
(8, '2026_05_02_152306_add_stock_quantity_to_products_table', 6),
(9, '2026_05_02_165625_add_offer_and_is_new_to_products_table', 7),
(10, '2026_05_02_170010_rename_discount_price_to_discount_percentage_in_products_table', 8),
(11, '2026_05_02_171918_create_orders_table', 9),
(12, '2026_05_03_064107_modify_role_enum_in_users_table', 10),
(13, '2026_05_03_065127_create_delivery_applications_table', 10),
(14, '2026_05_03_065441_create_notifications_table', 11),
(15, '2026_05_03_101203_add_delivery_boy_id_to_orders_table', 12),
(16, '2026_05_03_103005_modify_status_in_delivery_applications_table', 13),
(17, '2026_05_03_104716_add_delivery_verification_to_orders_table', 14),
(18, '2026_05_03_110805_add_balance_and_delivery_fee', 15),
(19, '2026_05_03_110814_add_delivery_fee_to_delivery_applications_table', 15),
(20, '2026_05_03_112536_add_payment_intent_id_to_orders_table', 16),
(21, '2026_05_03_150011_add_fire_reason_to_delivery_applications_table', 17),
(22, '2026_05_03_160737_add_seller_id_to_products_table', 18),
(23, '2026_05_03_161813_add_admin_id_to_users_table', 19),
(27, '2026_05_03_161849_add_admin_id_to_categories_table', 20),
(28, '2026_05_03_161850_add_admin_id_to_products_table2', 20),
(29, '2026_05_03_162144_add_admin_id_to_orders_table', 20),
(30, '2026_05_03_163631_create_seller_assignments_table', 21),
(31, '2026_05_03_164947_create_business_types_table', 22),
(32, '2026_05_03_164954_add_business_type_to_users_table', 22),
(33, '2026_05_03_171939_add_is_blocked_to_users_table', 23),
(34, '2026_05_04_043919_drop_business_types_table_and_column', 24),
(35, '2026_05_04_144324_create_banners_table', 25),
(36, '2026_05_05_045253_add_delivery_image_to_orders_table', 26),
(37, '2026_05_05_045859_create_order_deliveries_table', 27),
(38, '2026_05_05_050048_remove_delivery_columns_from_orders_table', 28),
(39, '2026_05_06_044850_create_site_settings_table', 29),
(40, '2026_05_06_045428_add_user_id_to_site_settings_table', 30),
(41, '2026_05_07_090000_create_product_reviews_table', 31),
(42, '2026_05_07_051326_add_assignment_type_to_orders', 32),
(45, '2026_05_09_090627_create_auto_delete_procedures', 33),
(46, '2026_05_09_091353_create_auto_delete_event', 34),
(47, '2026_05_10_065357_create_order_returns_table', 35),
(48, '2026_05_10_070509_add_delivered_at_to_orders_table', 36),
(49, '2026_05_16_084953_add_delivery_boy_to_order_returns_table', 37),
(50, '2026_05_16_091809_add_images_and_new_status_to_order_returns', 38),
(51, '2026_05_16_093121_add_rejection_reason_to_order_returns', 39),
(52, '2026_05_16_104228_create_product_variants_table', 40),
(54, '2026_05_19_103228_create_auto_delete_stored_procedures_v2', 41),
(55, '2026_05_20_055425_add_payment_method_to_orders_table', 42),
(56, '2026_05_22_095707_add_deleted_fields_to_users', 43),
(57, '2026_05_23_074536_add_reply_to_product_reviews_table', 44),
(58, '2026_05_28_045208_add_reply_fields_to_product_reviews', 45);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('0cb0d650-eb63-4655-9fa4-b24576da3d6f', 'App\\Notifications\\DeliveryApplicationNotification', 'App\\Models\\User', 14, '{\"delivery_boy_id\":15,\"delivery_boy_name\":\"asas\",\"message\":\"asas has applied to join your delivery network.\"}', NULL, '2026-05-03 09:39:30', '2026-05-03 09:39:30'),
('0cdc18a6-5b3c-4c74-a329-dd4399cfddc4', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":45,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"14411.88\",\"message\":\"Order #45 has been cancelled by the customer.\"}', '2026-05-22 04:14:34', '2026-05-19 23:53:43', '2026-05-22 04:14:34'),
('1f5da75e-f107-4d15-bf58-fe2e933ef274', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":17,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"90.00\",\"message\":\"Order #17 has been cancelled by the customer.\"}', NULL, '2026-05-04 23:07:44', '2026-05-04 23:07:44'),
('267690e6-fb36-4af8-b416-8a3fa5520219', 'App\\Notifications\\ProductOutOfStockNotification', 'App\\Models\\User', 11, '{\"product_id\":10,\"product_name\":\"Los\",\"message\":\"Product \\\"Los\\\" is out of stock. Please add stock.\"}', '2026-05-22 03:38:40', '2026-05-19 23:52:36', '2026-05-22 03:38:40'),
('366892fc-637d-4d20-b7e8-6fd835b5c802', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":43,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"4583.54\",\"message\":\"Order #43 has been cancelled by the customer.\"}', NULL, '2026-05-19 23:48:13', '2026-05-19 23:48:13'),
('3d22c5f7-085f-41dd-9d6d-25854da87704', 'App\\Notifications\\DeliveryHiredNotification', 'App\\Models\\User', 12, '{\"store_name\":\"E-Shop\",\"delivery_fee\":10,\"message\":\"Congratulations! You have been hired by E-Shop. You will earn $10.00 per order delivered.\"}', NULL, '2026-05-05 00:31:03', '2026-05-05 00:31:03'),
('40d459c5-8112-4183-8f09-2bbf92a026ab', 'App\\Notifications\\ProductOutOfStockNotification', 'App\\Models\\User', 11, '{\"product_id\":10,\"product_name\":\"Los\",\"message\":\"Product \\\"Los\\\" is out of stock. Please add stock.\"}', '2026-05-22 03:38:47', '2026-05-17 02:05:14', '2026-05-22 03:38:47'),
('48e91255-8814-475f-aa97-40595415d6db', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 12, '{\"order_id\":27,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"12\",\"message\":\"New return request for Order #27. Reason: 12\"}', NULL, '2026-05-16 04:29:42', '2026-05-16 04:29:42'),
('4caffdfb-2e84-4bac-8574-aad202a29248', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":22,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"3376.56\",\"message\":\"Order #22 has been cancelled by the customer.\"}', NULL, '2026-05-06 22:55:38', '2026-05-06 22:55:38'),
('5277ba7a-315f-49e7-abbc-78faca19d65c', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":44,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"4583.54\",\"message\":\"Order #44 has been cancelled by the customer.\"}', NULL, '2026-05-19 23:51:11', '2026-05-19 23:51:11'),
('56802c20-e798-40f7-87a6-9015e608d4c3', 'App\\Notifications\\ProductOutOfStockNotification', 'App\\Models\\User', 17, '{\"product_id\":11,\"product_name\":\"oodivaa\",\"message\":\"Product \\\"oodivaa\\\" is out of stock. Please add stock.\"}', '2026-05-22 03:46:14', '2026-05-07 23:56:59', '2026-05-22 03:46:14'),
('5f42591c-82b2-4254-a944-f8f33612db77', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 12, '{\"order_id\":31,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"color\",\"message\":\"New return request for Order #31. Reason: color\"}', NULL, '2026-05-16 03:08:16', '2026-05-16 03:08:16'),
('66c0c615-e9de-4911-bf8c-44d9b7f43afb', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 12, '{\"order_id\":29,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"asd\",\"message\":\"New return request for Order #29. Reason: asd\"}', NULL, '2026-05-16 04:03:18', '2026-05-16 04:03:18'),
('6d7d59cb-ad2a-414b-b7e0-991cb04203ae', 'App\\Notifications\\ReturnAssignedNotification', 'App\\Models\\User', 12, '{\"return_id\":2,\"order_id\":31,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"color\",\"type\":\"return_assignment\"}', NULL, '2026-05-16 03:27:10', '2026-05-16 03:27:10'),
('7235d6c0-3192-4661-acfc-f0f494d753fe', 'App\\Notifications\\ProductOutOfStockNotification', 'App\\Models\\User', 11, '{\"product_id\":10,\"product_name\":\"Los\",\"message\":\"Product \\\"Los\\\" is out of stock. Please add stock.\"}', '2026-05-19 22:20:08', '2026-05-17 23:23:06', '2026-05-19 22:20:08'),
('73876315-e858-42c5-9db1-b0d2d40879a1', 'App\\Notifications\\DeliveryHiredNotification', 'App\\Models\\User', 16, '{\"store_name\":\"E-Shop\",\"delivery_fee\":12,\"message\":\"Congratulations! You have been hired by E-Shop. You will earn $12.00 per order delivered.\"}', NULL, '2026-05-03 09:54:14', '2026-05-03 09:54:14'),
('765d9d2d-3d39-4f1d-b1fa-8a9547b1ecc8', 'App\\Notifications\\ProductOutOfStockNotification', 'App\\Models\\User', 11, '{\"product_id\":10,\"product_name\":\"Los\",\"message\":\"Product \\\"Los\\\" is out of stock. Please add stock.\"}', '2026-05-19 22:20:04', '2026-05-19 03:07:52', '2026-05-19 22:20:04'),
('77e4e1f1-c7a4-4edd-9800-39b5ee17500f', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 11, '{\"order_id\":29,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"asd\",\"message\":\"New return request for Order #29. Reason: asd\"}', '2026-05-16 04:14:23', '2026-05-16 04:03:18', '2026-05-16 04:14:23'),
('79edd32e-49f7-4655-a42e-7aa14a4e114d', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 13, '{\"order_id\":30,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qwe\",\"message\":\"New return request for Order #30. Reason: qwe\"}', NULL, '2026-05-16 03:53:25', '2026-05-16 03:53:25'),
('7cf37ca3-6f37-4824-91f8-c0532b1d7f72', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":48,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"4803.96\",\"message\":\"Order #48 has been cancelled by the customer.\"}', '2026-05-22 04:14:22', '2026-05-20 00:06:39', '2026-05-22 04:14:22'),
('82b6f5f0-0f0a-4374-8860-5a158d9721bd', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":50,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"1200.99\",\"message\":\"Order #50 has been cancelled by the customer.\"}', '2026-05-20 00:34:11', '2026-05-20 00:33:52', '2026-05-20 00:34:11'),
('86401d74-7cb8-4537-9235-5afb5df18947', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":24,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"3837.00\",\"message\":\"Order #24 has been cancelled by the customer.\"}', NULL, '2026-05-06 22:56:14', '2026-05-06 22:56:14'),
('8fe1848c-7a9e-42fa-ac1e-bdfe7044e225', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":23,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"639.50\",\"message\":\"Order #23 has been cancelled by the customer.\"}', NULL, '2026-05-06 22:55:39', '2026-05-06 22:55:39'),
('9f10dcaf-a289-4d9b-ae94-106872cb98e4', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 13, '{\"order_id\":28,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qza\",\"message\":\"New return request for Order #28. Reason: qza\"}', NULL, '2026-05-16 04:03:27', '2026-05-16 04:03:27'),
('aa063a4a-1d02-4d0f-858f-e4b13a4a6c8c', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 11, '{\"order_id\":31,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"color\",\"message\":\"New return request for Order #31. Reason: color\"}', '2026-05-16 03:51:28', '2026-05-16 03:08:17', '2026-05-16 03:51:28'),
('abea3f87-6638-4866-8f89-5275740a72cf', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 12, '{\"order_id\":30,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qwe\",\"message\":\"New return request for Order #30. Reason: qwe\"}', NULL, '2026-05-16 03:53:25', '2026-05-16 03:53:25'),
('ac19b5fe-296c-4dee-b611-79f14234073d', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":42,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"642.24\",\"message\":\"Order #42 has been cancelled by the customer.\"}', '2026-05-22 04:14:45', '2026-05-19 23:51:20', '2026-05-22 04:14:45'),
('bd52b121-65ed-49d1-b1ba-36327da361ea', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 13, '{\"order_id\":31,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"color\",\"message\":\"New return request for Order #31. Reason: color\"}', NULL, '2026-05-16 03:08:17', '2026-05-16 03:08:17'),
('c6ad7e42-2ee2-4cce-a43d-9f8e107e5bb7', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 11, '{\"order_id\":27,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"12\",\"message\":\"New return request for Order #27. Reason: 12\"}', '2026-05-16 04:30:10', '2026-05-16 04:29:42', '2026-05-16 04:30:10'),
('c81e3950-849d-476b-9e30-6d61dfddaadf', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":49,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"1200.99\",\"message\":\"Order #49 has been cancelled by the customer.\"}', '2026-05-20 00:33:09', '2026-05-20 00:33:04', '2026-05-20 00:33:09'),
('c8942b01-9ada-4d1f-bbd0-e97cbb9d9e2a', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 13, '{\"order_id\":27,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"12\",\"message\":\"New return request for Order #27. Reason: 12\"}', NULL, '2026-05-16 04:29:42', '2026-05-16 04:29:42'),
('caae0a4e-4f34-49b4-8e98-efbbd3fb927c', 'App\\Notifications\\ReturnAssignedNotification', 'App\\Models\\User', 12, '{\"return_id\":5,\"order_id\":28,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qza\",\"type\":\"return_assignment\"}', NULL, '2026-05-17 01:43:57', '2026-05-17 01:43:57'),
('d0a22348-e81f-431a-b93b-319b33b8078e', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 11, '{\"order_id\":30,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qwe\",\"message\":\"New return request for Order #30. Reason: qwe\"}', '2026-05-16 04:01:14', '2026-05-16 03:53:25', '2026-05-16 04:01:14'),
('d18a2cfd-8489-468a-86c5-c6861271bee0', 'App\\Notifications\\ReturnAssignedNotification', 'App\\Models\\User', 12, '{\"return_id\":5,\"order_id\":28,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qza\",\"type\":\"return_assignment\"}', NULL, '2026-05-17 01:43:57', '2026-05-17 01:43:57'),
('d5256b68-e01b-45a8-8f05-687000accec9', 'App\\Notifications\\ReturnAssignedNotification', 'App\\Models\\User', 12, '{\"return_id\":1,\"order_id\":20,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"test\",\"type\":\"return_assignment\"}', NULL, '2026-05-16 03:27:31', '2026-05-16 03:27:31'),
('e1ae6211-6a1c-487d-a4ae-a6e2134ad1a7', 'App\\Notifications\\ReturnAssignedNotification', 'App\\Models\\User', 12, '{\"return_id\":3,\"order_id\":30,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qwe\",\"type\":\"return_assignment\"}', NULL, '2026-05-16 03:53:48', '2026-05-16 03:53:48'),
('e68b94a8-9d13-4b32-97f8-3f6e7ec04bc3', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 13, '{\"order_id\":29,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"asd\",\"message\":\"New return request for Order #29. Reason: asd\"}', NULL, '2026-05-16 04:03:18', '2026-05-16 04:03:18'),
('e730bcf4-5a91-4d0c-83e0-985b605b12c8', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 11, '{\"order_id\":28,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qza\",\"message\":\"New return request for Order #28. Reason: qza\"}', '2026-05-17 01:43:57', '2026-05-16 04:03:27', '2026-05-17 01:43:57'),
('e8fc36fa-5d3b-4da6-9dc4-45cb54685682', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":41,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"642.24\",\"message\":\"Order #41 has been cancelled by the customer.\"}', '2026-05-22 04:15:02', '2026-05-19 23:51:20', '2026-05-22 04:15:02'),
('efd9e721-6909-4101-9c1f-e39375253a48', 'App\\Notifications\\OrderReturnNotification', 'App\\Models\\User', 12, '{\"order_id\":28,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"qza\",\"message\":\"New return request for Order #28. Reason: qza\"}', NULL, '2026-05-16 04:03:27', '2026-05-16 04:03:27'),
('f9a6a71b-44bc-4e55-994b-66b21f8346c9', 'App\\Notifications\\ReturnAssignedNotification', 'App\\Models\\User', 12, '{\"return_id\":6,\"order_id\":27,\"customer_name\":\"Perinpamoorthy Tharanan\",\"reason\":\"12\",\"type\":\"return_assignment\"}', NULL, '2026-05-16 04:30:10', '2026-05-16 04:30:10'),
('fc11f9c1-3098-4fef-90e4-64ef392dc195', 'App\\Notifications\\OrderCancelledNotification', 'App\\Models\\User', 11, '{\"order_id\":40,\"customer_name\":\"Perinpamoorthy Tharanan\",\"amount\":\"642.24\",\"message\":\"Order #40 has been cancelled by the customer.\"}', '2026-05-22 04:14:53', '2026-05-19 23:51:24', '2026-05-22 04:14:53');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `delivery_boy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assignment_type` varchar(255) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `total_items` int(11) NOT NULL,
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items_json`)),
  `status` varchar(255) NOT NULL DEFAULT 'completed',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `admin_id`, `user_id`, `payment_intent_id`, `payment_method`, `delivery_boy_id`, `assignment_type`, `total_price`, `total_items`, `items_json`, `status`, `delivered_at`, `created_at`, `updated_at`) VALUES
(2, 11, 10, NULL, NULL, NULL, NULL, 38444.49, 10, '[{\"name\":\"shoes\",\"price\":3843.81,\"qty\":10,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/shoe_9554.jpg\"}]', 'shipped', NULL, '2026-05-02 11:57:42', '2026-05-09 06:56:53'),
(6, 11, 10, NULL, NULL, NULL, NULL, 3843.81, 1, '[{\"name\":\"Head\",\"price\":3843.81,\"qty\":1,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/as_2187.jpg\"}]', 'shipped', NULL, '2026-05-03 03:09:05', '2026-05-09 06:56:53'),
(20, 11, 10, 'pi_3TU1IeAjeS2kOmi30e7J7IqS', 'stripe', 12, 'self', 9220052.03, 1, '[{\"id\":1,\"name\":\"Head\",\"price\":9220052.03,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/as_2187.jpg\"}]', 'returning', NULL, '2026-05-06 03:29:40', '2026-05-20 00:25:38'),
(21, 11, 10, 'pi_3TU2llAjeS2kOmi31SSFvcg6', 'stripe', 12, 'self', 28836.58, 1, '[{\"id\":1,\"name\":\"Head\",\"price\":28836.58,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/as_2187.jpg\"}]', 'delivered', NULL, '2026-05-06 05:03:45', '2026-05-20 00:25:38'),
(22, 11, 10, 'pi_3TU35KAjeS2kOmi312P2FBW2', 'stripe', NULL, NULL, 3381.39, 1, '[{\"id\":8,\"name\":\"qwewe\",\"price\":3381.39,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/qwewe_6067.png\"}]', 'refunded', NULL, '2026-05-06 05:24:05', '2026-05-20 00:25:38'),
(23, 11, 10, 'pi_3TU36wAjeS2kOmi31LwY8OTt', 'stripe', NULL, NULL, 642.24, 1, '[{\"id\":9,\"name\":\"fewrfewrf\",\"price\":642.24,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/fewrfewrf_2235.png\"}]', 'refunded', NULL, '2026-05-06 05:25:37', '2026-05-20 00:25:38'),
(24, 11, 10, 'pi_3TUJUQAjeS2kOmi31uwvfe0i', 'stripe', NULL, NULL, 3843.81, 1, '[{\"id\":3,\"name\":\"shoes\",\"price\":3843.81,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/shoe_9554.jpg\"}]', 'refunded', NULL, '2026-05-06 22:54:58', '2026-05-20 00:25:38'),
(25, 11, 10, 'pi_3TUJW6AjeS2kOmi30BfDdhnP', 'stripe', 12, 'self', 3843.81, 1, '[{\"id\":3,\"name\":\"shoes\",\"price\":3843.81,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/shoe_9554.jpg\"}]', 'delivered', NULL, '2026-05-06 22:56:45', '2026-05-20 00:25:38'),
(26, 11, 10, 'pi_3TUJxWAjeS2kOmi316qnBMnx', 'stripe', 12, 'self', 3381.39, 1, '[{\"id\":8,\"name\":\"qwewe\",\"price\":3381.39,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/qwewe_6067.png\"}]', 'delivered', NULL, '2026-05-06 23:25:00', '2026-05-20 00:25:38'),
(27, 11, 10, 'pi_3TUK2SAjeS2kOmi30nErXl0b', 'stripe', 12, 'self', 3381.39, 1, '[{\"id\":8,\"name\":\"qwewe\",\"price\":3381.39,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/qwewe_6067.png\"}]', 'returning', NULL, '2026-05-06 23:30:05', '2026-05-20 00:25:38'),
(28, 11, 10, 'pi_3TUKBsAjeS2kOmi317j1hTrF', 'stripe', 13, 'self', 394723.92, 1, '[{\"id\":7,\"name\":\"fdfdff\",\"price\":394723.92,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/fdfdff_7429.png\"}]', 'returning', NULL, '2026-05-06 23:39:53', '2026-05-20 00:25:38'),
(29, 11, 10, 'pi_3TUKIpAjeS2kOmi31AKYpSix', 'stripe', 12, 'self', 3843.81, 1, '[{\"id\":6,\"name\":\"ASURE\",\"price\":3843.81,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/asure_7477.png\"}]', 'returning', NULL, '2026-05-06 23:47:10', '2026-05-20 00:25:38'),
(30, 11, 10, 'pi_3TUKPUAjeS2kOmi30scFPes5', 'stripe', 12, 'admin', 3381.39, 1, '[{\"id\":8,\"name\":\"qwewe\",\"price\":3381.39,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/qwewe_6067.png\"}]', 'returning', NULL, '2026-05-06 23:53:55', '2026-05-20 00:25:38'),
(31, 11, 10, 'pi_3TUKQpAjeS2kOmi31FVPAAJP', 'stripe', 12, 'self', 3381.39, 1, '[{\"id\":8,\"name\":\"qwewe\",\"price\":3381.39,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/qwewe_6067.png\"}]', 'returning', NULL, '2026-05-06 23:55:18', '2026-05-20 00:25:38'),
(32, 11, 10, 'pi_3TUKfuAjeS2kOmi317ZksaM7', 'stripe', 12, 'admin', 3381.39, 1, '[{\"id\":8,\"name\":\"qwewe\",\"price\":3381.39,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/qwewe_6067.png\"}]', 'delivered', '2026-05-17 01:49:23', '2026-05-07 00:10:59', '2026-05-20 00:25:38'),
(33, 11, 10, 'pi_3TUKh5AjeS2kOmi31pwLWUla', 'stripe', 12, 'self', 394723.92, 1, '[{\"id\":7,\"name\":\"fdfdff\",\"price\":394723.92,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/fdfdff_7429.png\"}]', 'delivered', '2026-05-17 01:49:45', '2026-05-07 00:12:07', '2026-05-20 00:25:38'),
(34, 11, 10, 'pi_3TUgqHAjeS2kOmi30dYgfNEy', 'stripe', 12, 'self', 29475.60, 2, '[{\"id\":1,\"name\":\"Head\",\"price\":28833.36,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/as_2187.jpg\"},{\"id\":9,\"name\":\"fewrfewrf\",\"price\":642.24,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/fewrfewrf_2235.png\"}]', 'delivered', '2026-05-17 01:50:34', '2026-05-07 23:51:03', '2026-05-20 00:25:38'),
(35, 11, 10, 'pi_3TUgwUAjeS2kOmi306cya9ZV', 'stripe', 12, 'admin', 1602.39, 2, '[{\"id\":10,\"name\":\"Los\",\"price\":1200.99,\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/los_4888.webp\"},{\"id\":11,\"name\":\"oodivaa\",\"price\":401.4,\"qty\":1,\"seller_id\":17,\"admin_id\":14,\"path\":\"O:\\\\xampp\\\\htdocs\\\\webbuilders\\\\e-com\\\\public\\\\media\\/product_img\\/oodivaa_3895.webp\"}]', 'delivered', '2026-05-17 01:50:20', '2026-05-07 23:57:32', '2026-05-20 00:25:38'),
(36, 11, 10, 'pi_3TXz9sAjeS2kOmi30zuXVUhk', 'stripe', 12, 'admin', 4803.96, 4, '[{\"id\":10,\"name\":\"Los\",\"price\":\"1200.99\",\"qty\":4,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'shipped', NULL, '2026-05-17 02:01:06', '2026-05-20 00:25:38'),
(37, 11, 10, 'pi_3TXzEoAjeS2kOmi31EkOV3Vp', 'stripe', 12, 'self', 8406.93, 7, '[{\"id\":10,\"name\":\"Los\",\"price\":\"1200.99\",\"qty\":7,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'shipped', NULL, '2026-05-17 02:06:02', '2026-05-20 00:25:38'),
(38, 11, 12, 'pi_3TYJJKAjeS2kOmi30yNvS3T1', 'stripe', 12, 'admin', 4803.96, 4, '[{\"id\":10,\"name\":\"Los\",\"price\":\"1200.99\",\"qty\":3,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"},{\"id\":10,\"name\":\"Los\",\"price\":\"1200.99\",\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'processing', NULL, '2026-05-17 23:31:59', '2026-05-20 00:25:38'),
(39, 11, 10, 'pi_3TYjOrAjeS2kOmi31JkV2Puu', 'stripe', 12, 'admin', 1200.99, 1, '[{\"id\":10,\"name\":\"Los\",\"price\":\"1200.99\",\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'processing', NULL, '2026-05-19 03:23:29', '2026-05-20 00:25:38'),
(40, 11, 10, '13206255X7326504V', 'paypal', NULL, NULL, 642.24, 1, '[{\"id\":9,\"name\":\"fewrfewrf\",\"price\":\"642.24\",\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/fewrfewrf_2235.png\"}]', 'refunded', NULL, '2026-05-19 22:46:40', '2026-05-22 04:14:53'),
(41, 11, 10, '4P3228695L451294M', 'paypal', NULL, NULL, 642.24, 1, '[{\"id\":9,\"name\":\"fewrfewrf\",\"price\":\"642.24\",\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/fewrfewrf_2235.png\"}]', 'refunded', NULL, '2026-05-19 22:55:33', '2026-05-22 04:15:13'),
(42, 11, 10, '51U86823FN040363D', 'paypal', NULL, NULL, 642.24, 1, '[{\"id\":9,\"name\":\"fewrfewrf\",\"price\":\"642.24\",\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/fewrfewrf_2235.png\"}]', 'refunded', NULL, '2026-05-19 23:01:51', '2026-05-22 04:14:45'),
(45, 11, 10, '8TB584763D674441N', 'paypal', NULL, NULL, 14411.88, 12, '[{\"id\":10,\"name\":\"Los (blue)\",\"price\":\"1200.99\",\"qty\":6,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"},{\"id\":10,\"name\":\"Los (Black)\",\"price\":\"1200.99\",\"qty\":6,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'refunded', NULL, '2026-05-19 23:53:12', '2026-05-22 04:14:34'),
(47, 11, 10, '7SE41826G8856382Y', 'paypal', 12, 'admin', 1200.99, 1, '[{\"id\":10,\"name\":\"Los\",\"price\":\"1200.99\",\"qty\":1,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'processing', NULL, '2026-05-20 00:01:20', '2026-05-20 00:25:38'),
(48, 11, 10, '5TP86467V5166603J', 'paypal', NULL, NULL, 4803.96, 4, '[{\"id\":10,\"name\":\"Los (Black)\",\"price\":\"1200.99\",\"qty\":2,\"color\":\"Black\",\"size\":null,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"},{\"id\":10,\"name\":\"Los (blue)\",\"price\":\"1200.99\",\"qty\":2,\"color\":\"blue\",\"size\":null,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'refunded', NULL, '2026-05-20 00:05:42', '2026-05-22 04:14:22'),
(49, 11, 10, 'pi_3TZ3BfAjeS2kOmi30zj7INbO', 'stripe', NULL, NULL, 1200.99, 1, '[{\"id\":10,\"name\":\"Los (Black)\",\"price\":\"1200.99\",\"qty\":1,\"color\":\"Black\",\"size\":null,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'refunded', NULL, '2026-05-20 00:31:05', '2026-05-20 00:33:09'),
(50, 11, 10, '5XL41403K9252364V', 'paypal', NULL, NULL, 1200.99, 1, '[{\"id\":10,\"name\":\"Los\",\"price\":\"1200.99\",\"qty\":1,\"color\":null,\"size\":null,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'refunded', NULL, '2026-05-20 00:31:49', '2026-05-20 00:34:11'),
(51, 11, 10, '6R984917R0069833Y', 'paypal', 12, 'admin', 1200.99, 1, '[{\"id\":10,\"name\":\"Los\",\"price\":\"1200.99\",\"qty\":1,\"color\":null,\"size\":null,\"seller_id\":null,\"admin_id\":11,\"store_name\":\"E-Shop\",\"path\":\"media\\/product_img\\/los_4888.webp\"}]', 'processing', NULL, '2026-05-22 03:48:18', '2026-05-22 03:49:10');

-- --------------------------------------------------------

--
-- Table structure for table `order_deliveries`
--

CREATE TABLE `order_deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_boy_id` bigint(20) UNSIGNED NOT NULL,
  `pickup_image` varchar(255) DEFAULT NULL,
  `delivery_image` varchar(255) DEFAULT NULL,
  `secret_code` varchar(10) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'assigned',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_deliveries`
--

INSERT INTO `order_deliveries` (`id`, `order_id`, `delivery_boy_id`, `pickup_image`, `delivery_image`, `secret_code`, `status`, `created_at`, `updated_at`) VALUES
(3, 20, 12, 'media/delivery/pickup/1778058655_1000285448.jpg', 'media/delivery/delivery/1778058813_1000286083.jpg', '165675', 'delivered', '2026-05-06 03:40:55', '2026-05-06 03:43:33'),
(4, 21, 12, 'media/delivery/pickup/1778063734_Screenshot 2026-03-30 142631.png', 'media/delivery/delivery/1778129118_1000286083.jpg', '897585', 'delivered', '2026-05-06 05:05:34', '2026-05-06 23:15:18'),
(5, 25, 12, 'media/delivery/pickup/1778129508_1000286083.jpg', 'media/delivery/delivery/1778129552_1000286083.jpg', '498226', 'delivered', '2026-05-06 23:21:48', '2026-05-06 23:22:32'),
(6, 26, 12, 'media/delivery/pickup/1778129859_1000285448.jpg', 'media/delivery/delivery/1778129922_1000286083.jpg', '490051', 'delivered', '2026-05-06 23:27:39', '2026-05-06 23:28:42'),
(7, 27, 12, 'media/delivery/pickup/1778130082_1000287690.jpg', 'media/delivery/delivery/1778130245_1000287690.jpg', '576507', 'delivered', '2026-05-06 23:31:22', '2026-05-06 23:34:05'),
(8, 28, 13, 'media/delivery/pickup/1778130930_1000287690.jpg', 'media/delivery/delivery/1778130970_1000287690.jpg', '639841', 'delivered', '2026-05-06 23:45:30', '2026-05-06 23:46:10'),
(9, 29, 12, 'media/delivery/pickup/1778131081_1000287690.jpg', 'media/delivery/delivery/1778131139_1000287690.jpg', '347614', 'delivered', '2026-05-06 23:48:01', '2026-05-06 23:48:59'),
(10, 30, 12, 'media/delivery/pickup/1778131480_1000287690.jpg', 'media/delivery/delivery/1778131547_1000287690.jpg', '322529', 'delivered', '2026-05-06 23:54:40', '2026-05-06 23:55:47'),
(11, 31, 12, 'media/delivery/pickup/1778131579_1000287690.jpg', 'media/delivery/delivery/1778132215_1000287690.jpg', '611940', 'delivered', '2026-05-06 23:56:19', '2026-05-07 00:06:55'),
(12, 32, 12, 'media/delivery/pickup/1778133414_1000287690.jpg', 'media/delivery/delivery/1779002362_sho.jpg', '596283', 'delivered', '2026-05-07 00:26:54', '2026-05-17 01:49:23'),
(13, 33, 12, 'media/delivery/pickup/1778133425_1000287690.jpg', 'media/delivery/delivery/1779002385_hed.jpg', '727280', 'delivered', '2026-05-07 00:27:05', '2026-05-17 01:49:45'),
(14, 34, 12, 'media/delivery/pickup/1779001521_sho.jpg', 'media/delivery/delivery/1779002434_wat.jpg', '048042', 'delivered', '2026-05-17 01:35:21', '2026-05-17 01:50:34'),
(15, 35, 12, 'media/delivery/pickup/1779002230_sho.jpg', 'media/delivery/delivery/1779002420_sho.jpg', '927848', 'delivered', '2026-05-17 01:47:10', '2026-05-17 01:50:20'),
(16, 37, 12, 'media/delivery/pickup/1779003881_wat.jpg', NULL, '255326', 'picked_up', '2026-05-17 02:14:41', '2026-05-17 02:14:41'),
(17, 36, 12, 'media/delivery/pickup/1779003944_sho.jpg', NULL, '746730', 'picked_up', '2026-05-17 02:15:44', '2026-05-17 02:15:44');

-- --------------------------------------------------------

--
-- Table structure for table `order_returns`
--

CREATE TABLE `order_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_boy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assignment_type` varchar(255) DEFAULT NULL,
  `pickup_image` varchar(255) DEFAULT NULL,
  `store_image` varchar(255) DEFAULT NULL,
  `reason` text NOT NULL,
  `rejection_reason` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_returns`
--

INSERT INTO `order_returns` (`id`, `order_id`, `user_id`, `delivery_boy_id`, `assignment_type`, `pickup_image`, `store_image`, `reason`, `rejection_reason`, `status`, `created_at`, `updated_at`) VALUES
(1, 20, 10, 12, 'admin', NULL, NULL, 'test', NULL, 'completed', '2026-05-10 01:37:59', '2026-05-16 03:28:14'),
(2, 31, 10, 12, 'admin', 'media/returns/pickup/1778923522_Screenshot 2026-05-15 104113.jpg', 'media/returns/store/1778923533_Screenshot 2026-05-15 110940.jpg', 'color', NULL, 'completed', '2026-05-16 03:08:15', '2026-05-16 03:55:33'),
(3, 30, 10, 12, 'admin', 'media/returns/pickup/1778923658_Screenshot 2026-05-15 124452.jpg', 'media/returns/store/1778923708_Screenshot 2026-05-15 124452.jpg', 'qwe', NULL, 'completed', '2026-05-16 03:53:25', '2026-05-16 03:58:28'),
(4, 29, 10, NULL, NULL, NULL, NULL, 'asd', 'not clear reson', 'rejected', '2026-05-16 04:03:18', '2026-05-16 04:04:02'),
(5, 28, 10, 12, 'admin', 'media/returns/pickup/1779002169_sho.jpg', 'media/returns/store/1779002194_sho.jpg', 'qza', NULL, 'completed', '2026-05-16 04:03:27', '2026-05-17 01:46:34'),
(6, 27, 10, 12, 'admin', 'media/returns/pickup/1779002176_wat.jpg', 'media/returns/store/1779002199_sho.jpg', '12', NULL, 'completed', '2026-05-16 04:29:42', '2026-05-17 01:46:39');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `seller_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_percentage` int(11) DEFAULT NULL,
  `is_new` tinyint(1) NOT NULL DEFAULT 0,
  `stock_status` varchar(255) NOT NULL DEFAULT 'available',
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image_urls`)),
  `main_image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `admin_id`, `seller_id`, `category_id`, `name`, `description`, `price`, `discount_percentage`, `is_new`, `stock_status`, `stock_quantity`, `image_urls`, `main_image_url`, `created_at`, `updated_at`) VALUES
(1, 11, NULL, 3, 'Head', 'aas', 32038.14, 10, 1, 'available', 11, '[\"media\\/product_img\\/as_2187.jpg\"]', 'media/product_img/as_2187.jpg', '2026-05-02 09:17:32', '2026-05-09 06:56:53'),
(3, 11, NULL, 1, 'shoes', 'aa', 3843.81, 0, 0, 'available', 1, '[\"media\\/product_img\\/shoe_9554.jpg\",\"media\\/product_img\\/shoes_1515.png\",\"media\\/product_img\\/shoes_1070.png\"]', 'media/product_img/shoe_9554.jpg', '2026-05-02 10:01:12', '2026-05-09 06:56:53'),
(4, 11, NULL, 4, 'asd', NULL, 321.12, 0, 0, 'available', 122, '[\"media\\/product_img\\/asd_6655.jpeg\"]', 'media/product_img/asd_6655.jpeg', '2026-05-03 05:58:35', '2026-05-09 06:56:53'),
(6, 11, NULL, 1, 'ASURE', 'qweweqwedasdqwdas\r\ndsa\r\ndewdewd', 3843.81, 0, 0, 'available', 11, '[\"media\\/product_img\\/asure_7477.png\"]', 'media/product_img/asure_7477.png', '2026-05-05 22:35:10', '2026-05-09 06:56:53'),
(8, 11, NULL, 1, 'qwewe', '23213fewfrefref', 3843.81, 12, 0, 'available', 118, '[\"media\\/product_img\\/qwewe_6067.png\"]', 'media/product_img/qwewe_6067.png', '2026-05-05 22:36:10', '2026-05-19 03:31:16'),
(9, 11, NULL, 1, 'fewrfewrf', '2322', 642.24, 0, 0, 'available', 13, '[\"media\\/product_img\\/fewrfewrf_2235.png\"]', 'media/product_img/fewrfewrf_2235.png', '2026-05-05 22:36:50', '2026-05-19 23:51:19'),
(10, 11, NULL, 1, 'Los', 'Ahshdjdjmsjw\r\nShshshsjjs', 1200.99, NULL, 1, 'available', 23, '[\"media\\/product_img\\/los_4888.webp\",\"media\\/product_img\\/los_3997.webp\",\"media\\/product_img\\/los_9069.webp\"]', 'media/product_img/los_4888.webp', '2026-05-07 02:28:23', '2026-05-22 03:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `replied_by` bigint(20) UNSIGNED DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `rating`, `title`, `comment`, `reply`, `created_at`, `updated_at`, `replied_by`, `replied_at`) VALUES
(1, 3, 10, 3, 'price', 'product is good but price is high', NULL, '2026-05-06 22:39:06', '2026-05-06 22:39:35', NULL, NULL),
(2, 10, 10, 4, 'Supper', 'Good products limjdewd', NULL, '2026-05-07 02:32:11', '2026-05-23 02:28:01', NULL, NULL),
(7, 8, 10, 3, 'sdsw', 'sdsadxs', NULL, '2026-05-27 23:03:17', '2026-05-27 23:06:09', NULL, NULL),
(8, 4, 10, 2, 'aas', 'asasssaaa121qsdf7867', NULL, '2026-05-27 23:06:31', '2026-05-27 23:32:58', NULL, NULL),
(9, 9, 10, 3, 'sdsds', 'sadsad', NULL, '2026-05-27 23:12:31', '2026-05-27 23:12:31', NULL, NULL),
(10, 6, 10, 3, 'asdd', 'asddsad', NULL, '2026-05-27 23:18:14', '2026-05-27 23:18:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_type` enum('color','size') NOT NULL,
  `value` varchar(255) NOT NULL,
  `hex_code` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `price_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_type`, `value`, `hex_code`, `image_url`, `stock_quantity`, `price_adjustment`, `sort_order`, `created_at`, `updated_at`) VALUES
(11, 10, 'color', 'Black', '#000000', 'media/product_img/loscolorblack_9458.webp', 12, 0.00, 0, '2026-05-20 00:05:00', '2026-05-20 00:32:59'),
(12, 10, 'color', 'blue', '#0d2cc9', 'media/product_img/loscolorblue_7883.webp', 12, 0.00, 1, '2026-05-20 00:05:00', '2026-05-20 00:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `seller_assignments`
--

CREATE TABLE `seller_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('4N1hLQYDAql6kIQrrCrOVHeQTNFzA877NgrMah7J', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.122.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSm5DTUw3OXdXZzhHMkNFS24zUjI4bFk5VEFQVjV0RERZaEVXTURWRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly9sb2NhbGhvc3Qvd2ViYnVpbGRlcnMvZS1jb20vcHVibGljL3NzZS9zdHJlYW0iO3M6NToicm91dGUiO3M6MTA6InNzZS5zdHJlYW0iO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMTt9', 1779944828),
('i570qydus1AA7bKPiXAhn5cWXSbepixnUUrCpP2i', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaWpyTDBIV212RHVZMHZTM0JLeGNRSUs2aDZua2pQYkZBSHlxVkw1cSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly9sb2NhbGhvc3Qvd2ViYnVpbGRlcnMvZS1jb20vcHVibGljL3NzZS9zdHJlYW0iO3M6NToicm91dGUiO3M6MTA6InNzZS5zdHJlYW0iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1779945469),
('UxpboeiyoDYBA6HBBzB6Fy8eoeyLkSSWXssES3EI', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.122.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNnFFUko2UUFKdElwczBra3BFcVRRbjl1SjhYMHJhTGJlaVUzclNvcyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Qvd2ViYnVpbGRlcnMvZS1jb20vcHVibGljL2FkbWluL3NpZ24taW4iO3M6NToicm91dGUiO3M6MTE6ImFkbWluLmxvZ2luIjt9fQ==', 1779944987),
('xtEQbwLyvEBaIM1CrY6lGkzywD4w3lvx7VDtLHVm', 10, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVmdNM1RIMkx3bU1OU0c0OW1vYW1ENG5qcGZqSDJ3RjFLR3pIN2tVUCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTA7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTE6Imh0dHA6Ly9sb2NhbGhvc3Qvd2ViYnVpbGRlcnMvZS1jb20vcHVibGljL3Byb2R1Y3QvNCI7czo1OiJyb3V0ZSI7czoxMjoicHJvZHVjdC5zaG93Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1779944584);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `user_id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 11, 'auto_delete_delivered_value', '1', '2026-05-06 00:10:42', '2026-05-06 00:17:26'),
(2, 11, 'auto_delete_delivered_unit', 'years', '2026-05-06 00:10:42', '2026-05-06 00:23:50'),
(3, 11, 'auto_delete_cancelled_value', '1', '2026-05-06 00:10:42', '2026-05-06 00:17:26'),
(4, 11, 'auto_delete_cancelled_unit', 'years', '2026-05-06 00:10:42', '2026-05-06 00:17:26'),
(5, 11, 'auto_delete_refunded_value', '1', '2026-05-06 00:10:42', '2026-05-06 00:10:42'),
(6, 11, 'auto_delete_refunded_unit', 'years', '2026-05-06 00:10:42', '2026-05-06 00:17:26'),
(7, 11, 'auto_delete_notifications_days', '7', '2026-05-06 00:10:42', '2026-05-06 00:10:42'),
(15, NULL, 'site_currency', 'LKR', '2026-05-06 02:22:40', '2026-05-09 06:56:53'),
(16, NULL, 'site_currency_symbol', 'Rs.', '2026-05-06 02:22:40', '2026-05-09 06:56:53'),
(17, 11, 'auto_delete_returns_value', '1', '2026-05-19 05:19:14', '2026-05-19 05:19:14'),
(18, 11, 'auto_delete_returns_unit', 'years', '2026-05-19 05:19:14', '2026-05-19 05:19:14'),
(19, 11, 'auto_delete_sessions_value', '2', '2026-05-19 05:19:14', '2026-05-19 05:19:14'),
(20, 11, 'auto_delete_sessions_unit', 'months', '2026-05-19 05:19:14', '2026-05-19 05:19:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','seller','client','delivery_boy') DEFAULT 'client',
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_edited_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deletion_reason` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `admin_id`, `name`, `email`, `email_verified_at`, `password`, `otp`, `otp_expires_at`, `is_verified`, `remember_token`, `created_at`, `updated_at`, `role`, `is_blocked`, `balance`, `last_edited_by`, `deletion_reason`, `deleted_at`) VALUES
(10, NULL, 'Perinpamoorthy Tharanan', 'ptharanan@gmail.com', NULL, '$2y$12$Z5jKIGvdGeMQkJX7KYkMZutSi58B1fCJXn9HrweVRZozUyKE3Q78u', NULL, NULL, 1, '33s3SHkV2HVdXdWk6B5nwVzbnCIEUVB0xYK1GV1lISMbBmWaUNlAZPfU9X8l', '2026-05-02 05:19:52', '2026-05-22 04:15:13', 'client', 0, 82653.36, NULL, NULL, NULL),
(11, NULL, 'Tharanan', 'ptharanan@gmail.coma', NULL, '$2y$12$xTrrVxb399hb7KWm5bYdiOwTCd3eWW98JsdPAd/bDYAZFX/56qCBe', NULL, NULL, 1, '27yeSnX0BEDZgK1r5Ueo8e1MbHT44pj4xOh79Odd2VUZ3iiywWK8buc5O9C1', '2026-05-02 05:22:23', '2026-05-22 05:06:29', 'admin', 0, -70583.96, NULL, NULL, NULL),
(12, NULL, 'davin', 'davin@e-shop', NULL, '$2y$12$J.850fPNX10jTtAVf9LuROKMRJg3KTgS1hbESMLcsw10Gi0cXiSmS', NULL, NULL, 1, 'xRLF1gr2hMQhfNCzf68rFweFQ06aH48uttpkAqGMNklr27IrSrhu1aGIDgdX', '2026-05-03 01:17:09', '2026-05-17 01:50:34', 'delivery_boy', 0, 44857.26, NULL, NULL, NULL),
(13, NULL, 'Twrrer', 'vimalathevithanikasalam@gmail.com', NULL, '$2y$12$tMKFYBM/oTw0jyW2p2Pmb.vfVNAhy3XYnFqw5cFwzsFHpZryJ8fBu', NULL, NULL, 1, 'gvO08oMMUlaFDIRvNEbTlYOjNjgZdizVWH4DDQma0RgdIJMWajj8e8YDGSsi', '2026-05-03 02:18:05', '2026-05-09 06:56:53', 'delivery_boy', 0, 3204.78, NULL, NULL, NULL),
(15, NULL, 'asas', 'besivej659@cadinr.com', NULL, '$2y$12$67TjoRizb39EPGlYGwYfEeD2EGYteykylm/VlEo88JkZmdDG/YJ6K', NULL, NULL, 1, 'eDLFr0Sqrl6QJgsF9DODxDlunSFJ5EfmkuDpHr5elJJUkXA5katuo3CFlP9C', '2026-05-03 09:38:51', '2026-05-09 06:56:53', 'delivery_boy', 0, 3843.81, NULL, NULL, NULL),
(16, NULL, 'mmu', 'kabiceb817@cadinr.com', NULL, '$2y$12$IYWLQ.4ffD2EyAE91I8PXuEGwfBOt6fw9GqtFBpvXRlyJQybTqLX2', NULL, NULL, 1, NULL, '2026-05-03 09:44:24', '2026-05-09 06:56:53', 'delivery_boy', 0, 3843.81, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_info`
--

CREATE TABLE `user_info` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `phno` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_info`
--

INSERT INTO `user_info` (`id`, `user_id`, `phno`, `address`, `created_at`, `updated_at`) VALUES
(1, 10, '0771800458', 'Thalayali Ln, Jaffna [ 9.687111, 80.017917 ]', '2026-05-02 05:19:52', '2026-05-02 05:19:52'),
(2, 11, NULL, NULL, '2026-05-02 05:22:23', '2026-05-02 05:22:23'),
(4, 15, '123456789', 'asqwzxcv', '2026-05-03 09:39:28', '2026-05-03 09:39:28'),
(5, 16, '123456789', 'qwewdsd\r\nasdsadsadsaderw', '2026-05-03 09:45:08', '2026-05-03 09:45:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `delivery_applications`
--
ALTER TABLE `delivery_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_applications_delivery_boy_id_foreign` (`delivery_boy_id`),
  ADD KEY `delivery_applications_store_owner_id_foreign` (`store_owner_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_delivery_boy_id_foreign` (`delivery_boy_id`),
  ADD KEY `orders_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `order_deliveries`
--
ALTER TABLE `order_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_deliveries_order_id_foreign` (`order_id`),
  ADD KEY `order_deliveries_delivery_boy_id_foreign` (`delivery_boy_id`);

--
-- Indexes for table `order_returns`
--
ALTER TABLE `order_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_returns_order_id_foreign` (`order_id`),
  ADD KEY `order_returns_user_id_foreign` (`user_id`),
  ADD KEY `order_returns_delivery_boy_id_foreign` (`delivery_boy_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_seller_id_foreign` (`seller_id`),
  ADD KEY `products_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_reviews_product_id_user_id_unique` (`product_id`,`user_id`),
  ADD KEY `product_reviews_user_id_foreign` (`user_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variants_product_id_foreign` (`product_id`);

--
-- Indexes for table `seller_assignments`
--
ALTER TABLE `seller_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_assignments_seller_id_foreign` (`seller_id`),
  ADD KEY `seller_assignments_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_settings_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_admin_id_foreign` (`admin_id`),
  ADD KEY `users_last_edited_by_foreign` (`last_edited_by`);

--
-- Indexes for table `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_info_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `delivery_applications`
--
ALTER TABLE `delivery_applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `order_deliveries`
--
ALTER TABLE `order_deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_returns`
--
ALTER TABLE `order_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `seller_assignments`
--
ALTER TABLE `seller_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_info`
--
ALTER TABLE `user_info`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_applications`
--
ALTER TABLE `delivery_applications`
  ADD CONSTRAINT `delivery_applications_delivery_boy_id_foreign` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_applications_store_owner_id_foreign` FOREIGN KEY (`store_owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_delivery_boy_id_foreign` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_deliveries`
--
ALTER TABLE `order_deliveries`
  ADD CONSTRAINT `order_deliveries_delivery_boy_id_foreign` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_returns`
--
ALTER TABLE `order_returns`
  ADD CONSTRAINT `order_returns_delivery_boy_id_foreign` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_returns_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_assignments`
--
ALTER TABLE `seller_assignments`
  ADD CONSTRAINT `seller_assignments_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_assignments_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD CONSTRAINT `site_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_last_edited_by_foreign` FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_info`
--
ALTER TABLE `user_info`
  ADD CONSTRAINT `user_info_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
