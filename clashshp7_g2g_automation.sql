-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 01, 2025 at 09:37 AM
-- Server version: 10.6.23-MariaDB
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clashshp7_g2g_automation`
--

-- --------------------------------------------------------

--
-- Table structure for table `application_setups`
--

CREATE TABLE `application_setups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_setups`
--

INSERT INTO `application_setups` (`id`, `type`, `value`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'G2G Automation', '2025-09-26 08:08:57', '2025-09-26 08:08:57'),
(2, 'app_email', 'tanbir@mail.com', '2025-09-26 08:08:57', '2025-09-26 08:08:57'),
(3, 'app_phone', '+56565848541', '2025-09-26 08:08:57', '2025-09-26 08:08:57'),
(4, 'app_address', 'Uttara, Dhaka', '2025-09-26 08:08:57', '2025-09-26 08:08:57'),
(5, 'app_logo', 'organization/68dcb6e963ceb-logoipsum-236.png', '2025-09-26 08:08:57', '2025-10-01 11:06:49'),
(6, 'app_favicon', 'organization/68dcb6e968f5b-icons8-favicon-50.png', '2025-09-26 08:09:39', '2025-10-01 11:06:49'),
(7, 'scheduler', '{\"start\":\"00:01\",\"end\":\"23:59\",\"intervalMinutes\":\"1\"}', '2025-09-29 10:55:13', '2025-09-29 11:32:29'),
(8, 'schedule_start_time', '00:50', '2025-09-29 10:55:13', '2025-09-29 11:39:36'),
(9, 'schedule_end_time', '23:59', '2025-09-29 10:55:13', '2025-09-29 10:55:13'),
(10, 'schedule_interval_minutes', '1', '2025-09-29 10:55:13', '2025-09-29 10:55:13'),
(11, 'scheduler_windows', '[{\"start\":\"11:45\",\"end\":\"14:01\"}]', '2025-09-30 07:19:05', '2025-10-01 15:22:18'),
(12, 'schedule_days', 'mon,tue,wed,thu,fri,sat,sun', '2025-09-30 07:19:05', '2025-09-30 07:19:05');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Dumping data for table `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(1, '09a9d63d-9d8c-4cab-b39e-5530ccdae3c1', 'database', 'default', '{\"uuid\":\"09a9d63d-9d8c-4cab-b39e-5530ccdae3c1\",\"displayName\":\"App\\\\Jobs\\\\PostOfferTemplate\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":300,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\PostOfferTemplate\",\"command\":\"O:26:\\\"App\\\\Jobs\\\\PostOfferTemplate\\\":1:{s:11:\\\"\\u0000*\\u0000template\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:24:\\\"App\\\\Models\\\\OfferTemplate\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 'Exception: Node.js process failed with exit code: 1 in /home/clashshp7/public_html/app/Jobs/PostOfferTemplate.php:195\nStack trace:\n#0 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): App\\Jobs\\PostOfferTemplate->handle()\n#1 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#2 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(95): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#3 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#4 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/Container.php(696): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#5 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(126): Illuminate\\Container\\Container->call(Array)\n#6 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(170): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\PostOfferTemplate))\n#7 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(127): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\PostOfferTemplate))\n#8 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(130): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#9 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(126): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\PostOfferTemplate), false)\n#10 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(170): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\PostOfferTemplate))\n#11 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(127): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\PostOfferTemplate))\n#12 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(121): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(69): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\PostOfferTemplate))\n#14 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#15 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(442): Illuminate\\Queue\\Jobs\\Job->fire()\n#16 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(392): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#17 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(178): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#18 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#20 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#21 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#22 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(95): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#23 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#24 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Container/Container.php(696): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#25 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Console/Command.php(213): Illuminate\\Container\\Container->call(Array)\n#26 /home/clashshp7/public_html/vendor/symfony/console/Command/Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#27 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Console/Command.php(182): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#28 /home/clashshp7/public_html/vendor/symfony/console/Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#29 /home/clashshp7/public_html/vendor/symfony/console/Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 /home/clashshp7/public_html/vendor/symfony/console/Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 /home/clashshp7/public_html/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 /home/clashshp7/public_html/artisan(13): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#34 {main}', '2025-10-01 12:08:31');

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
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `value` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`id`, `value`, `type`, `active`, `created_at`, `updated_at`) VALUES
(1, '17', 'Town Hall', 1, '2025-09-25 10:09:25', '2025-09-25 10:09:25'),
(2, '90+', 'King', 1, '2025-09-25 10:09:35', '2025-09-25 10:09:41'),
(3, '70+', 'Queen', 1, '2025-09-25 10:09:50', '2025-09-25 10:09:50'),
(4, '70+', 'Warden', 1, '2025-09-25 10:10:01', '2025-09-25 10:10:01'),
(5, '45+', 'Champion', 1, '2025-09-25 10:10:09', '2025-09-25 10:10:09'),
(6, '100', 'King', 1, '2025-09-26 08:17:04', '2025-09-26 08:17:04'),
(7, '100', 'Queen', 1, '2025-09-29 07:38:44', '2025-09-29 07:38:44'),
(8, '75', 'Warden', 1, '2025-09-29 07:39:54', '2025-09-29 07:39:54'),
(9, '16', 'Town Hall', 1, '2025-09-29 07:40:07', '2025-09-29 07:40:07'),
(10, '50', 'Champion', 1, '2025-09-29 07:40:27', '2025-09-29 07:40:27');

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
(4, '2024_11_13_170656_create_permission_tables', 1),
(5, '2024_11_15_105127_add_group_to_permissions_table', 1),
(6, '2024_11_18_152158_create_application_setups_table', 1),
(12, '2025_09_23_061323_create_user_accounts_table', 2),
(13, '2025_09_23_084839_create_offer_templates_table', 2),
(14, '2025_09_23_105626_add_columns_to_offer_template', 2),
(15, '2025_09_25_091100_create_levels_table', 2),
(16, '2025_09_26_090028_change_column_type_to_offer_templates_table', 3),
(18, '2025_09_29_101729_create_offer_schedulers_table', 4),
(21, '2025_09_30_151731_create_offer_automation_logs_table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `offer_automation_logs`
--

CREATE TABLE `offer_automation_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `offer_template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('success','failed') NOT NULL,
  `message` text NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offer_schedulers`
--

CREATE TABLE `offer_schedulers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `offer_template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_time` time NOT NULL DEFAULT '09:00:00',
  `end_time` time NOT NULL DEFAULT '17:00:00',
  `posts_per_cycle` int(11) NOT NULL DEFAULT 1,
  `interval_minutes` int(11) NOT NULL DEFAULT 60,
  `max_posts_per_day` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `posts_today` int(11) NOT NULL DEFAULT 0,
  `posts_today_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offer_templates`
--

CREATE TABLE `offer_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_account_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `th_level` varchar(255) DEFAULT NULL,
  `king_level` varchar(255) DEFAULT NULL,
  `queen_level` varchar(255) DEFAULT NULL,
  `warden_level` varchar(255) DEFAULT NULL,
  `champion_level` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `medias` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`medias`)),
  `currency` varchar(255) NOT NULL DEFAULT 'USD',
  `price` decimal(8,2) NOT NULL DEFAULT 0.00,
  `delivery_method` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`delivery_method`)),
  `enable_low_stock_alert` tinyint(1) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT NULL,
  `minimum_order_quantity` int(11) NOT NULL DEFAULT 1,
  `instant_delivery` tinyint(1) NOT NULL DEFAULT 1,
  `enable_wholesale_pricing` tinyint(1) NOT NULL DEFAULT 0,
  `wholesale_pricing` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`wholesale_pricing`)),
  `region` varchar(255) NOT NULL DEFAULT 'Global',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_posted_at` timestamp NULL DEFAULT NULL,
  `offers_to_generate` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offer_templates`
--

INSERT INTO `offer_templates` (`id`, `user_account_id`, `title`, `description`, `th_level`, `king_level`, `queen_level`, `warden_level`, `champion_level`, `is_active`, `medias`, `currency`, `price`, `delivery_method`, `enable_low_stock_alert`, `low_stock_threshold`, `minimum_order_quantity`, `instant_delivery`, `enable_wholesale_pricing`, `wholesale_pricing`, `region`, `created_at`, `updated_at`, `last_posted_at`, `offers_to_generate`) VALUES
(2, 1, 'TH 17 full max nothing left [ 5 epic max 2 lvl 20+ others all good] | Heros-100-100-90-75-50 | th17 max | xp-265 | instant delivery', 'Do not Cancel The Order, Please Have Patience. We Will Respond As Soon As We see It [ If Not Sleeping ]\r\nAll types of accounts are available, please inbox us your budget and requirements.\r\n\r\nWHAT WILL YOU GET AFTER PLACING AN ORDER?\r\n- Email linked to Supercell ID &amp; password [Full Email Access] or if you want you can change account mail any time\r\n-You &#039;ll have aGuidelines to secure your account\r\n-SAFE AND SECURE ACCOUNT PURCHASES FROM MY STORE\r\n\r\nAuthentic Accounts: Thoroughly tested and secure accounts.\r\nComprehensive Guarantee: Sourced directly from original owners for authenticity.\r\nPeace of Mind: Complete guarantee and quick issue resolution.\r\nExclusive Ownership: You will have complete ownership of the purchased account.\r\n\r\n! IMPORTANT ACCOUNT GUIDELINES TO FOLLOW:\r\nWe will help you secure details.\r\nKeep your account private and avoid sharing it\r\nDo not contact Supercell Help and suppport.\r\nDelay name changes for 12 hours at least and in-game purchases for 3-7 days.', '17', '100', '100', '75', '50', 0, '[{\"title\":\"Details\",\"link\":\"https:\\/\\/www.dropbox.com\\/scl\\/fi\\/b0zqlrhzjssnspyq5zhnc\\/InCollage_20250826_171830766.jpg?rlkey=tx6ymo0je9zy104unr2f38wf8&st=p3nbp0q8&raw=1\"}]', 'USD', 240.00, '{\"method\":\"manual\",\"quantity_from\":0,\"speed_hour\":\"0\",\"speed_min\":\"10\"}', 0, NULL, 1, 1, 0, NULL, 'Global', '2025-09-26 08:41:03', '2025-10-01 15:22:36', '2025-09-30 15:52:15', 0),
(4, 1, 'TH 17 near max [3 heros max and 20k = 20000 gems] | Heros-100-100-71-75-45 | th17 max | xp-230 | instant delivery', 'Do not cancel the order , Please Have Patience. We Will Respond As Soon As We see It [ If Not Sleeping ]\r\n\r\nAll types of accounts are available, please inbox us your budget and requirements.\r\n\r\nWHAT WILL YOU GET AFTER PLACING AN ORDER?\r\n\r\n- Email linked to Supercell ID & password [Full Email Access] or if you want you can change account mail any time\r\n\r\n-You \'ll have aGuidelines to secure your account\r\n\r\n_\r\n\r\n-SAFE AND SECURE ACCOUNT PURCHASES FROM MY STORE\r\n\r\nAuthentic Accounts: Thoroughly tested and secure accounts.\r\n\r\nComprehensive Guarantee: Sourced directly from original owners for authenticity.\r\n\r\nPeace of Mind: Complete guarantee and quick issue resolution.\r\n\r\nExclusive Ownership: You will have complete ownership of the purchased account.\r\n\r\n_\r\n\r\n! IMPORTANT ACCOUNT GUIDELINES TO FOLLOW:\r\n\r\nWe will help you secure details.\r\n\r\nKeep your account private and avoid sharing it\r\n\r\nDo not contact Supercell Help and suppport.\r\n\r\nDelay name changes for 12 hours at least and in-game purchases for 3-7 days.', '17', '100', '100', '75', '50', 0, '[{\"title\":\"Image\",\"link\":\"https:\\/\\/www.dropbox.com\\/scl\\/fi\\/1ox7w331djf9fh1jkhlom\\/InCollage_20250826_170111747.jpg?rlkey=ah2ar9dqcpko9gsw5qs5gkcza&st=q0vbgmo1&raw=1\"}]', 'USD', 90.00, '{\"method\":\"manual\",\"quantity_from\":\"1\",\"speed_hour\":\"0\",\"speed_min\":\"10\"}', 0, NULL, 1, 1, 0, NULL, 'Global', '2025-09-29 09:52:02', '2025-10-01 15:22:32', '2025-09-30 15:53:11', 0),
(5, 3, 'TH 17 max | Heros-67-95-70-60-45 | th17 max | 1681 cwl token | epoc equipment high lvl | max defence and troops | golden and purple walls | xp-225 | instant delivery  | ios and android  30', 'TH 17 max | Heros-67-95-70-60-45 | th17 max | 1681 cwl token | epoc equipment high lvl | max defence and troops | golden and purple walls | xp-225 | instant delivery  | ios and android\r\n\r\n30\r\n\r\nPicture - https://photos.app.goo.gl/WFK97q5uSn3tFax68\r\n\r\nDo not Cancel The Order, Please Have Patience. We Will Respond As Soon As We see It [ If Not Sleeping ]\r\n\r\nAll types of accounts are available, please inbox us your budget and requirements.\r\n\r\nWHAT WILL YOU GET AFTER PLACING AN ORDER?\r\n\r\n- Email linked to Supercell ID & password [Full Email Access] or if you want you can change account mail any time\r\n\r\n-You \'ll have aGuidelines to secure your account\r\n\r\n_\r\n\r\n-SAFE AND SECURE ACCOUNT PURCHASES FROM MY STORE\r\n\r\nAuthentic Accounts: Thoroughly tested and secure accounts.\r\n\r\nComprehensive Guarantee: Sourced directly from original owners for authenticity.\r\n\r\nPeace of Mind: Complete guarantee and quick issue resolution.\r\n\r\nExclusive Ownership: You will have complete ownership of the purchased account.\r\n\r\n_\r\n\r\n! IMPORTANT ACCOUNT GUIDELINES TO FOLLOW:\r\n\r\nWe will help you secure details.\r\n\r\nKeep your account private and avoid sharing it\r\n\r\nDo not contact Supercell Help and suppport.\r\n\r\nDelay name changes for 12 hours at least and in-game purchases for 3-7 days.\r\n\r\n70$', '17', '100', '100', '70+', '45+', 0, '\"[{\\\"title\\\":null,\\\"link\\\":\\\"https:\\\\\\/\\\\\\/www.dropbox.com\\\\\\/scl\\\\\\/fi\\\\\\/c9qmfwitrbhlnexeewmzr\\\\\\/InCollage_20250702_145317095.jpg?rlkey=puk3ggey8oizixe395lbbimdi&st=4smnil6i&raw=1\\\"}]\"', 'USD', 70.00, '\"{\\\"method\\\":\\\"manual\\\",\\\"quantity_from\\\":\\\"1\\\",\\\"speed_hour\\\":\\\"0\\\",\\\"speed_min\\\":\\\"10\\\"}\"', 0, NULL, 1, 1, 0, NULL, 'Global', '2025-09-30 23:58:04', '2025-10-01 15:22:28', NULL, 1),
(6, 3, 'TH 16 semi max | Heros-57-75-41-50-32 | th16 max | 500 cwl madel | equipment good | golden  walls | xp-170 | instant delivery  | ios and android', '36\r\n\r\nPicture - https://photos.app.goo.gl/9MJyhbviAzmdRkdn7\r\n\r\nDo not Cancel The Order, Please Have Patience. We Will Respond As Soon As We see It [ If Not Sleeping ]\r\n\r\nAll types of accounts are available, please inbox us your budget and requirements.\r\n\r\nWHAT WILL YOU GET AFTER PLACING AN ORDER?\r\n\r\n- Email linked to Supercell ID & password [Full Email Access] or if you want you can change account mail any time\r\n\r\n-You \'ll have aGuidelines to secure your account\r\n\r\n_\r\n\r\n-SAFE AND SECURE ACCOUNT PURCHASES FROM MY STORE\r\n\r\nAuthentic Accounts: Thoroughly tested and secure accounts.\r\n\r\nComprehensive Guarantee: Sourced directly from original owners for authenticity.\r\n\r\nPeace of Mind: Complete guarantee and quick issue resolution.\r\n\r\nExclusive Ownership: You will have complete ownership of the purchased account.\r\n\r\n_\r\n\r\n! IMPORTANT ACCOUNT GUIDELINES TO FOLLOW:\r\n\r\nWe will help you secure details.\r\n\r\nKeep your account private and avoid sharing it\r\n\r\nDo not contact Supercell Help and suppport.\r\n\r\nDelay name changes for 12 hours at least and in-game purchases for 3-7 days.\r\n\r\n37$', '17', '100', '100', '75', '50', 0, '\"[{\\\"title\\\":null,\\\"link\\\":\\\"https:\\\\\\/\\\\\\/www.dropbox.com\\\\\\/scl\\\\\\/fi\\\\\\/b1h2v9l4wqptfcj8kv7sy\\\\\\/InCollage_20250702_164231484.jpg?rlkey=85jh8pkdowxtnr1ibcihzjg6m&st=wl6gw5e4&raw=1\\\"}]\"', 'USD', 37.00, '\"{\\\"method\\\":\\\"manual\\\",\\\"quantity_from\\\":\\\"1\\\",\\\"speed_hour\\\":\\\"0\\\",\\\"speed_min\\\":\\\"10\\\"}\"', 0, NULL, 1, 1, 0, NULL, 'Global', '2025-09-30 23:59:17', '2025-10-01 15:22:39', NULL, 1);

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
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `group` varchar(255) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `group`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'create role', 'Role Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(2, 'view role', 'Role Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(3, 'update role', 'Role Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(4, 'delete role', 'Role Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(5, 'create user', 'User Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(6, 'view user', 'User Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(7, 'update user', 'User Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(8, 'delete user', 'User Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(9, 'create permission', 'Permission Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(10, 'view permission', 'Permission Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(11, 'update permission', 'Permission Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(12, 'delete permission', 'Permission Management', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(2, 'admin', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(3, 'staff', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49'),
(4, 'user', 'web', '2025-09-14 11:12:49', '2025-09-14 11:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(5, 1),
(5, 2),
(6, 1),
(6, 2),
(7, 1),
(7, 2),
(8, 1),
(9, 1),
(9, 2),
(10, 1),
(10, 2),
(11, 1),
(12, 1);

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
('0GTLhHAeCMTekIga6Sm9lm7Jum32yW5wyTxGVwcU', NULL, '103.15.42.199', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicFVUUTNjVTlaOXFnUEROWVhnM0xMN3lHNzlrV1JFWEVxSXIxYlBjdCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzI6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5ei9vZmZlci1sb2dzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759298954),
('3esuip7kYWLdqXJG1UO9rTJ6lSWNaiAVxTmIHsNb', NULL, '3.124.204.9', 'CheckMarkNetwork/1.0 (+http://www.checkmarknetwork.com/spider.html)', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiajcxMVAyTlo4RXJsdjZhRmlWbG1DeGthbjBEck45cmhxYjJaNzlsViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759291924),
('3KT7lFPod4cGfTFQBLGNqqbXLqpqvlfsslkjQQg0', NULL, '34.168.145.115', 'Mozilla/5.0 (Linux; Android 12; Pixel 6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS3RGcHJnTGJBcHNIaFNpTHVYd3VSVUhpV0JYUjFKa1NMdFlXa1dhRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vbWFpbC5jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759292402),
('dzYyQRNSSnOs3CVrbaaroLZMex0aV7vyy0FD0Mrl', NULL, '34.229.118.171', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV2RkS3QwSmdpVU4yaEpLbU9aVURaVm84RTN4eVF6NmlaSDhnRE5OcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5eiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759296945),
('gU0vdaPN6GB6r9fOt7zmandYEAwT4DFGTjkBsE1d', NULL, '13.219.240.46', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6.1 Safari/605.1.15', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRFoxZHV6TXhSMG5KTVlXaHNJVmh6NUJ6NVY1WEttbHpFMW51VWU0aiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5eiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759295345),
('imVGAdNhWbbePIAiHupCAneeqeosFlzEQUkXDXPP', NULL, '16.163.123.244', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQTdVd3JtZ3hXdlMzSzlWRk1DZ0JTSXRzeUFzNGJXYjZhbmJORzA0diI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5eiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759300110),
('ivjZPU9MIFiLE1uerhNUWsJfzzohU9hyxIn0Fe8u', 1, '43.245.120.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWXBwN3ZBWjUybmdiMEhLcnM0Y0NrQzBPdTBxWmhCMWljSFRDWDNhMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5ei9vZmZlci10ZW1wbGF0ZXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1759297045),
('JRWKj770m7HhGRA2GAcjcyiyIfrrrkPQp6RgXQw3', NULL, '103.15.42.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVXVacWFsb3ExRzhnanNSNjJvMERScjdNenFtaTRhWnptRVBmWHdrTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5ei9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1759298899),
('lOyBjeC1mFu6BnndIpoPO5goGbeBnJv5vznsoZ4H', 1, '45.248.151.244', 'Mozilla/5.0 (X11; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVDd1ZDA0RDJwSTJqNzhqeG5iQUtRNDg2ejNsR0hqTFNCYk5TZ3dnaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5ei9vZmZlci10ZW1wbGF0ZXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1759310612),
('lxhskXYnNJ3maZfESnljO9MqSa00QUEamXMytE87', NULL, '45.90.63.129', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOG1NVVdZcHh1bXU5SDFHdkJlYzlVVG1yT0s4dWNwT010T0tqa0pFeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759299777),
('ocaELsLvGKgvx96vcwv28SUa3q87iW4hjF4BmhGt', NULL, '16.163.123.244', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTXVkVDVucVVEcUtzMWU0cGdrVTluTDZrbEt3N05DSlNVNjZPazd0ZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5eiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759299917),
('S2qYe5niUu1f6mgdY6bo578D7oit7yfZARCV2IyO', NULL, '34.203.38.233', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUThISXZremROalVIQWZsZ0FxSnpiMFpLQ0tDNUFwUHlwUkQ4cnkyNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759297850),
('snWfE3r4CksBS4EV8eCDx8FA83xavd4gvA2UUvIL', NULL, '77.110.103.217', 'python-requests/2.32.5', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMzl0ajFaZ01UWnJOekprM3QzU0hzZVJRamNyUUdDc3hnM3J2NHg0aCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5eiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759310306),
('TgLY1IDsbYRsQtXKRbUKvn0J557RX9FqgZnpzy0d', NULL, '44.222.76.21', 'Mozilla/5.0 (Kubuntu; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNzRjRjVIdUh0SXUwY3hHWFZ2WnZJZXdGVk93UmVqV3NtQWxqWEw1dyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5eiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759296644),
('TqfmyKP7yZjIg56Lc5pBj25bEBpqFEtxVfKjsYh3', 1, '43.245.120.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWnplSnR4SkRLR1prODRkSWM2TGtZeUxlN0JHTmdoUW9FWVdhbzBWQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5ei9vZmZlci10ZW1wbGF0ZXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1759310102),
('w72beA3116GvEexDM3b7jTRuECR1lrIh8oQLlkR7', NULL, '103.15.42.69', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTjhGRFJ5WkxOd1ltdXcwR3FnM2k1S2ZQSWdlVUFvMjVodGRaTDNyaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5ei9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759298900),
('WbjCtOzck1np3I3mni4faDQX7cRgMEq1UDVw2mKy', 1, '103.190.133.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVmFoQzJBTHlRcm5RU0ZIc0g3a3VoRDRITzhJOXpBUVhSZVVHM3FTNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5ei9wdWJsaWMvc2V0dGluZ3Mvb3JnYW5pemF0aW9uIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1759299898),
('whbC5paCqNBjdD48zmLYtqvA8GLCgPc0mjooecJi', NULL, '3.124.204.9', 'CheckMarkNetwork/1.0 (+http://www.checkmarknetwork.com/spider.html)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR05UYzBER0t1b2F5YVhjQU11ckNSbU1iNzZuT0hPenlyVHV4M0RCYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759291924),
('WnNU6eGVMDRdCOXU3l1rgWGbJbtD6KvhUubPFR6v', NULL, '44.222.76.21', 'Mozilla/5.0 (Ubuntu; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidEdERkFOTWhTM2V2aVA1emxNS0ZLbGxNVDJOUXBQS09ZT2c2Q1lRQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759296650),
('Y72Bqh2oqx1jKQafKUUOZiJSHyGJhQw70HfJUZvn', NULL, '84.201.183.102', 'Go-http-client/1.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZGJtUG9kTE1TM0xXU3lmODlZazhsa1Nzb1RScUY2WUdwTU1NS2RXWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759300289),
('yb1j9GQYJtyQo9VuT4uDlhVIYbGgJnxWsLTWPNLL', NULL, '116.203.34.44', 'Mozilla/5.0 (X11; Linux x86_64) Chrome/19.0.1084.9 Safari/536.5', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQlp0OVJIaHN0azhEclRrSWw4Q3F1M0s0WERrMlhYa1YzNXBnNHdjUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759301280),
('YfZ6qGEjFt1adJjOcpfP6Ur12aFzSbL4e1gFOqKF', NULL, '141.95.175.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZm1CeFVOcXNBVjNvUkxEOHRWRE9wYzF6Y0dRaG1NcUNNMGE5V2szRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759293427),
('Z8XeqnYxgaRiZ2jWWguflTSDrDC8VnFOSshnAgo7', NULL, '13.219.240.46', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSGlmZFcwMjA0WHdISnlBWUdJOEVyblZuS1gxcXpHb3hndm1xNlZNbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9jbGFzaHNocDcueHl6Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759295350),
('Zwjum7UTMtCr8AershDTs45D5WoKJEgkuo03o5nc', NULL, '202.173.122.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYzZqaDloWkxuUDVBY3ZjZ2xnUVowNEJidUdYc2lybWdFcVNUR2swbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHBzOi8vY2xhc2hzaHA3Lnh5ei9sZXZlbHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1759299132);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@gmail.com', NULL, '$2y$12$INhl1uO76GcRZbTYiYGpNu5yxTTYiRcbCeJ49sY/LAQ5AJl69WmZa', 'FfnnGKpz4mvLqKLyjoYZtiyYvQMkDSJcdoO8pGvSacU96JDBEYZDs1OWWu08', '2025-09-14 11:12:50', '2025-09-14 11:12:50'),
(2, 'Admin', 'admin@gmail.com', NULL, '$2y$12$FgMaEDD2D2qo.CcFm.cN9.TgXxIKaWrJ5kN//C/iZthTQtd9WyLRa', NULL, '2025-09-14 11:12:50', '2025-09-14 11:12:50'),
(3, 'Staff', 'staff@gmail.com', NULL, '$2y$12$HO7MrMSf/o/JUEEDz7y8Ue1qWMrlJkEZWMfGGpf8HoJCFvYiuRA8i', NULL, '2025-09-14 11:12:50', '2025-09-14 11:12:50');

-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

CREATE TABLE `user_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_generated_cookies` tinyint(1) NOT NULL DEFAULT 0,
  `cookies_file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_accounts`
--

INSERT INTO `user_accounts` (`id`, `owner_name`, `email`, `password`, `is_generated_cookies`, `cookies_file_path`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'recoveryth0000@gmail.com', 'eyJpdiI6IjdRSllHNGVnMEhabU5OQlN2UmJxWnc9PSIsInZhbHVlIjoicklFUE1PZkh3WHgybHpGU2FiVnZDZz09IiwibWFjIjoiYjBhNzFhNjI5OGE5ZWQ1YTU2ZTYwYWI2OGZmMWQ2OTA2NGRlMTMyM2JjNmMzYTgyNzQxOTJmNmIzNzIwNzVhMSIsInRhZyI6IiJ9', 1, 'g2g_auth_state.json', '2025-09-25 10:09:03', '2025-09-25 10:09:03'),
(2, 'Tanbir Ahmed', 'developertanbir1@gmail.com', 'eyJpdiI6IklkbmRrZnNJUWhzUnR3SFdpYjFjUGc9PSIsInZhbHVlIjoiN0F3TFF3ZGliQVlmdjF5MmhCRVFTZz09IiwibWFjIjoiMjI4ZDM0YTAyNjM0MzI4MWUwMmNjYzZhZjM3NWJmOGEzMmJmMGVkYjE1NGY5ZWI5OTE3N2VjNTNjZmFmMmQ4NyIsInRhZyI6IiJ9', 1, NULL, '2025-09-29 05:03:03', '2025-10-01 10:18:25'),
(3, 'mdshalum2016', 'mdshalum2016@gmail.com', 'eyJpdiI6InozUy9qamJGa3lMaWlMaCs5d2I5NFE9PSIsInZhbHVlIjoiS0h6WjNqK0RPNVdzVzdrMzgwN3pOQT09IiwibWFjIjoiZmUxMGI0NjY2OWUwYjMxOTVhOGMyYTUyYzc1NTczNWQ5OTI4NTRlZGE5OWMyNzBjODg2NzlkYzIxMWMyYzA3NCIsInRhZyI6IiJ9', 0, NULL, '2025-09-30 18:55:04', '2025-09-30 18:55:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application_setups`
--
ALTER TABLE `application_setups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

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
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `offer_automation_logs`
--
ALTER TABLE `offer_automation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offer_automation_logs_offer_template_id_foreign` (`offer_template_id`),
  ADD KEY `offer_automation_logs_status_index` (`status`),
  ADD KEY `offer_automation_logs_executed_at_index` (`executed_at`),
  ADD KEY `offer_automation_logs_created_at_index` (`created_at`);

--
-- Indexes for table `offer_schedulers`
--
ALTER TABLE `offer_schedulers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offer_schedulers_offer_template_id_is_active_index` (`offer_template_id`,`is_active`);

--
-- Indexes for table `offer_templates`
--
ALTER TABLE `offer_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offer_templates_user_account_id_foreign` (`user_account_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_accounts_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application_setups`
--
ALTER TABLE `application_setups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=732;

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `offer_automation_logs`
--
ALTER TABLE `offer_automation_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offer_schedulers`
--
ALTER TABLE `offer_schedulers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offer_templates`
--
ALTER TABLE `offer_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offer_automation_logs`
--
ALTER TABLE `offer_automation_logs`
  ADD CONSTRAINT `offer_automation_logs_offer_template_id_foreign` FOREIGN KEY (`offer_template_id`) REFERENCES `offer_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offer_schedulers`
--
ALTER TABLE `offer_schedulers`
  ADD CONSTRAINT `offer_schedulers_offer_template_id_foreign` FOREIGN KEY (`offer_template_id`) REFERENCES `offer_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offer_templates`
--
ALTER TABLE `offer_templates`
  ADD CONSTRAINT `offer_templates_user_account_id_foreign` FOREIGN KEY (`user_account_id`) REFERENCES `user_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
