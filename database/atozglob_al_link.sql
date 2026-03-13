-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 10, 2026 at 11:19 AM
-- Server version: 8.0.45
-- PHP Version: 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `atozglob_al_link`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int UNSIGNED NOT NULL,
  `role_id` int UNSIGNED DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `role_id`, `username`, `email`, `password_hash`, `is_active`, `last_login`, `created_at`) VALUES
(1, 1, 'atoz', 'admin@atozglobal.com', '$2y$10$wv7Kxsjbj3h2gpvKvHP8oOOe6Ul0wig.6NI9C9kyD0kXnzWyrPJK.', 1, '2026-02-10 09:17:45', '2026-01-31 03:58:40'),
(2, 2, 'queenmanager', 'queenmanager@gmail.com', '$2y$10$tAIq92kOBboPTXPedQX64.6JLicf6RzY2MXd3KXi977xejU9O7ram', 1, '2026-02-06 07:13:08', '2026-02-06 07:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int UNSIGNED NOT NULL,
  `permission_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `permission_name`, `permission_slug`, `module`, `description`) VALUES
(1, 'View Dashboard', 'view_dashboard', 'dashboard', NULL),
(2, 'View Applications', 'view_applications', 'applications', NULL),
(3, 'Create Applications', 'create_applications', 'applications', NULL),
(4, 'Edit Applications', 'edit_applications', 'applications', NULL),
(5, 'Delete Applications', 'delete_applications', 'applications', NULL),
(6, 'Change Application Status', 'change_application_status', 'applications', NULL),
(7, 'View Users', 'view_users', 'users', NULL),
(8, 'Create Users', 'create_users', 'users', NULL),
(9, 'Edit Users', 'edit_users', 'users', NULL),
(10, 'Delete Users', 'delete_users', 'users', NULL),
(11, 'View Services', 'view_services', 'services', NULL),
(12, 'Create Services', 'create_services', 'services', NULL),
(13, 'Edit Services', 'edit_services', 'services', NULL),
(14, 'Delete Services', 'delete_services', 'services', NULL),
(15, 'View Categories', 'view_categories', 'categories', NULL),
(16, 'Manage Categories', 'manage_categories', 'categories', NULL),
(17, 'View Countries', 'view_countries', 'countries', NULL),
(18, 'Manage Countries', 'manage_countries', 'countries', NULL),
(19, 'View Destinations', 'view_destinations', 'destinations', NULL),
(20, 'Manage Destinations', 'manage_destinations', 'destinations', NULL),
(21, 'View Payments', 'view_payments', 'payments', NULL),
(22, 'Manage Payments', 'manage_payments', 'payments', NULL),
(23, 'View Roles', 'view_roles', 'roles', NULL),
(24, 'Manage Roles', 'manage_roles', 'roles', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `id` int UNSIGNED NOT NULL,
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_roles`
--

INSERT INTO `admin_roles` (`id`, `role_name`, `role_slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super_admin', 'Full system access with all permissions', 1, '2026-02-06 06:43:06', '2026-02-06 06:43:06'),
(2, 'Manager', 'manager', 'Can manage applications, users, and services', 1, '2026-02-06 06:43:06', '2026-02-06 06:43:06'),
(3, 'Support Staff', 'support_staff', 'Can view and update applications', 1, '2026-02-06 06:43:06', '2026-02-06 06:43:06'),
(4, 'Accountant', 'accountant', 'Can manage payments and financial records', 1, '2026-02-06 06:43:06', '2026-02-06 06:43:06'),
(5, 'Viewer', 'viewer', 'Read-only access to system data', 1, '2026-02-06 06:43:06', '2026-02-06 06:43:06');

-- --------------------------------------------------------

--
-- Table structure for table `admin_role_permissions`
--

CREATE TABLE `admin_role_permissions` (
  `id` int UNSIGNED NOT NULL,
  `role_id` int UNSIGNED NOT NULL,
  `permission_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_role_permissions`
--

INSERT INTO `admin_role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(47, 3, 6, '2026-02-06 06:43:06'),
(48, 3, 4, '2026-02-06 06:43:06'),
(49, 3, 2, '2026-02-06 06:43:06'),
(50, 3, 1, '2026-02-06 06:43:06'),
(51, 3, 11, '2026-02-06 06:43:06'),
(52, 3, 7, '2026-02-06 06:43:06'),
(54, 4, 22, '2026-02-06 06:43:06'),
(55, 4, 2, '2026-02-06 06:43:06'),
(56, 4, 1, '2026-02-06 06:43:06'),
(57, 4, 21, '2026-02-06 06:43:06'),
(58, 4, 7, '2026-02-06 06:43:06'),
(61, 5, 2, '2026-02-06 06:43:06'),
(62, 5, 15, '2026-02-06 06:43:06'),
(63, 5, 17, '2026-02-06 06:43:06'),
(64, 5, 1, '2026-02-06 06:43:06'),
(65, 5, 19, '2026-02-06 06:43:06'),
(66, 5, 21, '2026-02-06 06:43:06'),
(67, 5, 23, '2026-02-06 06:43:06'),
(68, 5, 11, '2026-02-06 06:43:06'),
(69, 5, 7, '2026-02-06 06:43:06'),
(76, 1, 6, '2026-02-06 07:24:47'),
(77, 1, 3, '2026-02-06 07:24:47'),
(78, 1, 5, '2026-02-06 07:24:47'),
(79, 1, 4, '2026-02-06 07:24:47'),
(80, 1, 2, '2026-02-06 07:24:47'),
(81, 1, 1, '2026-02-06 07:24:47'),
(82, 1, 12, '2026-02-06 07:24:47'),
(83, 1, 13, '2026-02-06 07:24:47'),
(84, 1, 8, '2026-02-06 07:24:47'),
(85, 1, 10, '2026-02-06 07:24:47'),
(86, 1, 9, '2026-02-06 07:24:47'),
(87, 1, 7, '2026-02-06 07:24:47'),
(88, 1, 15, '2026-02-06 07:32:02'),
(89, 1, 16, '2026-02-06 07:32:02'),
(90, 1, 17, '2026-02-06 07:32:02'),
(91, 1, 18, '2026-02-06 07:32:02'),
(92, 1, 19, '2026-02-06 07:32:02'),
(93, 1, 20, '2026-02-06 07:32:02'),
(94, 1, 21, '2026-02-06 07:32:02'),
(95, 1, 22, '2026-02-06 07:32:02'),
(96, 1, 23, '2026-02-06 07:32:02'),
(97, 1, 24, '2026-02-06 07:32:02'),
(98, 1, 11, '2026-02-06 07:32:02'),
(99, 1, 14, '2026-02-06 07:32:02'),
(194, 2, 6, '2026-02-09 15:28:05'),
(195, 2, 3, '2026-02-09 15:28:05'),
(196, 2, 4, '2026-02-09 15:28:05'),
(197, 2, 2, '2026-02-09 15:28:05'),
(198, 2, 15, '2026-02-09 15:28:05'),
(199, 2, 17, '2026-02-09 15:28:05'),
(200, 2, 1, '2026-02-09 15:28:05'),
(201, 2, 19, '2026-02-09 15:28:05'),
(202, 2, 22, '2026-02-09 15:28:05'),
(203, 2, 21, '2026-02-09 15:28:05'),
(204, 2, 13, '2026-02-09 15:28:05'),
(205, 2, 11, '2026-02-09 15:28:05'),
(206, 2, 8, '2026-02-09 15:28:05'),
(207, 2, 9, '2026-02-09 15:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int UNSIGNED NOT NULL,
  `application_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `application_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_type` enum('online','manual') COLLATE utf8mb4_unicode_ci DEFAULT 'online',
  `user_id` int UNSIGNED NOT NULL,
  `service_id` int UNSIGNED NOT NULL,
  `status_id` int UNSIGNED NOT NULL,
  `destination_country_id` int UNSIGNED DEFAULT NULL,
  `travel_purpose` text COLLATE utf8mb4_unicode_ci,
  `intended_travel_date` date DEFAULT NULL,
  `duration_of_stay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_amount` decimal(10,2) NOT NULL,
  `additional_fees` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `payment_plan` enum('full','partial') COLLATE utf8mb4_unicode_ci DEFAULT 'full',
  `initial_payment_amount` decimal(10,2) DEFAULT '0.00',
  `remaining_payment_amount` decimal(10,2) DEFAULT '0.00',
  `amount_paid_so_far` decimal(10,2) DEFAULT '0.00',
  `payment_completion_percentage` decimal(5,2) DEFAULT '0.00',
  `initial_payment_percentage` decimal(5,2) DEFAULT '0.00',
  `remaining_payment_percentage` decimal(5,2) DEFAULT '0.00',
  `initial_payment_status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `final_payment_status` enum('pending','completed','failed','not_required') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'not_required',
  `payment_proof` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initial_payment_proof` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_payment_proof` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initial_transaction_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_transaction_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method_id` int UNSIGNED DEFAULT NULL,
  `transaction_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('pending','proof_submitted','under_review','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `application_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `internal_notes` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `refund_status` enum('none','requested','approved','processed') COLLATE utf8mb4_unicode_ci DEFAULT 'none'
) ;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `application_uuid`, `application_number`, `application_type`, `user_id`, `service_id`, `status_id`, `destination_country_id`, `travel_purpose`, `intended_travel_date`, `duration_of_stay`, `base_amount`, `additional_fees`, `discount_amount`, `total_amount`, `payment_plan`, `initial_payment_amount`, `remaining_payment_amount`, `amount_paid_so_far`, `payment_completion_percentage`, `initial_payment_percentage`, `remaining_payment_percentage`, `initial_payment_status`, `final_payment_status`, `payment_proof`, `initial_payment_proof`, `final_payment_proof`, `initial_transaction_reference`, `final_transaction_reference`, `payment_method_id`, `transaction_reference`, `payment_status`, `currency`, `application_data`, `internal_notes`, `submitted_at`, `last_updated_at`, `completed_at`, `refund_status`) VALUES
(114, 'd32adec2-0382-11f1-a30f-fa163e8467db', 'ATZ20266746', 'online', 125, 6, 3, NULL, '', NULL, '', 2500.00, 0.00, 0.00, 2500.00, 'full', 2500.00, 0.00, 0.00, 0.00, 100.00, 0.00, 'completed', 'not_required', 'uploads/payments/69862c070cada.pdf', NULL, NULL, NULL, NULL, 1, 'I will pay after Visa approved ', 'pending', 'USD', '{\"additional_info\":\"\"}', NULL, '2026-02-06 17:39:44', '2026-02-06 17:59:35', NULL, 'none'),
(115, '18e57723-039e-11f1-a30f-fa163e8467db', 'ATZ20264533', 'online', 165, 4, 2, 183, 'Work', '2026-03-15', '2 years', 1000.00, 0.00, 0.00, 1000.00, 'partial', 1.00, 999.00, 0.00, 0.00, 0.10, 99.90, 'completed', 'pending', NULL, 'uploads/payments/69865717d82aa.png', NULL, NULL, NULL, 1, '25913996523', 'pending', 'USD', '{\"additional_info\":\"\"}', NULL, '2026-02-06 20:54:58', '2026-02-09 15:23:57', NULL, 'none'),
(120, '84f922cc-03f7-11f1-93cf-fa163e8467db', 'ATZ20266747', 'manual', 21, 12, 2, 122, 'Working', '2026-06-01', '', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 0.00, 0.00, 20.00, 80.00, 'pending', 'not_required', NULL, NULL, NULL, NULL, NULL, 5, '', 'pending', 'USD', NULL, NULL, '2026-02-07 07:35:04', '2026-02-07 07:35:04', NULL, 'none'),
(121, '7d58c19e-0415-11f1-93cf-fa163e8467db', 'ATZ20266748', 'manual', 166, 4, 2, 183, 'WORK ID', '2026-02-22', '2 YEARS', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 1, '', 'pending', 'USD', NULL, NULL, '2026-02-07 11:09:36', '2026-02-09 12:08:04', NULL, 'none'),
(122, '96616a14-0418-11f1-93cf-fa163e8467db', 'ATZ20266749', 'manual', 167, 4, 2, 183, 'WORKING', '2026-02-22', '2 YEARS', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 1, '', 'pending', 'USD', NULL, NULL, '2026-02-07 11:31:47', '2026-02-09 12:07:37', NULL, 'none'),
(123, 'a9a1c4d6-0419-11f1-93cf-fa163e8467db', 'ATZ20266750', 'manual', 168, 4, 2, 183, 'WORK', '2026-01-31', '2 YEARS ', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 5, '', 'pending', 'USD', NULL, NULL, '2026-02-07 11:39:29', '2026-02-09 13:38:28', NULL, 'none'),
(124, 'a99a55f1-041a-11f1-93cf-fa163e8467db', 'ATZ20266751', 'manual', 112, 12, 2, 122, 'work', '2026-01-03', '2 years ', 2500.00, 0.00, 0.00, 2500.00, 'partial', 344.00, 2156.00, 344.00, 13.76, 13.76, 86.24, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 5, '', 'pending', 'USD', NULL, NULL, '2026-02-07 11:46:38', '2026-02-09 12:03:54', NULL, 'none'),
(125, '19ad0d1b-041b-11f1-93cf-fa163e8467db', 'ATZ20266752', 'manual', 169, 4, 2, 183, 'WORKING', '2026-02-22', '2 YEARS', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 1, '', 'pending', 'USD', NULL, NULL, '2026-02-07 11:49:46', '2026-02-09 13:34:53', NULL, 'none'),
(126, '38e43250-041d-11f1-93cf-fa163e8467db', 'ATZ20266753', 'manual', 172, 4, 2, 183, 'WORKING', '2026-02-22', '2 YEARS', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 1, '', 'pending', 'USD', NULL, NULL, '2026-02-07 12:04:58', '2026-02-09 13:37:04', NULL, 'none'),
(127, '8ef4bcbd-0427-11f1-93cf-fa163e8467db', 'ATZ20266754', 'manual', 173, 4, 2, 183, 'WORKING', '2026-02-22', '2 YEARS', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 1, '', 'pending', 'USD', NULL, NULL, '2026-02-07 13:18:57', '2026-02-09 14:06:16', NULL, 'none'),
(128, 'fda1e0a1-0430-11f1-93cf-fa163e8467db', 'ATZ20266755', 'manual', 175, 4, 2, 183, 'WORK', '2026-01-01', '2 YEARS ', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 5, '', 'pending', 'USD', NULL, NULL, '2026-02-07 14:26:28', '2026-02-09 13:36:35', NULL, 'none'),
(129, '3f672bb7-0439-11f1-93cf-fa163e8467db', 'ATZ20266756', 'manual', 176, 4, 2, 183, 'WORKING', '2026-02-22', '2 YEARS', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, NULL, NULL, 1, '', 'pending', 'USD', NULL, NULL, '2026-02-07 15:25:34', '2026-02-09 13:35:41', NULL, 'none'),
(158, '8bee54b9-05b6-11f1-93cf-fa163e8467db', 'ATZ20266757', 'manual', 154, 14, 5, 122, 'working', '2026-06-01', '2 years', 6000.00, 0.00, 0.00, 6000.00, 'partial', 2000.00, 4000.00, 2000.00, 33.33, 33.33, 66.67, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 12:55:01', '2026-02-09 12:56:35', NULL, 'none'),
(161, 'bc72eb36-05bb-11f1-93cf-fa163e8467db', 'ATZ20266758', 'manual', 155, 13, 5, 8, 'Working', '2026-06-01', '2 years', 3000.00, 0.00, 0.00, 3000.00, 'partial', 500.00, 2500.00, 500.00, 16.67, 16.67, 83.33, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 13:32:10', '2026-02-09 13:32:46', NULL, 'none'),
(162, '4920166d-05bc-11f1-93cf-fa163e8467db', 'ATZ20266759', 'manual', 156, 6, 5, 99, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 13:36:06', '2026-02-09 13:36:51', NULL, 'none'),
(163, '2e930184-05bd-11f1-93cf-fa163e8467db', 'ATZ20266760', 'manual', 24, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 350.00, 2150.00, 350.00, 14.00, 14.00, 86.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 13:42:31', '2026-02-09 13:43:15', NULL, 'none'),
(164, '8ea5a5cf-05bd-11f1-93cf-fa163e8467db', 'ATZ20266761', 'manual', 25, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 350.00, 2150.00, 350.00, 14.00, 14.00, 86.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 13:45:12', '2026-02-09 13:47:34', NULL, 'none'),
(165, '80e4b6f4-05be-11f1-93cf-fa163e8467db', 'ATZ20266762', 'manual', 21, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 13:51:58', '2026-02-09 13:53:03', NULL, 'none'),
(166, '2f49d682-05bf-11f1-93cf-fa163e8467db', 'ATZ20266763', 'manual', 26, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 1000.00, 1500.00, 1000.00, 40.00, 40.00, 60.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 13:56:51', '2026-02-09 13:57:28', NULL, 'none'),
(167, '8c0c2ae5-05bf-11f1-93cf-fa163e8467db', 'ATZ20266764', 'manual', 27, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 13:59:27', '2026-02-09 14:00:09', NULL, 'none'),
(168, '1b97ada5-05c0-11f1-93cf-fa163e8467db', 'ATZ20266765', 'manual', 30, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:03:27', '2026-02-09 14:04:01', NULL, 'none'),
(169, 'f8412e1c-05c0-11f1-93cf-fa163e8467db', 'ATZ20266766', 'manual', 29, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:09:38', '2026-02-09 14:10:32', NULL, 'none'),
(170, 'a8757ae0-05c1-11f1-93cf-fa163e8467db', 'ATZ20266767', 'manual', 31, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:14:33', '2026-02-09 14:15:10', NULL, 'none'),
(171, '46fa9c33-05c2-11f1-93cf-fa163e8467db', 'ATZ20266768', 'manual', 32, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:18:59', '2026-02-09 14:19:31', NULL, 'none'),
(172, 'b158769c-05c2-11f1-93cf-fa163e8467db', 'ATZ20266769', 'manual', 33, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:21:58', '2026-02-09 14:22:36', NULL, 'none'),
(173, '9c756f3f-05c4-11f1-93cf-fa163e8467db', 'ATZ20266770', 'manual', 34, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:35:42', '2026-02-09 14:36:21', NULL, 'none'),
(174, '2aa6d1b4-05c5-11f1-93cf-fa163e8467db', 'ATZ20266771', 'manual', 35, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:39:40', '2026-02-09 14:40:23', NULL, 'none'),
(175, 'bd0dbf1f-05c5-11f1-93cf-fa163e8467db', 'ATZ20266772', 'manual', 36, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:43:46', '2026-02-09 14:44:23', NULL, 'none'),
(176, '831c98b0-05c6-11f1-93cf-fa163e8467db', 'ATZ20266773', 'manual', 37, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 14:49:18', '2026-02-09 14:49:51', NULL, 'none'),
(177, '1e547708-05c9-11f1-93cf-fa163e8467db', 'ATZ20266774', 'manual', 38, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 479.00, 2021.00, 479.00, 19.16, 19.16, 80.84, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:07:58', '2026-02-09 15:08:47', NULL, 'none'),
(178, 'aa6ab97b-05c9-11f1-93cf-fa163e8467db', 'ATZ20266775', 'manual', 39, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:11:53', '2026-02-09 15:13:01', NULL, 'none'),
(179, '5b62095d-05cc-11f1-93cf-fa163e8467db', 'ATZ20266776', 'manual', 181, 4, 5, 183, 'Working', '2026-06-01', '2 years', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:31:08', '2026-02-09 15:36:57', NULL, 'none'),
(180, 'a58f3a29-05cc-11f1-93cf-fa163e8467db', 'ATZ20266777', 'manual', 184, 4, 5, 183, 'Working', '2026-06-01', '2 years', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:33:13', '2026-02-09 15:38:03', NULL, 'none'),
(181, 'b302df31-05cd-11f1-93cf-fa163e8467db', 'ATZ20266778', 'manual', 40, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 344.00, 2156.00, 344.00, 13.76, 13.76, 86.24, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:40:45', '2026-02-09 15:41:16', NULL, 'none'),
(182, '416d6345-05ce-11f1-93cf-fa163e8467db', 'ATZ20266779', 'manual', 41, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:44:44', '2026-02-09 15:45:15', NULL, 'none'),
(183, '9e3c6a79-05ce-11f1-93cf-fa163e8467db', 'ATZ20266780', 'manual', 42, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 344.00, 2156.00, 344.00, 13.76, 13.76, 86.24, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:47:20', '2026-02-09 15:47:47', NULL, 'none'),
(184, '0d1f327a-05cf-11f1-93cf-fa163e8467db', 'ATZ20266781', 'manual', 43, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:50:26', '2026-02-09 15:51:00', NULL, 'none'),
(185, '80c5ea18-05cf-11f1-93cf-fa163e8467db', 'ATZ20266782', 'manual', 44, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 344.00, 2156.00, 344.00, 13.76, 13.76, 86.24, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:53:40', '2026-02-09 15:54:36', NULL, 'none'),
(186, '4fa1be41-05d0-11f1-93cf-fa163e8467db', 'ATZ20266783', 'manual', 45, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-09 15:59:27', '2026-02-09 15:59:55', NULL, 'none'),
(187, 'f7961f32-0653-11f1-93cf-fa163e8467db', 'ATZ20266784', 'manual', 46, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-10 07:41:53', '2026-02-10 07:42:51', NULL, 'none'),
(188, 'ce3cd901-0655-11f1-93cf-fa163e8467db', 'ATZ20266785', 'manual', 48, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-10 07:55:02', '2026-02-10 07:57:56', NULL, 'none'),
(189, 'a91ac443-0656-11f1-93cf-fa163e8467db', 'ATZ20266786', 'manual', 49, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-10 08:01:09', '2026-02-10 08:03:18', NULL, 'none'),
(190, 'd1974c95-065e-11f1-93cf-fa163e8467db', 'ATZ20266787', 'manual', 57, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 344.00, 2156.00, 344.00, 13.76, 13.76, 86.24, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-10 08:59:33', '2026-02-10 09:00:33', NULL, 'none'),
(191, 'b9fb7201-065f-11f1-93cf-fa163e8467db', 'ATZ20266788', 'manual', 58, 12, 5, 122, 'Working', '2026-06-01', '2 years', 2500.00, 0.00, 0.00, 2500.00, 'partial', 500.00, 2000.00, 500.00, 20.00, 20.00, 80.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-10 09:06:03', '2026-02-10 09:06:46', NULL, 'none'),
(192, '5da606d6-0660-11f1-93cf-fa163e8467db', 'ATZ20266789', 'manual', 185, 4, 2, 183, 'work', '2026-01-03', '24 months', 1000.00, 0.00, 0.00, 1000.00, 'partial', 0.00, 1000.00, 0.00, 0.00, 0.00, 100.00, 'completed', 'not_required', NULL, NULL, NULL, '', NULL, 5, NULL, 'approved', 'USD', NULL, NULL, '2026-02-10 09:10:38', '2026-02-10 09:10:52', NULL, 'none');

-- --------------------------------------------------------

--
-- Table structure for table `application_messages`
--

CREATE TABLE `application_messages` (
  `id` int UNSIGNED NOT NULL,
  `application_id` int UNSIGNED NOT NULL,
  `sender_type` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` int UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attachment_type` enum('text','image','pdf') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `attachment_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_statuses`
--

CREATE TABLE `application_statuses` (
  `id` int UNSIGNED NOT NULL,
  `status_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_final_status` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_statuses`
--

INSERT INTO `application_statuses` (`id`, `status_code`, `status_name`, `description`, `is_final_status`, `sort_order`, `created_at`) VALUES
(1, 'draft', 'Draft', 'Application is being prepared', 0, 1, '2026-01-31 03:58:40'),
(2, 'submitted', 'Submitted', 'Application has been submitted', 0, 2, '2026-01-31 03:58:40'),
(3, 'under_review', 'Under Review', 'Application is being reviewed by staff', 0, 3, '2026-01-31 03:58:40'),
(4, 'documents_required', 'Documents Required', 'Additional documents are needed', 0, 4, '2026-01-31 03:58:40'),
(5, 'processing', 'Processing', 'Application is being processed', 0, 5, '2026-01-31 03:58:40'),
(6, 'approved', 'Approved', 'Application has been approved', 0, 6, '2026-01-31 03:58:40'),
(7, 'rejected', 'Rejected', 'Application has been rejected', 1, 7, '2026-01-31 03:58:40'),
(8, 'completed', 'Completed', 'Service has been completed', 1, 8, '2026-01-31 03:58:40'),
(9, 'cancelled', 'Cancelled', 'Application has been cancelled', 1, 9, '2026-01-31 03:58:40');

-- --------------------------------------------------------

--
-- Table structure for table `application_status_history`
--

CREATE TABLE `application_status_history` (
  `id` int UNSIGNED NOT NULL,
  `application_id` int UNSIGNED NOT NULL,
  `from_status_id` int UNSIGNED DEFAULT NULL,
  `to_status_id` int UNSIGNED NOT NULL,
  `change_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int UNSIGNED NOT NULL,
  `user_type` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `admin_id` int UNSIGNED DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_type` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `replied` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_code_2` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_code_3` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visa_required_for_rwanda` tinyint(1) DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `iso_code_2`, `iso_code_3`, `phone_code`, `currency_code`, `region`, `visa_required_for_rwanda`, `is_active`, `created_at`) VALUES
(1, 'Afghanistan', 'AF', 'AFG', '+93', 'AFN', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(2, 'Albania', 'AL', 'ALB', '+355', 'ALL', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(3, 'Algeria', 'DZ', 'DZA', '+213', 'DZD', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(4, 'Andorra', 'AD', 'AND', '+376', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(5, 'Angola', 'AO', 'AGO', '+244', 'AOA', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(6, 'Argentina', 'AR', 'ARG', '+54', 'ARS', 'South America', 1, 1, '2026-01-31 04:02:36'),
(7, 'Armenia', 'AM', 'ARM', '+374', 'AMD', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(8, 'Australia', 'AU', 'AUS', '+61', 'AUD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(9, 'Austria', 'AT', 'AUT', '+43', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(10, 'Azerbaijan', 'AZ', 'AZE', '+994', 'AZN', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(11, 'Bahamas', 'BS', 'BHS', '+1242', 'BSD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(12, 'Bahrain', 'BH', 'BHR', '+973', 'BHD', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(13, 'Bangladesh', 'BD', 'BGD', '+880', 'BDT', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(14, 'Barbados', 'BB', 'BRB', '+1246', 'BBD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(15, 'Belarus', 'BY', 'BLR', '+375', 'BYN', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(16, 'Belgium', 'BE', 'BEL', '+32', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(17, 'Belize', 'BZ', 'BLZ', '+501', 'BZD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(18, 'Benin', 'BJ', 'BEN', '+229', 'XOF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(19, 'Bhutan', 'BT', 'BTN', '+975', 'BTN', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(20, 'Bolivia', 'BO', 'BOL', '+591', 'BOB', 'South America', 1, 1, '2026-01-31 04:02:36'),
(21, 'Bosnia and Herzegovina', 'BA', 'BIH', '+387', 'BAM', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(22, 'Botswana', 'BW', 'BWA', '+267', 'BWP', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(23, 'Brazil', 'BR', 'BRA', '+55', 'BRL', 'South America', 1, 1, '2026-01-31 04:02:36'),
(24, 'Brunei', 'BN', 'BRN', '+673', 'BND', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(25, 'Bulgaria', 'BG', 'BGR', '+359', 'BGN', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(26, 'Burkina Faso', 'BF', 'BFA', '+226', 'XOF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(27, 'Burundi', 'BI', 'BDI', '+257', 'BIF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(28, 'Cambodia', 'KH', 'KHM', '+855', 'KHR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(29, 'Cameroon', 'CM', 'CMR', '+237', 'XAF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(30, 'Canada', 'CA', 'CAN', '+1', 'CAD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(31, 'Cape Verde', 'CV', 'CPV', '+238', 'CVE', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(32, 'Central African Republic', 'CF', 'CAF', '+236', 'XAF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(33, 'Chad', 'TD', 'TCD', '+235', 'XAF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(34, 'Chile', 'CL', 'CHL', '+56', 'CLP', 'South America', 1, 1, '2026-01-31 04:02:36'),
(35, 'China', 'CN', 'CHN', '+86', 'CNY', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(36, 'Colombia', 'CO', 'COL', '+57', 'COP', 'South America', 1, 1, '2026-01-31 04:02:36'),
(37, 'Comoros', 'KM', 'COM', '+269', 'KMF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(38, 'Congo', 'CG', 'COG', '+242', 'XAF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(39, 'Costa Rica', 'CR', 'CRI', '+506', 'CRC', 'North America', 1, 1, '2026-01-31 04:02:36'),
(40, 'Croatia', 'HR', 'HRV', '+385', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(41, 'Cuba', 'CU', 'CUB', '+53', 'CUP', 'North America', 1, 1, '2026-01-31 04:02:36'),
(42, 'Cyprus', 'CY', 'CYP', '+357', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(43, 'Czech Republic', 'CZ', 'CZE', '+420', 'CZK', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(44, 'Democratic Republic of the Congo', 'CD', 'COD', '+243', 'CDF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(45, 'Denmark', 'DK', 'DNK', '+45', 'DKK', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(46, 'Djibouti', 'DJ', 'DJI', '+253', 'DJF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(47, 'Dominica', 'DM', 'DMA', '+1767', 'XCD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(48, 'Dominican Republic', 'DO', 'DOM', '+1809', 'DOP', 'North America', 1, 1, '2026-01-31 04:02:36'),
(49, 'Ecuador', 'EC', 'ECU', '+593', 'USD', 'South America', 1, 1, '2026-01-31 04:02:36'),
(50, 'Egypt', 'EG', 'EGY', '+20', 'EGP', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(51, 'El Salvador', 'SV', 'SLV', '+503', 'USD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(52, 'Equatorial Guinea', 'GQ', 'GNQ', '+240', 'XAF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(53, 'Eritrea', 'ER', 'ERI', '+291', 'ERN', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(54, 'Estonia', 'EE', 'EST', '+372', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(55, 'Eswatini', 'SZ', 'SWZ', '+268', 'SZL', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(56, 'Ethiopia', 'ET', 'ETH', '+251', 'ETB', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(57, 'Fiji', 'FJ', 'FJI', '+679', 'FJD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(58, 'Finland', 'FI', 'FIN', '+358', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(59, 'France', 'FR', 'FRA', '+33', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(60, 'Gabon', 'GA', 'GAB', '+241', 'XAF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(61, 'Gambia', 'GM', 'GMB', '+220', 'GMD', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(62, 'Georgia', 'GE', 'GEO', '+995', 'GEL', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(63, 'Germany', 'DE', 'DEU', '+49', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(64, 'Ghana', 'GH', 'GHA', '+233', 'GHS', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(65, 'Greece', 'GR', 'GRC', '+30', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(66, 'Grenada', 'GD', 'GRD', '+1473', 'XCD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(67, 'Guatemala', 'GT', 'GTM', '+502', 'GTQ', 'North America', 1, 1, '2026-01-31 04:02:36'),
(68, 'Guinea', 'GN', 'GIN', '+224', 'GNF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(69, 'Guinea-Bissau', 'GW', 'GNB', '+245', 'XOF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(70, 'Guyana', 'GY', 'GUY', '+592', 'GYD', 'South America', 1, 1, '2026-01-31 04:02:36'),
(71, 'Haiti', 'HT', 'HTI', '+509', 'HTG', 'North America', 1, 1, '2026-01-31 04:02:36'),
(72, 'Honduras', 'HN', 'HND', '+504', 'HNL', 'North America', 1, 1, '2026-01-31 04:02:36'),
(73, 'Hungary', 'HU', 'HUN', '+36', 'HUF', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(74, 'Iceland', 'IS', 'ISL', '+354', 'ISK', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(75, 'India', 'IN', 'IND', '+91', 'INR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(76, 'Indonesia', 'ID', 'IDN', '+62', 'IDR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(77, 'Iran', 'IR', 'IRN', '+98', 'IRR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(78, 'Iraq', 'IQ', 'IRQ', '+964', 'IQD', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(79, 'Ireland', 'IE', 'IRL', '+353', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(80, 'Israel', 'IL', 'ISR', '+972', 'ILS', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(81, 'Italy', 'IT', 'ITA', '+39', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(82, 'Ivory Coast', 'CI', 'CIV', '+225', 'XOF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(83, 'Jamaica', 'JM', 'JAM', '+1876', 'JMD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(84, 'Japan', 'JP', 'JPN', '+81', 'JPY', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(85, 'Jordan', 'JO', 'JOR', '+962', 'JOD', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(86, 'Kazakhstan', 'KZ', 'KAZ', '+7', 'KZT', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(87, 'Kenya', 'KE', 'KEN', '+254', 'KES', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(88, 'Kiribati', 'KI', 'KIR', '+686', 'AUD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(89, 'Kuwait', 'KW', 'KWT', '+965', 'KWD', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(90, 'Kyrgyzstan', 'KG', 'KGZ', '+996', 'KGS', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(91, 'Laos', 'LA', 'LAO', '+856', 'LAK', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(92, 'Latvia', 'LV', 'LVA', '+371', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(93, 'Lebanon', 'LB', 'LBN', '+961', 'LBP', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(94, 'Lesotho', 'LS', 'LSO', '+266', 'LSL', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(95, 'Liberia', 'LR', 'LBR', '+231', 'LRD', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(96, 'Libya', 'LY', 'LBY', '+218', 'LYD', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(97, 'Liechtenstein', 'LI', 'LIE', '+423', 'CHF', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(98, 'Lithuania', 'LT', 'LTU', '+370', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(99, 'Luxembourg', 'LU', 'LUX', '+352', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(100, 'Madagascar', 'MG', 'MDG', '+261', 'MGA', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(101, 'Malawi', 'MW', 'MWI', '+265', 'MWK', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(102, 'Malaysia', 'MY', 'MYS', '+60', 'MYR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(103, 'Maldives', 'MV', 'MDV', '+960', 'MVR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(104, 'Mali', 'ML', 'MLI', '+223', 'XOF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(105, 'Malta', 'MT', 'MLT', '+356', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(106, 'Marshall Islands', 'MH', 'MHL', '+692', 'USD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(107, 'Mauritania', 'MR', 'MRT', '+222', 'MRU', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(108, 'Mauritius', 'MU', 'MUS', '+230', 'MUR', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(109, 'Mexico', 'MX', 'MEX', '+52', 'MXN', 'North America', 1, 1, '2026-01-31 04:02:36'),
(110, 'Micronesia', 'FM', 'FSM', '+691', 'USD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(111, 'Moldova', 'MD', 'MDA', '+373', 'MDL', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(112, 'Monaco', 'MC', 'MCO', '+377', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(113, 'Mongolia', 'MN', 'MNG', '+976', 'MNT', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(114, 'Montenegro', 'ME', 'MNE', '+382', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(115, 'Morocco', 'MA', 'MAR', '+212', 'MAD', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(116, 'Mozambique', 'MZ', 'MOZ', '+258', 'MZN', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(117, 'Myanmar', 'MM', 'MMR', '+95', 'MMK', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(118, 'Namibia', 'NA', 'NAM', '+264', 'NAD', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(119, 'Nauru', 'NR', 'NRU', '+674', 'AUD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(120, 'Nepal', 'NP', 'NPL', '+977', 'NPR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(121, 'Netherlands', 'NL', 'NLD', '+31', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(122, 'New Zealand', 'NZ', 'NZL', '+64', 'NZD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(123, 'Nicaragua', 'NI', 'NIC', '+505', 'NIO', 'North America', 1, 1, '2026-01-31 04:02:36'),
(124, 'Niger', 'NE', 'NER', '+227', 'XOF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(125, 'Nigeria', 'NG', 'NGA', '+234', 'NGN', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(126, 'North Korea', 'KP', 'PRK', '+850', 'KPW', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(127, 'North Macedonia', 'MK', 'MKD', '+389', 'MKD', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(128, 'Norway', 'NO', 'NOR', '+47', 'NOK', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(129, 'Oman', 'OM', 'OMN', '+968', 'OMR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(130, 'Pakistan', 'PK', 'PAK', '+92', 'PKR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(131, 'Palau', 'PW', 'PLW', '+680', 'USD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(132, 'Panama', 'PA', 'PAN', '+507', 'PAB', 'North America', 1, 1, '2026-01-31 04:02:36'),
(133, 'Papua New Guinea', 'PG', 'PNG', '+675', 'PGK', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(134, 'Paraguay', 'PY', 'PRY', '+595', 'PYG', 'South America', 1, 1, '2026-01-31 04:02:36'),
(135, 'Peru', 'PE', 'PER', '+51', 'PEN', 'South America', 1, 1, '2026-01-31 04:02:36'),
(136, 'Philippines', 'PH', 'PHL', '+63', 'PHP', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(137, 'Poland', 'PL', 'POL', '+48', 'PLN', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(138, 'Portugal', 'PT', 'PRT', '+351', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(139, 'Qatar', 'QA', 'QAT', '+974', 'QAR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(140, 'Romania', 'RO', 'ROU', '+40', 'RON', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(141, 'Russia', 'RU', 'RUS', '+7', 'RUB', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(142, 'Rwanda', 'RW', 'RWA', '+250', 'RWF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(143, 'Saint Kitts and Nevis', 'KN', 'KNA', '+1869', 'XCD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(144, 'Saint Lucia', 'LC', 'LCA', '+1758', 'XCD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(145, 'Saint Vincent and the Grenadines', 'VC', 'VCT', '+1784', 'XCD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(146, 'Samoa', 'WS', 'WSM', '+685', 'WST', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(147, 'San Marino', 'SM', 'SMR', '+378', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(148, 'Sao Tome and Principe', 'ST', 'STP', '+239', 'STN', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(149, 'Saudi Arabia', 'SA', 'SAU', '+966', 'SAR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(150, 'Senegal', 'SN', 'SEN', '+221', 'XOF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(151, 'Serbia', 'RS', 'SRB', '+381', 'RSD', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(152, 'Seychelles', 'SC', 'SYC', '+248', 'SCR', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(153, 'Sierra Leone', 'SL', 'SLE', '+232', 'SLL', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(154, 'Singapore', 'SG', 'SGP', '+65', 'SGD', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(155, 'Slovakia', 'SK', 'SVK', '+421', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(156, 'Slovenia', 'SI', 'SVN', '+386', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(157, 'Solomon Islands', 'SB', 'SLB', '+677', 'SBD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(158, 'Somalia', 'SO', 'SOM', '+252', 'SOS', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(159, 'South Africa', 'ZA', 'ZAF', '+27', 'ZAR', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(160, 'South Korea', 'KR', 'KOR', '+82', 'KRW', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(161, 'South Sudan', 'SS', 'SSD', '+211', 'SSP', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(162, 'Spain', 'ES', 'ESP', '+34', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(163, 'Sri Lanka', 'LK', 'LKA', '+94', 'LKR', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(164, 'Sudan', 'SD', 'SDN', '+249', 'SDG', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(165, 'Suriname', 'SR', 'SUR', '+597', 'SRD', 'South America', 1, 1, '2026-01-31 04:02:36'),
(166, 'Sweden', 'SE', 'SWE', '+46', 'SEK', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(167, 'Switzerland', 'CH', 'CHE', '+41', 'CHF', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(168, 'Syria', 'SY', 'SYR', '+963', 'SYP', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(169, 'Taiwan', 'TW', 'TWN', '+886', 'TWD', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(170, 'Tajikistan', 'TJ', 'TJK', '+992', 'TJS', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(171, 'Tanzania', 'TZ', 'TZA', '+255', 'TZS', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(172, 'Thailand', 'TH', 'THA', '+66', 'THB', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(173, 'Timor-Leste', 'TL', 'TLS', '+670', 'USD', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(174, 'Togo', 'TG', 'TGO', '+228', 'XOF', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(175, 'Tonga', 'TO', 'TON', '+676', 'TOP', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(176, 'Trinidad and Tobago', 'TT', 'TTO', '+1868', 'TTD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(177, 'Tunisia', 'TN', 'TUN', '+216', 'TND', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(178, 'Turkey', 'TR', 'TUR', '+90', 'TRY', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(179, 'Turkmenistan', 'TM', 'TKM', '+993', 'TMT', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(180, 'Tuvalu', 'TV', 'TUV', '+688', 'AUD', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(181, 'Uganda', 'UG', 'UGA', '+256', 'UGX', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(182, 'Ukraine', 'UA', 'UKR', '+380', 'UAH', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(183, 'United Arab Emirates', 'AE', 'ARE', '+971', 'AED', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(184, 'United Kingdom', 'GB', 'GBR', '+44', 'GBP', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(185, 'United States', 'US', 'USA', '+1', 'USD', 'North America', 1, 1, '2026-01-31 04:02:36'),
(186, 'Uruguay', 'UY', 'URY', '+598', 'UYU', 'South America', 1, 1, '2026-01-31 04:02:36'),
(187, 'Uzbekistan', 'UZ', 'UZB', '+998', 'UZS', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(188, 'Vanuatu', 'VU', 'VUT', '+678', 'VUV', 'Oceania', 1, 1, '2026-01-31 04:02:36'),
(189, 'Vatican City', 'VA', 'VAT', '+39', 'EUR', 'Europe', 1, 1, '2026-01-31 04:02:36'),
(190, 'Venezuela', 'VE', 'VEN', '+58', 'VES', 'South America', 1, 1, '2026-01-31 04:02:36'),
(191, 'Vietnam', 'VN', 'VNM', '+84', 'VND', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(192, 'Yemen', 'YE', 'YEM', '+967', 'YER', 'Asia', 1, 1, '2026-01-31 04:02:36'),
(193, 'Zambia', 'ZM', 'ZMB', '+260', 'ZMW', 'Africa', 1, 1, '2026-01-31 04:02:36'),
(194, 'Zimbabwe', 'ZW', 'ZWE', '+263', 'ZWL', 'Africa', 1, 1, '2026-01-31 04:02:36');

-- --------------------------------------------------------

--
-- Table structure for table `destination_countries`
--

CREATE TABLE `destination_countries` (
  `id` int UNSIGNED NOT NULL,
  `country_id` int UNSIGNED NOT NULL,
  `flag_emoji` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `services_offered` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_featured` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `destination_countries`
--

INSERT INTO `destination_countries` (`id`, `country_id`, `flag_emoji`, `services_offered`, `is_featured`, `sort_order`, `created_at`) VALUES
(2, 30, '🇨🇦', 'Tourist visa, Study visa, work visa', 1, 0, '2026-01-31 12:11:11'),
(5, 73, '🇭🇺', 'study visa', 1, 1, '2026-01-31 12:23:23'),
(6, 185, '🇺🇸', 'work permit', 1, 0, '2026-02-02 09:12:20'),
(7, 122, '🇳🇿', 'VISIT VISA, WORK VISA  AND FAMILY VISA ', 1, 1, '2026-02-04 11:53:21'),
(8, 8, '🇦🇺', 'VISIT VISA, TOURISM VISA  AND WORK VISA ', 1, 1, '2026-02-04 11:54:08'),
(9, 5, '🇦🇴', 'ruktudr', 1, 0, '2026-02-09 12:33:31'),
(10, 16, 'BE', 'TRAVEL, ', 1, 0, '2026-02-09 13:06:38'),
(11, 183, 'AE', 'WORKING', 1, 0, '2026-02-09 14:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int UNSIGNED NOT NULL,
  `document_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `application_id` int UNSIGNED NOT NULL,
  `requirement_id` int UNSIGNED DEFAULT NULL,
  `document_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `uploaded_by` int UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `document_uuid`, `application_id`, `requirement_id`, `document_type`, `original_filename`, `stored_filename`, `file_path`, `file_size`, `mime_type`, `file_hash`, `is_verified`, `uploaded_by`, `uploaded_at`) VALUES
(35, '472c5cd7-0290-11f1-a30f-fa163e8467db', 105, NULL, 'Passport Copy', 'Application Report - ATZ20260001.pdf', '69849073bed50.pdf', 'uploads/documents/69849073bed50.pdf', 496177, 'application/pdf', NULL, 0, NULL, '2026-02-05 12:43:31'),
(36, '472cacbd-0290-11f1-a30f-fa163e8467db', 105, NULL, 'PASSPORT photo', 'gettyimages-2208522215-612x612.jpg', '69849073bf976.jpg', 'uploads/documents/69849073bf976.jpg', 28330, 'image/jpeg', NULL, 0, NULL, '2026-02-05 12:43:31'),
(45, 'e563be77-0384-11f1-a30f-fa163e8467db', 114, NULL, 'Passport Copy', 'Passeport_0001.jpg', '69862ada68447.jpg', 'uploads/documents/69862ada68447.jpg', 114426, 'image/jpeg', NULL, 0, 125, '2026-02-06 17:54:34'),
(46, 'e564076a-0384-11f1-a30f-fa163e8467db', 114, NULL, 'Passport Photo', 'PASSPORT.jpg', '69862ada68dc5.jpg', 'uploads/documents/69862ada68dc5.jpg', 185120, 'image/jpeg', NULL, 0, 125, '2026-02-06 17:54:34'),
(47, 'e564335a-0384-11f1-a30f-fa163e8467db', 114, NULL, 'Curriculum Vitae (CV)', 'MY CURRICULUM VITAE.pdf', '69862ada692ae.pdf', 'uploads/documents/69862ada692ae.pdf', 81109, 'application/pdf', NULL, 0, 125, '2026-02-06 17:54:34'),
(48, '7d597b8e-0415-11f1-93cf-fa163e8467db', 121, NULL, 'Passport Copy', 'DAVID PASSPORTS.pdf', '69871d70dbc14.pdf', 'uploads/documents/69871d70dbc14.pdf', 392047, 'application/pdf', NULL, 0, NULL, '2026-02-07 11:09:36'),
(49, '7d59b839-0415-11f1-93cf-fa163e8467db', 121, NULL, 'Document', 'WhatsApp Image 2026-02-07 at 12.36.27 PM.jpeg', '69871d70dcdeb.jpeg', 'uploads/documents/69871d70dcdeb.jpeg', 169833, 'image/jpeg', NULL, 0, NULL, '2026-02-07 11:09:36'),
(50, '96621466-0418-11f1-93cf-fa163e8467db', 122, NULL, 'Document', 'Pasport org20260201_17131989.pdf', '698722a35f543.pdf', 'uploads/documents/698722a35f543.pdf', 1304285, 'application/pdf', NULL, 0, NULL, '2026-02-07 11:31:47'),
(51, '96626290-0418-11f1-93cf-fa163e8467db', 122, NULL, 'Document', 'IMG_5204.JPG.jpeg', '698722a360579.jpeg', 'uploads/documents/698722a360579.jpeg', 166719, 'image/jpeg', NULL, 0, NULL, '2026-02-07 11:31:47'),
(52, 'a9a26683-0419-11f1-93cf-fa163e8467db', 123, NULL, 'Passport Copy', 'hassan passport .pdf', '698724712d42d.pdf', 'uploads/documents/698724712d42d.pdf', 1712339, 'application/pdf', NULL, 0, NULL, '2026-02-07 11:39:29'),
(53, 'a9a2a24d-0419-11f1-93cf-fa163e8467db', 123, NULL, 'Document', 'WhatsApp Image 2026-02-07 at 1.32.22 PM.jpeg', '698724712e332.jpeg', 'uploads/documents/698724712e332.jpeg', 61300, 'image/jpeg', NULL, 0, NULL, '2026-02-07 11:39:29'),
(54, 'a99abd42-041a-11f1-93cf-fa163e8467db', 124, NULL, 'Document', 'BIKORIMANA Joseph_ (1).pdf', '6987261e9aa77.pdf', 'uploads/documents/6987261e9aa77.pdf', 315431, 'application/pdf', NULL, 0, NULL, '2026-02-07 11:46:38'),
(55, '19ad7654-041b-11f1-93cf-fa163e8467db', 125, NULL, 'Passport Copy', 'MUHIRE PASSPORT (1).pdf', '698726daa14a4.pdf', 'uploads/documents/698726daa14a4.pdf', 674379, 'application/pdf', NULL, 0, NULL, '2026-02-07 11:49:46'),
(56, '19adac99-041b-11f1-93cf-fa163e8467db', 125, NULL, 'Document', 'WhatsApp Image 2026-02-07 at 1.33.49 PM (1).jpeg', '698726daa1ea8.jpeg', 'uploads/documents/698726daa1ea8.jpeg', 78047, 'image/jpeg', NULL, 0, NULL, '2026-02-07 11:49:46'),
(57, '38e4ccd8-041d-11f1-93cf-fa163e8467db', 126, NULL, 'Document', 'Scan_2026-02-05_22-57-58.pdf', '69872a6a061c4.pdf', 'uploads/documents/69872a6a061c4.pdf', 1496007, 'application/pdf', NULL, 0, NULL, '2026-02-07 12:04:58'),
(58, '38e50db9-041d-11f1-93cf-fa163e8467db', 126, NULL, 'Document', 'WhatsApp Image 2026-02-07 at 1.54.47 PM.jpeg', '69872a6a07085.jpeg', 'uploads/documents/69872a6a07085.jpeg', 335262, 'image/jpeg', NULL, 0, NULL, '2026-02-07 12:04:58'),
(59, '8ef52c72-0427-11f1-93cf-fa163e8467db', 127, NULL, 'Document', 'img20260207_10504855.pdf', '69873bc15df9d.pdf', 'uploads/documents/69873bc15df9d.pdf', 1110847, 'application/pdf', NULL, 0, NULL, '2026-02-07 13:18:57'),
(60, '8ef56929-0427-11f1-93cf-fa163e8467db', 127, NULL, 'Document', 'Jules photo.jpg (1).jpeg', '69873bc15e972.jpeg', 'uploads/documents/69873bc15e972.jpeg', 252900, 'image/jpeg', NULL, 0, NULL, '2026-02-07 13:18:57'),
(61, '3f6786ed-0439-11f1-93cf-fa163e8467db', 129, NULL, 'Document', '_1OH4746.jpg (1).jpeg', '6987596ed186b.jpeg', 'uploads/documents/6987596ed186b.jpeg', 489072, 'image/jpeg', NULL, 0, NULL, '2026-02-07 15:25:34');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int UNSIGNED NOT NULL,
  `payment_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `application_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `remaining_amount` decimal(10,2) DEFAULT '0.00',
  `currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT 'RWF',
  `payment_method_id` int UNSIGNED NOT NULL,
  `payment_status` enum('pending','proof_submitted','under_review','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_type` enum('full','partial_initial','partial_final') COLLATE utf8mb4_unicode_ci DEFAULT 'full',
  `is_partial_payment` tinyint(1) DEFAULT '0',
  `proof_of_payment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_type` enum('screenshot','bank_slip','receipt') COLLATE utf8mb4_unicode_ci DEFAULT 'screenshot',
  `transaction_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int UNSIGNED DEFAULT NULL,
  `review_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_uuid`, `application_id`, `user_id`, `amount`, `remaining_amount`, `currency`, `payment_method_id`, `payment_status`, `payment_type`, `is_partial_payment`, `proof_of_payment`, `proof_type`, `transaction_reference`, `submitted_at`, `reviewed_at`, `reviewed_by`, `review_notes`, `created_at`, `updated_at`) VALUES
(22, '98936b60-0385-11f1-a30f-fa163e8467db', 114, 125, 2500.00, 0.00, 'USD', 1, 'proof_submitted', 'full', 0, 'uploads/payments/69862c070cada.pdf', 'screenshot', 'I will pay after Visa approved ', '2026-02-06 17:59:35', NULL, NULL, NULL, '2026-02-06 17:59:35', '2026-02-06 17:59:35'),
(23, '43e39975-039f-11f1-a30f-fa163e8467db', 115, 165, 1.00, 999.00, 'USD', 1, 'proof_submitted', 'partial_initial', 1, 'uploads/payments/69865717d82aa.png', 'screenshot', '25913996523', '2026-02-06 21:03:19', NULL, NULL, NULL, '2026-02-06 21:03:19', '2026-02-06 21:03:19');

-- --------------------------------------------------------

--
-- Table structure for table `payment_installments`
--

CREATE TABLE `payment_installments` (
  `id` int UNSIGNED NOT NULL,
  `application_id` int UNSIGNED NOT NULL,
  `installment_number` tinyint NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','completed','overdue') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_installments`
--

INSERT INTO `payment_installments` (`id`, `application_id`, `installment_number`, `amount`, `due_date`, `status`, `payment_id`, `created_at`, `updated_at`) VALUES
(23, 21, 1, 800.00, NULL, 'pending', NULL, '2026-02-03 13:06:55', '2026-02-03 13:06:55'),
(24, 21, 2, 1700.00, NULL, 'pending', NULL, '2026-02-03 13:06:55', '2026-02-03 13:06:55'),
(25, 22, 1, 600.00, NULL, 'pending', NULL, '2026-02-03 13:17:49', '2026-02-03 13:17:49'),
(26, 22, 2, 900.00, NULL, 'pending', NULL, '2026-02-03 13:17:49', '2026-02-03 13:17:49'),
(31, 25, 1, 599.00, NULL, 'pending', NULL, '2026-02-03 13:24:09', '2026-02-03 13:24:09'),
(32, 25, 2, 901.00, NULL, 'pending', NULL, '2026-02-03 13:24:09', '2026-02-03 13:24:09'),
(45, 33, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 08:42:34', '2026-02-04 08:42:34'),
(46, 33, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 08:42:34', '2026-02-04 08:42:34'),
(47, 34, 1, 350.00, NULL, 'pending', NULL, '2026-02-04 08:52:23', '2026-02-04 08:52:23'),
(48, 34, 2, 2150.00, NULL, 'pending', NULL, '2026-02-04 08:52:23', '2026-02-04 08:52:23'),
(49, 38, 1, 1000.00, NULL, 'pending', NULL, '2026-02-04 09:09:56', '2026-02-04 09:09:56'),
(50, 38, 2, 1500.00, NULL, 'pending', NULL, '2026-02-04 09:09:56', '2026-02-04 09:09:56'),
(51, 39, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:11:14', '2026-02-04 09:11:14'),
(52, 39, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:11:14', '2026-02-04 09:11:14'),
(53, 40, 1, 1000.00, NULL, 'pending', NULL, '2026-02-04 09:13:25', '2026-02-04 09:13:25'),
(54, 40, 2, 1500.00, NULL, 'pending', NULL, '2026-02-04 09:13:25', '2026-02-04 09:13:25'),
(55, 41, 1, 1000.00, NULL, 'pending', NULL, '2026-02-04 09:16:02', '2026-02-04 09:16:02'),
(56, 41, 2, 1500.00, NULL, 'pending', NULL, '2026-02-04 09:16:02', '2026-02-04 09:16:02'),
(57, 42, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:18:04', '2026-02-04 09:18:04'),
(58, 42, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:18:04', '2026-02-04 09:18:04'),
(59, 43, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:19:37', '2026-02-04 09:19:37'),
(60, 43, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:19:37', '2026-02-04 09:19:37'),
(61, 44, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:23:10', '2026-02-04 09:23:10'),
(62, 44, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:23:10', '2026-02-04 09:23:10'),
(63, 45, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:24:45', '2026-02-04 09:24:45'),
(64, 45, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:24:45', '2026-02-04 09:24:45'),
(65, 46, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:26:25', '2026-02-04 09:26:25'),
(66, 46, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:26:25', '2026-02-04 09:26:25'),
(67, 47, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:28:11', '2026-02-04 09:28:11'),
(68, 47, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:28:11', '2026-02-04 09:28:11'),
(69, 48, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:30:02', '2026-02-04 09:30:02'),
(70, 48, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:30:02', '2026-02-04 09:30:02'),
(71, 49, 1, 479.00, NULL, 'pending', NULL, '2026-02-04 09:34:13', '2026-02-04 09:34:13'),
(72, 49, 2, 2021.00, NULL, 'pending', NULL, '2026-02-04 09:34:13', '2026-02-04 09:34:13'),
(73, 50, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:36:31', '2026-02-04 09:36:31'),
(74, 50, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:36:31', '2026-02-04 09:36:31'),
(75, 51, 1, 344.00, NULL, 'pending', NULL, '2026-02-04 09:39:03', '2026-02-04 09:39:03'),
(76, 51, 2, 2156.00, NULL, 'pending', NULL, '2026-02-04 09:39:03', '2026-02-04 09:39:03'),
(77, 52, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:40:58', '2026-02-04 09:40:58'),
(78, 52, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:40:58', '2026-02-04 09:40:58'),
(79, 53, 1, 344.00, NULL, 'pending', NULL, '2026-02-04 09:41:59', '2026-02-04 09:41:59'),
(80, 53, 2, 2156.00, NULL, 'pending', NULL, '2026-02-04 09:41:59', '2026-02-04 09:41:59'),
(81, 54, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:43:13', '2026-02-04 09:43:13'),
(82, 54, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:43:13', '2026-02-04 09:43:13'),
(83, 55, 1, 344.00, NULL, 'pending', NULL, '2026-02-04 09:44:52', '2026-02-04 09:44:52'),
(84, 55, 2, 2156.00, NULL, 'pending', NULL, '2026-02-04 09:44:52', '2026-02-04 09:44:52'),
(85, 56, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:49:32', '2026-02-04 09:49:32'),
(86, 56, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:49:32', '2026-02-04 09:49:32'),
(87, 57, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 09:50:55', '2026-02-04 09:50:55'),
(88, 57, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:50:55', '2026-02-04 09:50:55'),
(89, 58, 1, 2000.00, NULL, 'pending', NULL, '2026-02-04 09:52:58', '2026-02-04 09:52:58'),
(90, 58, 2, 500.00, NULL, 'pending', NULL, '2026-02-04 09:52:58', '2026-02-04 09:52:58'),
(91, 59, 1, 1000.00, NULL, 'pending', NULL, '2026-02-04 09:55:11', '2026-02-04 09:55:11'),
(92, 59, 2, 1500.00, NULL, 'pending', NULL, '2026-02-04 09:55:11', '2026-02-04 09:55:11'),
(93, 60, 1, 1000.00, NULL, 'pending', NULL, '2026-02-04 09:56:43', '2026-02-04 09:56:43'),
(94, 60, 2, 1500.00, NULL, 'pending', NULL, '2026-02-04 09:56:43', '2026-02-04 09:56:43'),
(95, 61, 1, 344.00, NULL, 'pending', NULL, '2026-02-04 09:58:52', '2026-02-04 09:58:52'),
(96, 61, 2, 2156.00, NULL, 'pending', NULL, '2026-02-04 09:58:52', '2026-02-04 09:58:52'),
(97, 62, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 10:01:06', '2026-02-04 10:01:06'),
(98, 62, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 10:01:06', '2026-02-04 10:01:06'),
(99, 63, 1, 2000.00, NULL, 'pending', NULL, '2026-02-04 10:09:33', '2026-02-04 10:09:33'),
(100, 63, 2, 500.00, NULL, 'pending', NULL, '2026-02-04 10:09:33', '2026-02-04 10:09:33'),
(101, 64, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 10:11:26', '2026-02-04 10:11:26'),
(102, 64, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 10:11:26', '2026-02-04 10:11:26'),
(103, 65, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 10:18:07', '2026-02-04 10:18:07'),
(104, 65, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 10:18:07', '2026-02-04 10:18:07'),
(105, 66, 1, 500.00, NULL, 'pending', NULL, '2026-02-04 10:19:34', '2026-02-04 10:19:34'),
(106, 66, 2, 2000.00, NULL, 'pending', NULL, '2026-02-04 10:19:34', '2026-02-04 10:19:34'),
(107, 67, 1, 1000.00, NULL, 'pending', NULL, '2026-02-04 10:21:10', '2026-02-04 10:21:10'),
(108, 67, 2, 1500.00, NULL, 'pending', NULL, '2026-02-04 10:21:10', '2026-02-04 10:21:10'),
(113, 105, 1, 750.00, NULL, 'completed', NULL, '2026-02-05 12:42:51', '2026-02-05 12:44:12'),
(114, 105, 2, 750.00, NULL, 'pending', NULL, '2026-02-05 12:42:51', '2026-02-05 12:42:51'),
(125, 115, 1, 1.00, NULL, 'pending', NULL, '2026-02-06 20:54:58', '2026-02-06 20:54:58'),
(126, 115, 2, 999.00, NULL, 'pending', NULL, '2026-02-06 20:54:58', '2026-02-06 20:54:58');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('mobile_money','bank','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `type`, `account_number`, `account_name`, `instructions`, `is_active`, `created_at`) VALUES
(1, 'MTN Mobile Money', 'mobile_money', '0788123456', 'A to Z Global Link', 'Send money to this number and take screenshot of confirmation', 1, '2026-01-31 03:58:40'),
(2, 'Airtel Money', 'mobile_money', '0738123456', 'A to Z Global Link', 'Send money to this number and take screenshot of confirmation', 1, '2026-01-31 03:58:40'),
(3, 'Bank of Kigali', 'bank', '00012345678901', 'A to Z Global Link Solution Ltd', 'Transfer to this account and upload bank slip', 1, '2026-01-31 03:58:40'),
(4, 'Equity Bank', 'bank', '40012345678', 'A to Z Global Link Solution Ltd', 'Transfer to this account and upload bank slip', 1, '2026-01-31 03:58:40'),
(5, 'Cash Payment', 'other', 'CASH-001', 'A to Z Global Link', 'Cash payment received at office', 1, '2026-02-03 14:09:11');

-- --------------------------------------------------------

--
-- Table structure for table `refund_requests`
--

CREATE TABLE `refund_requests` (
  `id` int NOT NULL,
  `application_id` int NOT NULL,
  `user_id` int NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','processed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int UNSIGNED NOT NULL,
  `service_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `category_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_description` text COLLATE utf8mb4_unicode_ci,
  `base_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `processing_time_min` int DEFAULT NULL,
  `processing_time_max` int DEFAULT NULL,
  `processing_time_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requirements_summary` text COLLATE utf8mb4_unicode_ci,
  `requirements_json` text COLLATE utf8mb4_unicode_ci,
  `terms_conditions` text COLLATE utf8mb4_unicode_ci,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_uuid`, `category_id`, `name`, `slug`, `short_description`, `full_description`, `base_price`, `currency`, `processing_time_min`, `processing_time_max`, `processing_time_description`, `requirements_summary`, `requirements_json`, `terms_conditions`, `is_featured`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(4, '83131847-0044-11f1-a30f-fa163e8467db', 16, 'Dubai Job Opportunities – 2-Year Work Contract', 'dubai-job-opportunities-2-year-work-contract', 'Get a 2-year renewable work contract in Dubai with visa approval in just 5 days. No upfront payment. Accommodation, medical insurance, and Emirates ID included', 'AtoZ Global Link Solutions is proud to offer verified job opportunities in Dubai through our official recruitment partnership with a leading Dubai-based agency. Selected candidates can receive visa approval within 5 days and start working in Dubai within 7 days.\r\n\r\nAvailable Job Categories\r\n\r\nConstruction Workers\r\n\r\nConstruction Helpers\r\n\r\nElectricians\r\n\r\nPlumbers\r\n\r\nCarpenters\r\n\r\nHotel Staff\r\n\r\nCleaners (Female candidates only)\r\n\r\nTruck Drivers\r\n\r\nOther professional roles\r\n\r\nNote: Skilled and professional applicants must provide valid proof of work experience.\r\n\r\nSalary Structure\r\n\r\nProfessional & Skilled Jobs: USD 700 – 1,200 / month\r\n\r\nUnskilled Jobs: USD 500 – 700 / month\r\n(Salary depends on job category, experience, and employer.)\r\n\r\nContract Details\r\n\r\nDuration: 2 years\r\n\r\nRenewable upon completion\r\n\r\nBenefits Included\r\n\r\nDubai Work Visa\r\n\r\nAccommodation\r\n\r\nMedical Insurance\r\n\r\nEmirates ID / Work Card\r\n\r\nAt AtoZ Global Link Solutions, we are committed to making your dream of working abroad a reality.\r\nAbroad is our new home — and your journey starts here', 1000.00, 'USD', 5, 7, 'Visa approval within 5 working days (excluding weekends)', NULL, '{\"documents\":[{\"id\":1,\"name\":\"PASSPORT\",\"type\":\"pdf\",\"required\":false,\"description\":\"\",\"maxSize\":5},{\"id\":2,\"name\":\"PASSPORT PHOTOS\",\"type\":\"image\",\"required\":true,\"description\":\"\",\"maxSize\":5}]}', 'No upfront payment required,\r\nUSD 1000 payable only after visa approval and readiness', 0, 1, 0, '2026-02-02 14:36:08', '2026-02-02 14:36:08'),
(6, '2950ee1d-0053-11f1-a30f-fa163e8467db', 18, 'Luxembourg Work Visa Package', 'luxembourg-work-visa-package', 'Professional Luxembourg work visa package offering job placement support, transparent payments, and full guidance through the visa process within 3–4 months.', 'The Luxembourg Work Package is designed to assist clients in securing employment and\r\nprocessing their work visa for Luxembourg within 3–4 months. ATOZ Global Link Solutions ensures\r\nreliable guidance, professional support, and a smooth transition through every step of the process.\r\nTotal Program Fees\r\n- Total Fees: $2,500 USD\r\n- First Installment: $1,000 USD\r\n- Final Payment: $1,500 USD (after visa approval)\r\nRefund Policy\r\n- The $1,000 USD advance payment is fully refundable if visa is rejected.\r\nSalary Range in Luxembourg\r\n- €2,500 – €3,200 per month.\r\nAvailable Job Categories\r\n- General Labor\r\n- Warehouse Workers\r\n- Factory Workers\r\n- Caregivers\r\n- Construction Workers\r\n- Hospitality Staff\r\nProcessing Timeline\r\n- 3 to 4 months.', 2500.00, 'USD', 90, 120, 'Estimated processing timeline is 3–4 months, depending on immigration and employer procedures.', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":2,\"name\":\"Passport Photo\",\"type\":\"image\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":3,\"name\":\"Curriculum Vitae (CV)\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5}]}', 'Advance payment of USD 1,000 is required to start processing\r\n\r\nFinal payment is due only after visa approval\r\n\r\nAdvance payment is refundable if visa is rejected\r\n\r\nProcessing time may vary based on immigration and employer requirements\r\n\r\nAll documents must be genuine and valid', 0, 1, 0, '2026-02-02 16:21:00', '2026-02-02 16:21:00'),
(7, '01a7c836-0054-11f1-a30f-fa163e8467db', 19, 'Serbia Seasonal Work Visa', 'serbia-seasonal-work-visa', 'Seasonal work visa for Serbia offering farm and agricultural jobs, free accommodation, partial meals, and full visa guidance from ATOZ Global Link Solutions.', 'ATOZ Global Link Solutions provides a Serbia Seasonal Work Visa Package for men, women, and couples aged 18–50. This package includes seasonal employment in farming, greenhouse, and agricultural product sorting, with free accommodation and partially provided meals.\r\n\r\nNo prior work experience is required, making this package suitable for anyone willing to work. A professional team guides clients through every step—from document preparation to visa approval.\r\n\r\nLocation: Novi Sad, Serbia\r\n\r\nContract Duration: Seasonal (6 months)\r\n\r\nSalary Range: €600 – €800 per month', 1500.00, 'EUR', 21, 21, 'Approximate processing time: 3 weeks', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"Clear scan of passport bio-data page\",\"maxSize\":5},{\"id\":2,\"name\":\"Passport Photo\",\"type\":\"image\",\"required\":true,\"description\":\"Recent passport-size photo\",\"maxSize\":5}]}', 'Package is for seasonal work in Serbia (6 months)\r\n\r\nAccommodation is free; meals are partially provided\r\n\r\nPayment: advance payment required, remaining after document submission\r\n\r\nFees are non-refundable once documents are submitted\r\n\r\nApplicants must be aged 18–50 and willing to work\r\n\r\nNo prior experience required', 0, 1, 0, '2026-02-02 16:27:03', '2026-02-03 09:51:21'),
(8, 'fbd2c37b-0054-11f1-a30f-fa163e8467db', 21, 'South Korea Visa Processing', 'south-korea-visa-processing', 'Visa processing for South Korea including personal and business invitations, embassy appointment assistance, and fast document processing within 15 days.', 'ATOZ Global Link Solutions assists clients in obtaining South Korea visas through personal or business invitations. The service includes full guidance from document preparation to embassy appointment handling, ensuring a high visa approval rate.\r\n\r\nInvitation Types:\r\n\r\nPersonal Invitation – for buying cars\r\n\r\nBusiness Invitation\r\n\r\nProcessing Time: 15 days\r\n\r\nEmbassy Appointment Handling: Done by ATOZ Global Link Solutions', 1500.00, 'EUR', 15, 15, 'Approx. 15 days for complete document processing and embassy appointment handling.', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"Clear scan of passport bio-data page\",\"maxSize\":5},{\"id\":2,\"name\":\"Passport Photo\",\"type\":\"image\",\"required\":true,\"description\":\"Recent passport-size photo\",\"maxSize\":5},{\"id\":3,\"name\":\"Invitation Letter\",\"type\":\"pdf\",\"required\":true,\"description\":\"ersonal or business invitation for South Korea\",\"maxSize\":5}]}', 'Advance payment required to begin processing\r\n\r\nRemaining balance payable after document preparation\r\n\r\nATOZ handles embassy appointments\r\n\r\nHigh approval rate but applicants must provide authentic documents\r\n\r\nFees non-refundable after submission of completed documents', 0, 1, 0, '2026-02-02 16:34:02', '2026-02-03 09:50:49'),
(9, '9f03ccfb-0055-11f1-a30f-fa163e8467db', 18, 'Belarus Work Permit Processing', 'belarus-work-permit-processing', 'Obtain work permits for Belarus including Normal, Airport, and Special (Blue) Permits with full guidance and high approval rate.', 'ATOZ Global Link Solutions offers three types of Belarus work permits:\r\n\r\nNormal Work Invitation: Visa obtained from embassy\r\n\r\nNormal Work Invitation with Minsk National Airport Seal: Visa on arrival or embassy\r\n\r\nSpecial Permit / Blue Permit: 1-year work permit with D Visa, 100% approval\r\n\r\nAll permits include professional guidance, document preparation, and support through the visa process.', 1500.00, 'EUR', 5, 30, '5–30 days depending on permit type', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"Clear scan of passport bio-data page\",\"maxSize\":5},{\"id\":2,\"name\":\"Passport Photo\",\"type\":\"image\",\"required\":true,\"description\":\"Recent passport-size photograph\",\"maxSize\":5},{\"id\":3,\"name\":\"Work Invitation\",\"type\":\"pdf\",\"required\":true,\"description\":\"Official work invitation from Belarus employer\",\"maxSize\":5}]}', 'Advance payment is required to start processing\r\n\r\nRemaining balance payable after document submission\r\n\r\nSpecial / Blue permit guarantees approval if documents are correct\r\n\r\nAll documents must be genuine\r\n\r\nFees non-refundable after processing begins', 0, 1, 0, '2026-02-02 16:38:36', '2026-02-03 09:50:27'),
(10, '90a0f431-0056-11f1-a30f-fa163e8467db', 18, 'Moldova E-Visa Processing', 'moldova-e-visa-processing', 'Fast and secure Moldova e-visa processing with official invitation and full document handling.', 'ATOZ Global Link Solutions provides complete Moldova e-visa services including:\r\n\r\nOfficial invitation from the Ministry\r\n\r\nSupporting documents\r\n\r\nComplete e-visa processing handled by our team\r\n\r\nProfessional guidance from start to finish\r\n\r\nProcessing is handled efficiently to ensure clients receive their visa with minimal hassle.', 1500.00, 'EUR', 14, 21, 'Up to 3 weeks for complete visa processing', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"Clear scan of passport bio-data page\",\"maxSize\":5},{\"id\":2,\"name\":\"Passport Photo\",\"type\":\"image\",\"required\":true,\"description\":\"Recent passport-size photo\",\"maxSize\":5},{\"id\":3,\"name\":\"Invitation Letter\",\"type\":\"pdf\",\"required\":true,\"description\":\"Official invitation from Moldova Ministry\",\"maxSize\":5}]}', 'Advance payment required to begin processing\r\n\r\nRemaining balance payable after documents are prepared\r\n\r\nComplete guidance provided until e-visa approval\r\n\r\nAll documents must be authentic\r\n\r\nFees non-refundable after document submissio', 0, 1, 0, '2026-02-02 16:45:21', '2026-02-03 09:49:57'),
(11, '48c284f9-0057-11f1-a30f-fa163e8467db', 16, 'Turkey Work Visa 2026 – Manual Worker', 'turkey-work-visa-2026-manual-worker', 'Work in Turkey as a manual worker with accommodation, meals, 1-year contract, and high visa approval support from ATOZ Global Link Solutions.', 'ATOZ Global Link Solutions offers employment in Nilüfer, Bursa, Turkey for male and female applicants as manual workers in manufacturing and commercial production.\r\n\r\nWorkplace: Alaaddinbey Mahallesi Pazar (380) Cad. Gasan No: 10/1, Nilüfer / Bursa – Turkey\r\n\r\nEmployment Duration: 1-year contract (after work permit approval)\r\n\r\nSalary & Benefits: 26,005.50 TL per month, accommodation & meals provided\r\n\r\nEligibility: Open to all genders, no prior experience required', 1500.00, 'EUR', 10, 10, 'Documents issued within 10 days; smooth visa process with high approval rate', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"Clear scan of passport bio-data page\",\"maxSize\":5},{\"id\":2,\"name\":\"Passport Photo\",\"type\":\"image\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":3,\"name\":\"CV \\/ Resume \",\"type\":\"pdf\",\"required\":false,\"description\":\"Work experience details if available\",\"maxSize\":5}]}', 'Advance payment required to start processing\r\n\r\nRemaining balance payable after document issuance\r\n\r\nHigh visa approval rate if documents are complete\r\n\r\nAccommodation and meals provided by employer\r\n\r\nFees non-refundable after document submission', 0, 1, 0, '2026-02-02 16:50:30', '2026-02-03 09:49:31'),
(12, '13f71e6e-00e8-11f1-a30f-fa163e8467db', 16, 'New Zealand Work Program', 'new-zealand-work-program', 'End-to-end support for securing employment and a work visa in New Zealand, including job sourcing, visa guidance, and pre-departure orientation.', 'The New Zealand Work Program is designed for individuals seeking employment opportunities in New Zealand across various sectors. This service provides comprehensive support from job application and sourcing to visa processing assistance and documentation guidance. Our team supports you throughout the entire journey, ensuring compliance with immigration requirements and preparing you for a smooth relocation and work start in New Zealand.\r\n\r\nServices Included:\r\n\r\nJob application and sourcing\r\n\r\nVisa processing support\r\n\r\nDocumentation guidance\r\n\r\nPre-departure orientation\r\n\r\nPrograms Available: Warehouse • Factory • Drivers • Caregivers • Skilled Professionals\r\nPR pathway available for skilled workers under 45 years old.\r\nPricing & Processing\r\n\r\nBase Price: 2,500 USD\r\nPayment Plan:\r\n\r\nFirst Tranche: 500 USD (to start processing)\r\n\r\nRemaining Balance: 2,000 USD (after visa approval)', 2500.00, 'USD', 90, 120, '3–4 months', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":2,\"name\":\"Passport photo\",\"type\":\"image\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":3,\"name\":\"Updated CV\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5}]}', 'Fees are non-refundable once processing has started.\r\n\r\nRemaining balance is payable only after visa approval.\r\n\r\nProcessing timelines are estimates and subject to immigration authority decisions.', 0, 1, 0, '2026-02-03 10:06:59', '2026-02-03 10:17:00'),
(13, '7115e268-00e9-11f1-a30f-fa163e8467db', 16, 'Australia Work Program', 'australia-work-program', 'Professional assistance for employment placement and work visa processing in Australia, including documentation and pre-departure support.', 'The Australia Work Program supports applicants seeking employment opportunities in Australia across multiple industries. The service includes job sourcing, visa application assistance, documentation review, and pre-departure orientation to ensure candidates are well-prepared for employment and life in Australia.\r\n\r\nServices Included:\r\n\r\nJob application and sourcing\r\n\r\nVisa processing support\r\n\r\nDocumentation guidance\r\n\r\nPre-departure orientation\r\n\r\nPrograms Available: Warehouse • Factory • Drivers • Caregivers • Skilled Professionals\r\nPR pathway available for eligible skilled workers under 45 years old.\r\n\r\nPricing & Processing\r\n\r\nBase Price: 3,000 USD\r\nInstallment Plan:\r\n\r\n1st Payment: 1,000 USD (processing start)\r\n\r\n2nd Payment: 2,000 USD (after visa approval)', 3000.00, 'USD', 90, 120, '3–4 months', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":2,\"name\":\"Passport photo\",\"type\":\"image\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":3,\"name\":\"Updated CV\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":4,\"name\":\"Education Certificates\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":5,\"name\":\"Experience Certificates\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5}]}', 'Initial payment is required to begin processing.\r\n\r\nFinal payment is due only after visa approval.\r\n\r\nProcessing timelines may vary depending on immigration authorities and applicant profile.', 0, 1, 0, '2026-02-03 10:16:44', '2026-02-03 10:16:44'),
(14, '0de4aabd-01b3-11f1-a30f-fa163e8467db', 17, 'NEW ZEALAND  FAMILY VISA ', 'new-zealand-family-visa', '📌 New Zealand Family Visa Packages – Overview\r\n\r\nNew Zealand offers several family-based visa options that allow relatives to join a family member who is living, working, or planning to live in New Zealand. These visas are designed to help families reunite and stay together.', 'New Zealand Family Visa Packages – Overview\r\n\r\nNew Zealand offers several family-based visa options that allow relatives to join a family member who is living, working, or planning to live in New Zealand. These visas are designed to help families reunite and stay together.\r\n\r\n👨‍👩‍👧‍👦 1. Partner (Spouse) Visa\r\n\r\n✅ For spouses or partners of New Zealand citizens or residents\r\n✔ Allows the partner to live, work, and study in NZ\r\n✔ Requires proof of genuine relationship\r\n\r\n👶 2. Dependent Child Visa\r\n\r\n✅ For unmarried children under 24 (or dependent due to special circumstances)\r\n✔ Must be financially dependent on the sponsor\r\n✔ Allows children to live (and sometimes study) in NZ\r\n\r\n👴👵 3. Parent Visitor Visa / Parent Retirement Visa\r\n\r\nParent Visitor Visa\r\n➡ For parents to visit family in NZ for a limited time (usually up to 6–12 months)\r\n➡ Must show sufficient funds and return intentions\r\n\r\nParent Retirement Visa (letter-based, sometimes closed to new applicants)\r\n➡ Allows parents to live long-term if they invest a certain amount and meet financial requirements\r\n\r\n👨‍👩‍👧‍👦 4. Family-Sponsored Visa\r\n\r\n✅ Allows eligible family members to apply if a New Zealand citizen or resident sponsor meets income and support criteria\r\n✔ Sponsors must prove ability to support relatives\r\n✔ Often used for siblings, adult children, or other relatives\r\n\r\n🧾 General Benefits\r\n\r\n✔ Brings families together in New Zealand\r\n✔ Many family visas allow work and study rights\r\n✔ Legal protection and access to NZ services\r\n✔ Can be a step toward longer-term residence\r\n\r\n📌 Important Requirements\r\n\r\n🔹 Valid passport\r\n🔹 Proof of relationship with sponsor\r\n🔹 Adequate financial means\r\n🔹 Good character and health requirements\r\n🔹 Police certificates may be required\r\n\r\n📍 Note\r\n\r\nVisa rules change regularly. Each family category has specific requirements, fees, and conditions that can vary depending on nationality and the sponsor’s status in New Zealand.', 6000.00, 'USD', 120, 150, '', NULL, '{\"documents\":[{\"id\":1,\"name\":\"PASSPORT COPY \",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":2,\"name\":\"PHOTO SIEZE \",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":3,\"name\":\"Marriage certificate\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":4,\"name\":\"Proof of relationship with sponsor\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":5,\"name\":\"Good character and health requirements\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":6,\"name\":\"Police certificates may be required\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5}]}', '', 0, 1, 1, '2026-02-04 10:19:56', '2026-02-04 10:19:56'),
(15, '522bd26a-026a-11f1-a30f-fa163e8467db', 25, 'canada study visa', 'canada-study-visa', 'admission, study visa ,', 'Main Eligibility Criteria\r\n\r\nBefore you apply for a Canadian study visa, you must:\r\n\r\nBe accepted by a Canadian school\r\n\r\nYou need a Letter of Acceptance from a Designated Learning Institution (DLI) in Canada. This is the most important document — no visa application will be accepted without it.\r\n\r\nHave adequate financial resources\r\n\r\nYou must show you have enough money to pay for:\r\n\r\nTuition fees\r\n\r\nLiving expenses for yourself (and family members, if they’re coming with you)\r\n\r\nReturn transportation home\r\n\r\nGenerally, you’ll need proof of funds that covers at least the first year of study and living costs — e.g., bank statements, student loan letters, scholarship notices, or a Guaranteed Investment Certificate (GIC) from a Canadian bank.\r\n\r\nProve your identity and travel documents\r\n\r\nA valid passport or travel document (with at least one blank page).\r\n\r\nTwo recent passport-size photos with your name and date of birth written on the back.\r\n\r\nMeet additional conditions\r\n\r\nObey Canadian law and have no criminal record — in some cases this means providing a police certificate.\r\n\r\nBe in good health — if required, undergo a medical exam from an approved clinic.\r\n\r\nGive a Letter of Explanation describing why you want to study in Canada and confirming you understand your responsibilities as an international student.\r\n\r\n📄 Key Documents You’ll Usually Need\r\n\r\nBelow is a typical checklist of paperwork needed (exact requirements can vary by country):\r\n\r\n✉️ Letter of Acceptance from a DLI\r\n\r\n🛂 Valid passport/travel document\r\n\r\n📸 Passport-sized photographs\r\n\r\n💰 Proof of funds (bank statements, GIC, scholarship proof, etc.)\r\n\r\n🔎 Police certificate (if required)\r\n\r\n🩺 Medical exam results (if asked)\r\n\r\n📝 Letter of Explanation (study plan/motivation)\r\n\r\n📑 Other immigration forms (e.g., family info form if dependents are applying)\r\n\r\n🧾 Proof of payment of tuition or housing (sometimes required)\r\n\r\n📜 Academic transcripts or certificates (to support your acceptance)\r\n\r\n🧠 Other Things to Know\r\n\r\nLanguage tests (like IELTS/TOEFL) are often needed by the university or college to issue your acceptance letter — not always directly for the visa, but they’re usually part of the academic admission process.\r\n\r\nIn some regions (like Québec), you must also get a Québec Acceptance Certificate (CAQ) before applying.\r\n\r\nThe study permit application itself costs around CA$150, and you might need to provide biometrics (photo and fingerprints).\r\n\r\nApply early — processing times vary by country, and it’s best to submit your application months before your course starts.\r\n\r\n✅ Summary: To get a Canadian study visa, you need a valid acceptance from a designated school, proof of money to support your stay, identification documents (passport & photos), and meet health and legal checks. Once you have all documents ready, you submit your study permit application online or through the local Canadian visa office.', 1500.00, 'USD', 7, 78, 'Estimated processing time is 4–5 months, depending on immigration and document clearance.', NULL, '{\"documents\":[{\"id\":1,\"name\":\"Passport Copy\",\"type\":\"pdf\",\"required\":true,\"description\":\"\",\"maxSize\":5},{\"id\":2,\"name\":\"PASSPORT photo\",\"type\":\"image\",\"required\":true,\"description\":\"\",\"maxSize\":5}]}', '', 0, 1, 0, '2026-02-05 08:11:49', '2026-02-07 09:41:51'),
(16, '04ff2db2-05ba-11f1-93cf-fa163e8467db', 25, ' STUDY VISA  FOR OTHER COUNTRIES/UK, USA , ETC', 'study-visa-for-other-country', 'STUDY VISA ALLOCATIONS', '🌍 WE HELP YOU APPLY FOR STUDY VISA TO DIFFERENT COUNTRIES\r\nWith ATOZ GLOBAL LINK SOLUTIONS LTD\r\n\r\nHere are 10 BIG & POPULAR STUDY DESTINATION COUNTRIES we assist with:\r\n\r\n🎓 Canada\r\n\r\n🎓 United Kingdom\r\n\r\n🎓 Australia\r\n\r\n🎓 United States\r\n\r\n🎓 Germany\r\n\r\n🎓 France\r\n\r\n🎓 Ireland\r\n\r\n🎓 New Zealand\r\n\r\n🎓 Poland\r\n\r\n🎓 Finland\r\n\r\n✅ Our Services Include:\r\n\r\nUniversity & College Admission\r\n\r\nStudy Visa Application Support\r\n\r\nDocumentation & SOP Guidance\r\n\r\nInterview Preparation\r\n\r\nPre-departure Assistance\r\n\r\n📌 Your future starts with the right guidance.\r\nATOZ GLOBAL LINK SOLUTIONS LTD – Connecting You to Global Education Opportunities', 500.00, 'USD', 30, 90, '60 DAYS ', NULL, '{\"documents\":[]}', '', 0, 1, 0, '2026-02-09 13:19:53', '2026-02-09 13:20:20');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `description`, `icon`, `is_active`, `sort_order`, `created_at`) VALUES
(16, 'Work Visa / Overseas Employment', 'Services that help individuals secure legal jobs abroad, including work visa processing, job placement, and employer-sponsored employment opportunities.', NULL, 1, 0, '2026-02-02 12:53:44'),
(17, 'Family Work Visa Packages', 'Comprehensive family-based work visa solutions that allow parents and children to relocate abroad together, with guaranteed employment, accommodation, and full visa processing support.', NULL, 1, 0, '2026-02-02 15:58:16'),
(18, 'European Work Visa Programs', 'Work visa services for European countries offering legal employment opportunities, accommodation support, and guided visa processing for skilled and unskilled workers.', NULL, 1, 0, '2026-02-02 15:58:40'),
(19, 'Seasonal & Agricultural Work Visas', 'Short-term and seasonal overseas job opportunities, mainly in farming and agriculture, including accommodation, basic benefits, and fast visa processing.', NULL, 1, 0, '2026-02-02 15:59:12'),
(20, 'Overseas Work Permit Services', 'Professional assistance for obtaining work permits and job-linked visas, including invitations, employer documentation, and embassy or arrival visa options.', NULL, 1, 0, '2026-02-02 15:59:33'),
(21, 'Asia Work & Business Visa Services', 'Visa processing services for Asian countries, including work-related, business, and special invitation visas with high approval rates and fast processing times.', NULL, 1, 0, '2026-02-02 15:59:52'),
(22, 'E-Visa & Fast Track Visa Services', 'Digital and fast-track visa solutions with complete handling of invitations, documentation, and online visa submission for quick and secure approvals.', NULL, 1, 0, '2026-02-02 16:00:10'),
(23, 'Manufacturing & Industrial Job Programs', 'Overseas employment opportunities in factories and industrial companies, offering legal contracts, accommodation, meals, and employer-sponsored work permits.', NULL, 1, 0, '2026-02-02 16:00:29'),
(24, 'Unskilled & General Labor Job Placements', 'ob placement and visa services for general labor roles abroad, suitable for applicants without prior experience, including housing and employer support.', NULL, 1, 0, '2026-02-02 16:01:02'),
(25, 'STUDY VISA AND VISIT  VISA ', 'ALL SERVICES RELATED TO  STUDY AND VISIT  VISA AND FAMILY REUNIFICATION ', NULL, 1, 1, '2026-02-07 09:41:29');

-- --------------------------------------------------------

--
-- Table structure for table `service_requirements`
--

CREATE TABLE `service_requirements` (
  `id` int UNSIGNED NOT NULL,
  `service_id` int UNSIGNED NOT NULL,
  `requirement_type` enum('document','information','payment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_mandatory` tinyint(1) DEFAULT '1',
  `file_types_allowed` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_file_size` int DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `user_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `occupation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_uuid`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `date_of_birth`, `gender`, `country`, `nationality`, `address`, `city`, `postal_code`, `passport_number`, `passport_expiry`, `occupation`, `emergency_contact_name`, `emergency_contact_phone`, `profile_picture`, `is_active`, `email_verified`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `last_login`, `created_at`, `updated_at`) VALUES
(13, '4882cae9-00df-11f1-a30f-fa163e8467db', 'JOSUE', 'NDACYAYISENGA', 'joshinda99@gmail.com', '0783796101', '$2y$10$rw58Y7SqvG0OiOWvG.AOse7VoMV7jKON4JH4mS1y/S5IVvsbquvu2', '1986-01-01', 'male', 'Luxembourg', 'Rwanda', NULL, NULL, NULL, 'pc772167', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 09:04:01', '2026-02-03 09:04:01'),
(14, 'e8c80bd0-00df-11f1-a30f-fa163e8467db', 'GEOVANIE', 'IGIRANEZA', 'igaranezageovanie@gmail.com', '0785757701', '$2y$10$Wp1R.Iy/VZYlxJQoLprB2.AreUtrVJJ3Rq46f1nNsae05Huoy.cay', '1998-01-01', NULL, 'AUSTRALIA', 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 09:08:30', '2026-02-03 09:11:46'),
(15, '3735c8a6-00e0-11f1-a30f-fa163e8467db', 'EMMANUEL', 'NDAGIJE', 'ndagijeemmanuel@gmail.com', '+250788523997', '$2y$10$wOfgdAANeHOUzj9EmETNeO8Qe1HXTu42WpcqUt9rKQSMZ85eMvEnK', '2000-01-01', 'male', 'LUXEMBORUG', 'Rwanda', NULL, NULL, NULL, 'PC779849', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 09:10:42', '2026-02-03 09:12:11'),
(16, 'b9b06c0b-00e0-11f1-a30f-fa163e8467db', 'AIME MARIE', 'IGIRANEZA', 'igiraneza@gmail.com', '0788402775', '$2y$10$b2PtELCefEbOa7YjHSpB6uGuNqMJEicodUN6bwFkIHQBjob6d7O2e', '2000-01-01', 'female', 'AUSTRALIA', 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 09:14:21', '2026-02-03 09:14:45'),
(17, 'fd198f76-00e0-11f1-a30f-fa163e8467db', 'JULES', 'HIRWA', 'munyal2020@gmail.com', '+25078840101', '$2y$10$VNeS87PIY//8vashhPQqYesL4VEgsUG6/3UtzPiue9K7jIoY5Q0r6', '2000-01-01', 'male', 'AUSTRALIA', 'Australia', 'KIGALI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 09:16:14', '2026-02-03 09:16:35'),
(18, '40dddeb5-00e1-11f1-a30f-fa163e8467db', 'SANO', 'LIVINGSTONE', 'sanolivingstone@gmail.com', '0786930830', '$2y$10$hS9czpPRNoHmrYUU248M2etTJizob/5I7LBRODJc7oGCzOCEUih12', NULL, 'male', 'AUSTRALIA', 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 09:18:08', '2026-02-03 09:18:32'),
(19, '7a9b4ab6-00e1-11f1-a30f-fa163e8467db', 'MICHEL', 'DUSHIMIMANA', 'dushimimanamichel@gmail.com', '0786024456', '$2y$10$MY.4P0Os8u4oWZZatBAbIOL0xAhLwem3gpciDeWnPnvOemR2HGly2', NULL, 'male', 'AUSTRALIA', 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 09:19:44', '2026-02-03 09:19:58'),
(20, 'aebfeefa-00e1-11f1-a30f-fa163e8467db', 'dan', 'manishimwe', 'manishimwedan36@gmail.com', '0783273237', '$2y$10$Id.QUKldGFaODL33bmctW.KbHDClPeWtT65EsiZaVikc8HTI/yYpO', NULL, 'male', 'AUSTRALIA', 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 09:21:12', '2026-02-03 09:21:25'),
(21, 'fb8d7ff9-00e7-11f1-a30f-fa163e8467db', 'MUGABO', 'Isaac', 'mugaboisaac@gmail.com', '+250789286220', '$2y$10$6MNt4ERAiFKDg0xltK.CJu/EYKm3H8ipzv5tyuW7gfMvFpZqClEpW', '1998-02-01', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC898772', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:06:18', '2026-02-03 10:06:18'),
(23, 'ba8bc9e3-00e9-11f1-a30f-fa163e8467db', 'mucyo', 'clebere', 'mucyoclebere@gmail.com', '0790224675', '$2y$10$7FSQG2Hz/HhzE/CPH7LOOOrIUzmAjtnwndd8lSe.UonzfP9Myh53S', '2026-02-12', 'male', 'Rwanda', 'Rwanda', 'kabuga\r\nmasaka', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:18:48', '2026-02-03 10:18:48'),
(24, '09c22c73-00ea-11f1-a30f-fa163e8467db', 'NYIRANSEGUYE', 'Guadance', 'nyiranseguyeguadance@gmail.com', '+250783801861', '$2y$10$21GPEB4.L1Jsdcd1hIiQluqZ/.0dUfkp/s2buaxfLr.EhQeKfeOFW', '1995-02-01', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC872770', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:21:01', '2026-02-03 10:21:01'),
(25, '8525a5a7-00ea-11f1-a30f-fa163e8467db', 'MUSHIMIYIMANA', 'Aline', 'mushimiyimanaaline@gmail.com', '+250786668986', '$2y$10$QMAm/447Qr2aJiZ7p00lfuGsE3EJfDaAZ2d3hT0dPgNWEVWsZipYW', '1996-03-12', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC749735', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:24:28', '2026-02-03 10:24:28'),
(26, 'f5bb4440-00ea-11f1-a30f-fa163e8467db', 'SIMPENZWE', 'Anais', 'simpenzweanais@gmail.com', '+250783711066', '$2y$10$NlqrmL3LZzQvKOYpIWMjiuU4AtsuGERXENZR.PvVhHHyx/WrMr4zG', '2000-09-06', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC676682', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:27:37', '2026-02-03 10:27:37'),
(27, '54753502-00eb-11f1-a30f-fa163e8467db', 'MUGABO', 'Pascal', 'mugabopascal@gmail.com', '+250788344760', '$2y$10$UIGdelMAtkDK3pxkN99vuOTvYvMC/mhCYr8P415gqqxLvCfJyItam', '1999-12-12', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC901392', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:30:15', '2026-02-03 10:30:15'),
(29, '37eeb851-00ed-11f1-a30f-fa163e8467db', 'NTIRAMPEBA', 'Jean Claude', 'ntirampebajeanclaude@gmail.com', '+25761233214', '$2y$10$wgSClIxki/2ya.o8FomQz.2jedv9jfSWCK8P3hFerd4kPYTWHDVRi', '1999-12-12', 'male', 'New Zealand', 'Burundi', NULL, NULL, NULL, 'PO047243', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:43:47', '2026-02-03 10:43:47'),
(30, 'a991bd92-00ed-11f1-a30f-fa163e8467db', 'NYAMWERU', 'Therance', 'nyamwerutherance@gmai.com', '+25761233214', '$2y$10$8O20akR2mNuuavtCAo0wO.wCXHoVCDXoP3zFzSC1xAOy.8Mm3VBNu', '1998-12-03', 'female', 'New Zealand', 'Burundi', NULL, NULL, NULL, 'OP0425829', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:46:57', '2026-02-03 10:46:57'),
(31, '26806079-00ee-11f1-a30f-fa163e8467db', 'BIRARONDERWA', 'Damas', 'biraronderwadamas@gmail.com', '+25777160318', '$2y$10$UQSNG/BNFvY0Mp2r4xQAVuDskS.1TMu/YmK2NJVPVgtGjZGIWIIfe', '1991-01-01', 'male', 'New Zealand', 'Burundi', NULL, NULL, NULL, 'PC0096203', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:50:27', '2026-02-03 10:50:27'),
(32, 'e22d66cf-00ee-11f1-a30f-fa163e8467db', 'INGABIRE', 'Jeannette', 'ingabirejeannette@gmail.com', '+250785403197', '$2y$10$bEwtAvlPmh3/PZ0jDBFA.uQgyII6DEoUFqbRz4bTb1TmVFgvJXOYm', '2026-09-30', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC893388', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 10:55:42', '2026-02-03 10:55:42'),
(33, '1fa01cbf-00f1-11f1-a30f-fa163e8467db', 'NYINAWABARI ISHIMO', 'Evangeline', 'ishimoevangeline@gmail.com', '+250796395698', '$2y$10$ImXSzg5.X5yEE/9zeL71te5dgrPrbxc0n1MLIuX9P/X1HpFvhewm6', '1999-05-07', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC832724', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:11:44', '2026-02-03 11:11:44'),
(34, 'ff190fed-00f1-11f1-a30f-fa163e8467db', 'RINDIRO', 'Kenneth', 'rindirokenneth@gmail.com', '+250786782080', '$2y$10$SeUFoLe/sBqyZ4vr.AZbXuj13nYZr9WLPAWgPGifaccOExB52mP5y', '1996-09-12', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC867723', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:17:59', '2026-02-03 11:17:59'),
(35, '6ef700b7-00f2-11f1-a30f-fa163e8467db', 'BIKORIMANA', 'Nathan', 'bikorimananathan@gmail.com', '+250783436882', '$2y$10$DQipHauR4zCOnFF1APIkuOyOyz7lCC.3ok/awxEdPVm37pssz0PxG', '1997-07-13', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC894944', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:21:06', '2026-02-03 11:21:06'),
(36, 'e930dc0f-00f2-11f1-a30f-fa163e8467db', 'MUTUYIMANA', 'Patience', 'mutuyimanapatience@gmail.com', '+250786470810', '$2y$10$d59rS1OLCq8cZX301T7BYe6QqnsnA7jxMY6QWT8BebO2dnLR3U2Qm', '2000-09-12', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC832407', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:24:31', '2026-02-03 11:24:31'),
(37, '553e2e8d-00f3-11f1-a30f-fa163e8467db', 'MURISA', 'Ignace', 'murisaignace@gmail.com', '+250788351822', '$2y$10$JLrg.vHQwr51Xr6m.FW4HuCS1xvD.yk.RtGU9Jcpcv54KDBGWLKdm', '1996-05-06', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC667380', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:27:33', '2026-02-03 11:27:33'),
(38, 'c7838cce-00f3-11f1-a30f-fa163e8467db', 'KARINDA', 'Charles', 'karindacharles@gmail.com', '+250783262681', '$2y$10$79mETRzjHK0DEwHdYN.aaeulp7./wPGk/dv8ufjDtJ1iqnNGBssrC', '1997-12-01', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC897147', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:30:44', '2026-02-03 11:30:44'),
(39, '28a6d647-00f4-11f1-a30f-fa163e8467db', 'IZAHOTURI', 'Fabrice', 'izahoturifabrice@gmail.com', '+250789304673', '$2y$10$2xdCFg9iG1usDeCdJLZE/uRqua5F9DNiiQNZ5r9Hl1DIV0tKH91C2', '1996-12-05', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC689754', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:33:27', '2026-02-03 11:33:27'),
(40, 'e9379845-00f4-11f1-a30f-fa163e8467db', 'HAGUMA', 'Vicent', 'hagumavicent@gmail.com', '+250782560851', '$2y$10$DkxM9GoJrRZUtqomRzvxVuaQFq4GcswQ/MHb3XlPzEimSQ/yZdNOu', '1995-08-07', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC770391', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:38:50', '2026-02-03 11:38:50'),
(41, '4eb70a2f-00f5-11f1-a30f-fa163e8467db', 'MANIRAGUHA', 'Francois', 'maniraguhafrancois@gmail.com', '+250783089367', '$2y$10$GpG1UjBa3DXStwYTBBISgOkuX3X8WJhBM.Tp2smwFT5vidFEWtMTC', '1993-04-02', NULL, 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC800954', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:41:41', '2026-02-03 11:41:41'),
(42, '06e8fdcc-00f6-11f1-a30f-fa163e8467db', 'MVUYEKURE', 'Theogene', 'mvuyekuretheogene@gmail.com', '+250782101922', '$2y$10$hej444jDIqEwZP1iVanKOeYINUpkCQGPXiFHyMVkJ3utnjaXcygFO', '1974-01-01', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1197480044034196', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:46:50', '2026-02-03 11:46:50'),
(43, '98b22827-00f7-11f1-a30f-fa163e8467db', 'BATAMURIZA', 'Beth', 'batamurizabeth@gmail.com', '+250788955829', '$2y$10$Qy5K2RO2kIui1HsmwEE6ZOX9I3gaIA5LVwKw/w2hkUhgdVzuLv2Au', '1990-01-01', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '199070011677032', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 11:58:04', '2026-02-03 11:58:04'),
(44, '4d7b822a-00f8-11f1-a30f-fa163e8467db', 'BISENGIMANA', 'Vicent', 'bisengimanavicent@gmail.com', '+250785477973', '$2y$10$JF3FVqVR/gmLHxphsS7m6.QNO.L1FxuglfXtO71cGPprfnOv9nR8K', '1995-07-08', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC751643', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 12:03:07', '2026-02-03 12:03:07'),
(45, 'db6f0dfb-00f8-11f1-a30f-fa163e8467db', 'URAYENEZA', 'Aloys', 'urayenezaaloys@gmail.com', '+250785227703', '$2y$10$EfFvLsC20fGDpwMgcCD8OOFR/6XBAeXXGrbVXCVo.ChIJ9SvZHA76', '1997-05-06', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC897289', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 12:07:05', '2026-02-03 12:07:05'),
(46, '5394d302-00f9-11f1-a30f-fa163e8467db', 'BATSINDA', 'Gaspard', 'batsindagaspard@gmail.com', '+250783021918', '$2y$10$5APym7PhwqdiuSrDT.IJUuoEGlcvKTm.UFpIG81PDHePEXDE/11u.', '1996-12-08', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC730518', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 12:10:27', '2026-02-03 12:10:27'),
(47, 'b6dee339-00f9-11f1-a30f-fa163e8467db', 'SHIMIRWA', 'Alphonse', 'shimirwaalphonse@gmail.com', '+250786097388', '$2y$10$Tb5kuGmXX4nsRIJnaUIgc.wH8RuLzrVRAM0L.bHEioHWNjBnMGmlO', '1993-02-11', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC892057', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 12:13:14', '2026-02-03 12:13:14'),
(48, '481b1dc5-00fa-11f1-a30f-fa163e8467db', 'UWERA', 'Amina', 'uweraamina@gmail.com', '+250788734740', '$2y$10$mrFcLtsSYOB6Ve8UqEn3je/EGbFBc5fdMCrFVjn/zm3NLDIyPAc0W', '2000-08-05', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC896039', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 12:17:17', '2026-02-03 12:17:17'),
(49, 'eae14a30-00fa-11f1-a30f-fa163e8467db', 'IRAMBONA', 'Junior', 'irambonajunior@gmail.com', '+96567607144', '$2y$10$T6A1edZCjJ0g4kOdM0GFd.o86cKxc7VeRiujWejK6hXO0fyx67jxq', '1997-02-03', 'male', 'New Zealand', 'Burundi', NULL, NULL, NULL, 'PC866783', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 12:21:50', '2026-02-03 12:21:50'),
(50, '90f60b8a-00fe-11f1-a30f-fa163e8467db', 'BRYAN ARTHUR', 'GASANA', 'gasanaarthurbryan@gmail.com', '+250789322276', '$2y$10$6MTV.p5f2rdXkepfpqRllOLFlx.LIgE.36L.9HZ7SAuizCuLDCWSi', '2002-01-10', 'male', 'luxembourg', 'Luxembourg', NULL, NULL, NULL, 'pc664033', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 12:47:57', '2026-02-03 12:48:20'),
(51, '7bff6d1f-0102-11f1-a30f-fa163e8467db', 'QUERCY', 'AKEZA', 'akezaquery@gmail.com', '+25765608540', '$2y$10$sKkRgWF6MjcMlFq4T0D/XeEwWEKR5URgvbds8qZyrnqKdd0bcdCS.', '2000-01-01', 'male', 'Serbia', 'Serbia', NULL, NULL, NULL, '0P0385314', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 13:16:00', '2026-02-03 13:16:00'),
(52, '43e081b5-0103-11f1-a30f-fa163e8467db', 'MANASSEH', 'MUSHINZIMANA', 'atolgloballinksolutions@gmail.com', '+250788269809', '$2y$10$lh3I9Yap8COpMZXbDyzZsubv/7MuXy6EP.P9Q.fDsaiGYlep0JJDe', '2000-01-01', 'male', 'SERBIA', 'Rwanda', 'KIGALI', NULL, NULL, 'PC901362', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 13:21:36', '2026-02-03 13:22:09'),
(53, 'a321864d-0110-11f1-a30f-fa163e8467db', 'admin', 'sangwa', 'admn@gmail.com', NULL, '$2y$10$h87QGetN/xOWI.JxcmRDqOVOxqyjoOhHEcR77IlxM53hV56k2dPyK', '2026-02-02', NULL, 'Australia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-03 14:57:19', '2026-02-03 14:57:19'),
(54, '4973946a-0112-11f1-a30f-fa163e8467db', 'Divin', 'Nayihiki', 'ndivin01@gmail.com', NULL, '$2y$10$QhJ4nCwiITe7V7e6SoF0f.yurjqfpgziQlBKH91ltvZGSOHGhL7Zy', '1990-01-01', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-03 15:09:07', '2026-02-03 15:09:07'),
(55, '4ca430b3-0114-11f1-a30f-fa163e8467db', 'NDISANZE', 'JEAN BAPTISTE', 'ndisanzejeanbaptiste@gmail.com', '+250783588409', '$2y$10$WaELlzEWcYy/NXlNul7XZ.tUPT7FusxX.b8A3qjxOWrnRCI0ueP0.', '1997-01-01', 'male', 'Rwanda', NULL, 'kigali', 'kigali', '0000', 'PC87783', '2034-12-12', 'Teacher', 'Ndisanze Jean Baptiste', '+250783588409', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-03 15:23:32', '2026-02-03 15:27:36'),
(56, '822785ea-0114-11f1-a30f-fa163e8467db', 'Patrick', 'Kayiranga', 'mutoniwaselydia55@gmail.com', NULL, '$2y$10$X7tF2LqEaShoCi6JRSga2u/reS3MEX42i8Z48boJ53N6PwhXhTide', '1991-01-01', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-03 15:25:01', '2026-02-03 15:25:01'),
(57, 'a023a798-0114-11f1-a30f-fa163e8467db', 'UWAMAHORO', 'Justine', 'uwamahorojustine@gmail.com', '+250787549673', '$2y$10$XVIlmxlHfNio.W9OivbRa.4Kbbn7BL00CaEDCho.QmbtLM1zwRALG', '1998-12-04', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC897146', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 15:25:52', '2026-02-03 15:25:52'),
(58, '8d525b7a-0115-11f1-a30f-fa163e8467db', 'UWINEZA  MARIE', 'Ladouce', 'uwinezamarieladouce@gmail.com', '+250783273237', '$2y$10$Ujc8Zvg8J4BPDjDARrIutOjVaBeFF7Nw83Y5CBI4XfyK79bhpq8cC', '1995-04-05', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC896199', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 15:32:30', '2026-02-03 15:32:30'),
(59, '0b7d5df5-0116-11f1-a30f-fa163e8467db', 'UWAMBAJIMANA', 'Epaphrodite', 'uwambajimanaepaphrodite@gmail.com', '+250788455696', '$2y$10$TTJWYTH0gbIMA8ui0jfhDug6o.r7bU9/WJvmlhCedf5AYYyHYDU.2', '1996-12-08', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC753047', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 15:36:01', '2026-02-03 15:36:01'),
(60, 'a860c41f-0116-11f1-a30f-fa163e8467db', 'KWIZERA', 'Patrick', 'kwizerapatrick@gmail.com', '+250781650471', '$2y$10$6StWUDkvB08F6F8B4FO1ReR0wHmdnBcLkjn2C047rCHFQtXX3VXG2', '1993-03-06', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC786544', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 15:40:25', '2026-02-03 15:40:25'),
(61, '1ff1ff26-0117-11f1-a30f-fa163e8467db', 'BIRUNGI', 'Scovia', 'birungiscovia@gmail.com', '+250788426185', '$2y$10$6Z4o6kYCPn8q2f0fHsYLPekiqLK6Ti4iJ4vWfbDMUUT86d07uHunG', '1999-05-12', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC712565', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 15:43:45', '2026-02-03 15:43:45'),
(62, '3767c8cf-0118-11f1-a30f-fa163e8467db', 'MUTANGANA', 'Olivier', 'mutanganaolivier@gmail.com', '+250788276654', '$2y$10$VMyy/6M5uJrZhGlw05XaGulGfi.aXyPXqNZp9gusKXLU2dokWgoke', '1996-09-07', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1199480195236H177', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 15:51:34', '2026-02-03 15:51:34'),
(63, '196b3735-0119-11f1-a30f-fa163e8467db', 'RUGUMIRE MUKARAGE', 'Fleuron', 'rugumiremukarage@gmail.com', '+250784362979', '$2y$10$oSdfiqs6tKWMZsPmcJ6paeRAmnnRbxtty2Uga6ScccnTFNVHFZ8hK', '1998-12-12', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC832793', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 15:57:53', '2026-02-03 15:57:53'),
(64, '99e66130-0119-11f1-a30f-fa163e8467db', 'NGABONZIZA', 'Aloys', 'ngabonzizaaloys@gmail.com', '+250788356466', '$2y$10$lIqK86NX/RNWtql4ZZ2SSeqDCP/gs96VjR.mrndg0GJWDwi2.MZz2', '1998-07-09', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC897914', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:01:29', '2026-02-03 16:01:29'),
(65, '464c2078-011a-11f1-a30f-fa163e8467db', 'UYISABYE', 'Sother', 'uyisabyesother@gmail.com', '+250784502599', '$2y$10$6K8nHMbb30ws4sJWlmtk8u.SuPFbgse3nIKlRsfHxVGIPZT6Q7t.2', '2000-02-11', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC882894', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:06:18', '2026-02-03 16:06:18'),
(66, 'a618690d-011a-11f1-a30f-fa163e8467db', 'NDAHIRIWE', 'Jules', 'ndahiriwejules@gmail.com', '+250787381327', '$2y$10$MsGblLQ2/Me6RCXw3FiJF.PM1nN96Lp4HB.3UiewTPT0lA7mM683S', '2001-12-26', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC889596', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:08:59', '2026-02-03 16:08:59'),
(67, '223396d3-011b-11f1-a30f-fa163e8467db', 'NZOGERA', 'Felecien', 'nzogerafelecien@gmail.com', '+250788533236', '$2y$10$WhOjMhW8czXPo0I.3jTtGeS1P8ACnZ060tV7EDr4rW8Tf/rwtjb9y', '1973-04-08', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1197380100633030', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:12:27', '2026-02-03 16:12:27'),
(68, '7344d2e7-011b-11f1-a30f-fa163e8467db', 'RUSINGIZWA', 'Augustin', 'rusingizwaaugustin@gmail.com', '+250788807359', '$2y$10$QTV.AAASrOtJCkw7g8T9a.9z7p93mQhptZ5Dm9tpQTCG2c7xtRjSG', '1999-12-22', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC790691', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:14:43', '2026-02-03 16:14:43'),
(69, '08e9e799-011c-11f1-a30f-fa163e8467db', 'MANIRIHO', 'Ereneste', 'manirihoereneste@gmail.com', '+250788226191', '$2y$10$6WZHgFUw0EjxbE.WWnDfIeObAdaVfMfGLj8mxt3q3pzXlhnNqyUbK', '2000-03-12', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1198580107694147', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:18:54', '2026-02-03 16:18:54'),
(70, '97a0271f-011c-11f1-a30f-fa163e8467db', 'HAGENIMANA', 'Emmanuel', 'hagenimanaemmanuel@gmail.com', '+250786456033', '$2y$10$NBYVzGCENiS03g1E82uDVujoZSCXLZvQ6jEZzPNG9GJhzXZ/w4Uru', '1982-11-11', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1198280201513048', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:22:53', '2026-02-03 16:22:53'),
(71, '0c67e9ef-011d-11f1-a30f-fa163e8467db', 'RUKUNDO', 'Christian', 'rukundochistian@gmail.com', '+25072777380', '$2y$10$y6QYPfD8PKr84uSt67k7dOCcPV1VYNy6tIGURb0ivZfnriaCjiA6C', '2000-11-02', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC733770', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:26:09', '2026-02-03 16:26:09'),
(72, '557a5d7f-011d-11f1-a30f-fa163e8467db', 'NAHIMANA', 'Theoneste', 'nahimanatheoneste@gmail.com', '+250788553323', '$2y$10$OWajk3nhQ8nxsKv12Dqd3uGoMKuww7vS3mqBcm9MFy7QWjhSavPwe', '1997-02-06', NULL, 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC897509', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:28:12', '2026-02-03 16:28:12'),
(73, 'cab24f1a-011d-11f1-a30f-fa163e8467db', 'HABONIMANA', 'Lionel', 'habonimanalionel@gmail.com', '+250789712049', '$2y$10$UpNiKYohx6LrQxFRTM6yvOwGv3Ilbo9iBy2FcTS8PQjKpKFkeStMO', '2002-02-02', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PCRO12320', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-03 16:31:29', '2026-02-03 16:31:29'),
(74, '4c77c381-013e-11f1-a30f-fa163e8467db', 'albert', 'munyanshongore', 'munyL2020@gmail.com', NULL, '$2y$10$c..wle4AOF6UFInXAETdLuBUGXQbpDs.MsFzm0WYt2NAPH5I.4A92', '2000-01-01', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-03 20:24:10', '2026-02-03 20:24:10'),
(75, '69516110-0140-11f1-a30f-fa163e8467db', 'JEAN DAMOUR', 'NZAYISENGA', 'nzayisengadamour10@gmail.com', NULL, '$2y$10$iw1jtzsOYF7dYpoGuuZEAO5EMHosAR4tbJ/T/QPvN2Q2bR3FFBJ3i', '1995-01-02', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-03 20:39:18', '2026-02-03 20:39:18'),
(76, 'd3409871-014f-11f1-a30f-fa163e8467db', 'Maniraguha', 'Placide', 'maniraguhaplacide86@gmail.com', '+260952726263', '$2y$10$67MeG6c8hMZ4ajNr05JVDeEBYKhdE5uexWVw53ybiUTC9IDOPUIlW', '2000-06-16', 'male', 'Zambia', 'Rwandan', 'Great east chongwe', 'Lusaka', 'E2E2E2 ', 'PC 625833', '2027-10-13', 'Business man', 'Maniraguha Placide', '+260952726263', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-03 22:29:38', '2026-02-03 22:35:26'),
(77, '3391c334-01a3-11f1-a30f-fa163e8467db', 'Mwubahamana ', 'Martin ', 'mwubahamanamartin6@gmail.com', NULL, '$2y$10$bdqg4JCQ8gEBIrR6eeb3Y.WlWqFL0Tkjz2e.xl2fKS3L4qaM/kZt2', '1998-10-30', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-04 08:26:28', '2026-02-04 08:26:28'),
(78, '818f213e-01c2-11f1-a30f-fa163e8467db', 'Dieudonne', 'Nduwayezu', 'nduwayedieudonne@gmail.com', '+25766120457', '$2y$10$wIr1CWK.roIEqw8waLjhguOqDY7UxUhH5nyTizSKWmlC.jjIpNmXy', '1999-10-09', 'male', 'Burundi', 'Burundi', 'Rohero -bujumbura-burundi', 'Cibitoke-Ntahangwa-Bujumbura', '0000', 'P00166369', '2035-03-10', 'Informaticien', 'NDUWAYEZU Dieudonne ', '+25766120457', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-04 12:10:33', '2026-02-04 12:14:19'),
(79, 'a0a3744b-01c5-11f1-a30f-fa163e8467db', 'Uwamariya ', 'Delphine ', 'delphineuwamariya966@gmail.com', '+250787021520', '$2y$10$ZqWqkdDzyxh2.15js1GUquDxgSe2jCnOgFI3Bqjang648ewJAbZna', '1994-04-01', 'female', 'Rwanda', 'Rwandan ', 'Kicukiro ', 'Kigali ', '00000', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-04 12:32:54', '2026-02-04 12:34:49'),
(80, '269b1563-01c8-11f1-a30f-fa163e8467db', 'SANGWA', 'Emerance', 'sangwaemerance@gmail.com', '+250784502599', '$2y$10$OENZcsqIC.7CpW/Q6kSP7.TVj1rDJnAkTJJl.bY/xxcL5mktTjn1e', '1998-03-04', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC819585', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 12:50:57', '2026-02-04 12:50:57'),
(81, 'e8c42ead-01c8-11f1-a30f-fa163e8467db', 'MUKUNDWA', 'Rosine', 'mukundwarosine@gmail.com', '+971505893708', '$2y$10$BOLJTrzKAeOJRWEha/l7ceI99vdM/uZSMOrvELfM//aEE7KKnwvD6', '1997-12-22', 'female', 'Australia', 'United Arab Emirates', NULL, NULL, NULL, 'PC651206', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 12:56:23', '2026-02-04 12:56:23'),
(82, '6e86e0ed-01c9-11f1-a30f-fa163e8467db', 'TUYISHIMIRE', 'Wilson', 'tuyishimirewilson@gmail.com', '+250786136164', '$2y$10$F0PHNfnsLSC/gRVxEEGb/umPL9V49UiAmMBAS2jNQbWXXe9q9ZJ0S', '2000-03-12', 'male', 'Luxembourg', 'Rwanda', NULL, NULL, NULL, 'PC755567', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:00:07', '2026-02-04 13:00:07'),
(83, '0dfa7935-01ca-11f1-a30f-fa163e8467db', 'NGARUYINKA', 'Djuma', 'ngaruyinkadjuma@gmail.com', '+250784243376', '$2y$10$iGXQ7R2RQQy3788RnVpFYuuMf4RD2hfGL5eOS5/MfkFhJy67laBmO', '1995-12-06', 'male', 'Luxembourg', 'Rwanda', NULL, NULL, NULL, 'PC811468', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:04:35', '2026-02-04 13:04:35'),
(84, '718bc4f5-01ca-11f1-a30f-fa163e8467db', 'NKUSI', 'Yves', 'nkusiyves@gmail.com', '+250787275165', '$2y$10$aS8RllgO/OpgiTJx3DCqvO817szB5n6bdEN7f2XenRLgaslDIhq6u', '1995-04-07', 'male', 'Luxembourg', 'Rwanda', NULL, NULL, NULL, 'PC706360', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:07:22', '2026-02-04 13:07:22'),
(85, '209747e2-01cb-11f1-a30f-fa163e8467db', 'NKURUNZIZA', 'Huggor', 'nkurunzizahuggor@gmail.com', '+25769144608', '$2y$10$kj8EcbTUWcIxQ1oCeMzVLOJDVMzBPe3gDHsJxLsP3zXLswOQ6kI5a', '2000-06-09', 'male', 'Australia', 'Burundi', NULL, NULL, NULL, 'OP0393971', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:12:16', '2026-02-04 13:12:16'),
(86, 'a044f41a-01cb-11f1-a30f-fa163e8467db', 'MAHORO HILAIRE', 'Didier', 'mahorohilairedidier@gmail.com', '+250785291329', '$2y$10$lD4JnfiQfmNna6Qu9J3ia.W5LzXUBsKIySOdm70jJYiTBOO2VnD5C', '1998-05-12', 'male', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC565895', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:15:50', '2026-02-04 13:15:50'),
(87, '0dbde727-01cc-11f1-a30f-fa163e8467db', 'NSENGIYUMVA', 'Vicent', 'nsengiyumvavicent@gmail.com', '+250788491710', '$2y$10$fD7mH5ZI.d.s2lDgOPHZp.2IOvvHYQH9NbTtnFwnmfZlkR.PgBh/S', '1996-05-06', 'male', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC755514', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:18:54', '2026-02-04 13:18:54'),
(88, 'a0ddada5-01cc-11f1-a30f-fa163e8467db', 'BUGINGO', 'Jean Bosco', 'bugingojeanbosco@gmail.com', '+250789265261', '$2y$10$sGzdOXZpYeZkHJGA7wui4OiRvcx.MJygLs4PBLb4G8VqwulaDb2aO', '1984-06-09', 'male', 'Australia', 'Rwanda', NULL, NULL, NULL, '1198480179674007', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:23:00', '2026-02-04 13:23:00'),
(89, '2799ce48-01cd-11f1-a30f-fa163e8467db', 'NIRINGIYIMANA', 'Remy', 'niringiyimanaremy@gmail.com', '+250789994658', '$2y$10$N8QqnKMzq7VAgQDuL.ltVOsz1qNdar/Z6.Kd7Xu4ptFMHQc6zfOKC', '1999-03-09', 'male', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC509418', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:26:46', '2026-02-04 13:26:46'),
(90, '909e8f10-01cd-11f1-a30f-fa163e8467db', 'KANA', 'Evariste', 'kanaevariste@gmail.com', '+250792107388', '$2y$10$8fnGvFiu4Czy50U.P/Qohutec4NefRucAEHTF8wgl8Tx6x/9s6TNi', '2000-12-12', 'male', 'Luxembourg', 'Rwanda', NULL, NULL, NULL, 'OP0283761', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:29:43', '2026-02-04 13:29:43'),
(91, '3e34ac31-01ce-11f1-a30f-fa163e8467db', 'NSENGIYUMVA', 'Alphosne', 'nsengiyumvaalphonse@gmail.com', '+250787669865', '$2y$10$33fteVC0rXBKb9GP8UFwM.CoVVPLbu7XHdKyHe/989UyYEKYdOdAy', '1999-11-21', 'female', 'Australia', 'Rwanda', NULL, NULL, NULL, '1199980114116083', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:34:34', '2026-02-04 13:34:34'),
(92, 'ad7f175f-01ce-11f1-a30f-fa163e8467db', 'KAMUKAMA', 'Eva', 'kamukamaeva@gmail.com', '+250788825255', '$2y$10$WfOWZNWhDqUDrZufk6nCeuV7ibMpmvzgQNMNPJ1xaULjI7DWBldeW', '1997-04-08', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC713984', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:37:41', '2026-02-04 13:37:41'),
(93, '3d94ecff-01cf-11f1-a30f-fa163e8467db', 'TUYISENGE', 'Beatrice', 'tuyisengebeatrice@gmail.com', '+250780348107', '$2y$10$7ZP5tLZW5CfsZ1LHdRPNWul1GYeuylFnLb.G7O0NJ4ODSEBDWne7q', '1999-12-31', 'female', 'Mauritius', 'Rwanda', NULL, NULL, NULL, 'PC891935', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:41:42', '2026-02-04 13:41:42'),
(94, '00199a02-01d0-11f1-a30f-fa163e8467db', 'NYIRANSEGUYE', 'Mary Guadance', 'nyiranseguyemaryguadance@gmail.com', '+250783801861', '$2y$10$9zBy4b/nEsgyo5dvVFf.mOFD3gmMf31CE5BJb0oDMFbaU8XuPylt2', '1996-12-23', 'female', 'Mauritius', 'Rwanda', NULL, NULL, NULL, 'PC872770', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:47:09', '2026-02-04 13:47:09'),
(95, '774a6e18-01d1-11f1-a30f-fa163e8467db', 'MUSHIMIYIMANA', 'Aline', 'mushimiyimanaaline2@gmail.com', '+250786668986', '$2y$10$4VoFlmJ8foffZxa2Mmya8OgFw5Q.aGFKUooFutsc2PLBor7SVGwnO', '1996-03-04', 'female', 'Mauritius', 'Rwanda', NULL, NULL, NULL, 'PC749735', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 13:57:38', '2026-02-04 13:57:38'),
(96, '346531c1-01d2-11f1-a30f-fa163e8467db', 'HAGUMA', 'Vicent', 'hagumavicent2@gmail.com', '+250782560851', '$2y$10$uJVBNqWAmd/sF9n3ntsYN.k4npbbnmiLFANxnBRbyP4noCzb2//3C', '1996-02-06', 'male', 'Mauritius', 'Rwanda', NULL, NULL, NULL, 'PC770391', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:02:55', '2026-02-04 14:02:55'),
(97, 'c20d77a5-01d2-11f1-a30f-fa163e8467db', 'MUHIMPUNDU', 'Christelle', 'muhimpunduchristelle@gmail.com', '+250788981717', '$2y$10$Rnw2bO2gfu8QxnMkbEowJ.DUYv88UpRIHDqLZJFDiLl/wBSD8upJ.', '1997-07-09', 'female', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC882246', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:06:53', '2026-02-04 14:06:53'),
(98, '30a6aaca-01d3-11f1-a30f-fa163e8467db', 'SHEMA', 'Martin', 'shemamartin@gmai.com', '+250783772101', '$2y$10$I/5wiTHGCIRqURvmiNPLCOG6nMODzfrfBjLSurhCXMTK6R0JjYSJm', '2000-03-08', 'male', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC676507', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:09:59', '2026-02-04 14:09:59'),
(99, 'b17b5e5d-01d3-11f1-a30f-fa163e8467db', 'NSHIMIYIMANA', 'Bondy', 'nshimiyimanabondy@gmail.com', '+250788816061', '$2y$10$b8N6w7Tz76xojJVAs8LaqOdG9uy5D1LMg809NWoAAKPbI7JlPfEK2', '2000-02-12', 'male', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC711387', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:13:35', '2026-02-04 14:13:35'),
(100, '4cd24f4c-01d4-11f1-a30f-fa163e8467db', 'BYIRIGIRO', 'Robert', 'byiringirorobert@gmail.com', '+250781960388', '$2y$10$RKFNAzcwOx.WLNtJPr4phOV08JoWR50iM7vo3vCED1jKA1RFaXPQW', '1999-04-07', 'male', 'Mauritius', 'Rwanda', NULL, NULL, NULL, 'PC804358', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:17:55', '2026-02-04 14:17:55'),
(101, 'b6e8457c-01d5-11f1-a30f-fa163e8467db', 'NIYITEGEKA', 'Charlotte', 'niyitegekacharlotte@gmail.com', '+250782787670', '$2y$10$jD/wB8OfZvuVrmcJ1/3zEeiW5TR3BtHBiHu4VOLAhuBPFJkmA/9YO', '1997-09-03', 'female', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC873287', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:28:03', '2026-02-04 14:28:03'),
(102, 'c1793808-01d6-11f1-a30f-fa163e8467db', 'UMUGANWA', 'Remy Kevin', 'umuganwaremykevin@gmail.com', '+250788526400', '$2y$10$fTxx4EOtGiHSj6a0qgCwDe1pjpnYHYW0pld3oMfb/higgsSq1.Sha', '1996-12-22', 'male', 'Czech Republic', 'Rwanda', NULL, NULL, NULL, 'PC658086', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:35:30', '2026-02-04 14:35:30'),
(103, '286a6dd5-01d7-11f1-a30f-fa163e8467db', 'HATEGEKIMANA', 'Djihad', 'hategekimanadjihad@gmail.com', '+250788982429', '$2y$10$thTuE6s/2qaChah0nsfpVu0PUDSe6Mm3hE/1THgbuAfJxTHALEU7.', '1997-05-09', 'male', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC644019', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:38:23', '2026-02-04 14:38:23'),
(104, '4e10e7b7-01d7-11f1-a30f-fa163e8467db', 'NZEYIMANA ', 'REVERIEN ', 'reverien2000@gmail.com', NULL, '$2y$10$5PyaFGqitiZYg5T7.q7m5eWTqZ5.hiQt6Xh8t9p9Ys9qBblAu8EGO', '1983-10-18', NULL, 'Burundi', 'Burundi ', 'Bujumbura ', 'Bujumbura ', NULL, 'OP0379633', '2033-03-03', 'Engineering civil ', 'Devotte ', '+25761020495', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-04 14:39:26', '2026-02-04 14:45:48'),
(105, '92dae480-01d7-11f1-a30f-fa163e8467db', 'MBASHIMIRIMANA', 'Oscar', 'mbashimirimanaoscar@gmail.com', '+250788624252', '$2y$10$ZopsxwetuTXVoEKVJP1sKOjJVz44KINyXQRyl5EnOVukyUGLMk5xK', '1998-02-11', 'male', 'Mauritius', 'Rwanda', NULL, NULL, NULL, '702126', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 14:41:21', '2026-02-04 14:41:21'),
(106, 'b64027e8-01db-11f1-a30f-fa163e8467db', 'Honore ', 'Haguma', 'hhaguma3@gmail.com', NULL, '$2y$10$o53VlyleooFES30i2P8hW.WaCad/xbuOzdIOrsTULS2IpSqLJnDfi', '1984-03-12', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-04 15:10:59', '2026-02-04 15:10:59'),
(107, '23a6fcd0-01dc-11f1-a30f-fa163e8467db', 'TUYISENGE', 'Beatrice', 'tuyisengebeatrice2@gmail.com', '+250780348107', '$2y$10$BEUuQTqr6dHpsniavuFFT.a3q4Z5qKAVQ9F6/EVy.yZh/7GHhV3K6', '1998-03-02', 'female', 'Mauritius', 'Rwanda', NULL, NULL, NULL, 'PC891935', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 15:14:02', '2026-02-04 15:14:02'),
(108, '44e1109d-01dd-11f1-a30f-fa163e8467db', 'ISHIMWE', 'Schiphra', 'ishimweschiphra@gmail.com', '+250781268818', '$2y$10$2jV6FbTE0pMzKtmyTJ6hNOXGRCeHp/n0cmD2SHjZpiH1ic6akvMsO', '2000-12-12', 'female', 'France', 'Rwanda', NULL, NULL, NULL, 'PC606463', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 15:22:08', '2026-02-04 15:22:08'),
(109, '8afe4d4a-01de-11f1-a30f-fa163e8467db', 'DUSHIMIMANA', 'Bienvenue', 'bienvenuedushimimana@gtmail.com', '+250787723532', '$2y$10$VnVgV7KwrfisAQxWnan6weWG6lGCN8835.nThfzrMNVDgc9Rl2FWi', '1997-06-06', 'male', 'Serbia', 'Rwanda', NULL, NULL, NULL, 'PC620198', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 15:31:15', '2026-02-04 15:31:15'),
(110, 'f57f0ce7-01de-11f1-a30f-fa163e8467db', 'ATETE', 'Aline', 'atetealine@gmail.com', '+250782601725', '$2y$10$Tu87f0lfyrgkrSoLQM/mH.b8HzEQE92d/cvt.kAGZCa3ZC64rPy7u', '1996-02-12', 'female', 'Luxembourg', 'Rwanda', NULL, NULL, NULL, 'PC877085', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 15:34:13', '2026-02-04 15:34:13'),
(111, '6af52fe5-01df-11f1-a30f-fa163e8467db', 'NDAYEGAMIYE', 'Louis', 'ndayegamiyelouis@gmail.com', '+250780585594', '$2y$10$Z1nXi/wot3w7uCEb5FLFLOIvT.s47QMPuhgWflMJn4Ant2//c5PWS', '1997-03-09', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'OP0406848', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 15:37:30', '2026-02-04 15:37:30'),
(112, 'e2298bf2-01df-11f1-a30f-fa163e8467db', 'BIKORIMANA', 'Joseph', 'bikorimanajoseph@gmail.com', '+25078564998', '$2y$10$sxSqyP2.wemIh6UUFqS.r..5v4ezUUFyIHYDFoWGH4b46HN2k6kju', '1996-04-08', NULL, 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC895234', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 15:40:50', '2026-02-04 15:40:50'),
(113, '92056033-01e0-11f1-a30f-fa163e8467db', 'MUHOZA', 'Juslaine', 'muhozajuslaine@gmail.com', '+250782222303', '$2y$10$3JWR90eNUO88VQyR0iFPC.7w5IyUrrEsCILNPr8GQs0/wyfe6CF92', '1999-07-31', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'P816849', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 15:45:45', '2026-02-04 15:45:45'),
(114, '00a51bfc-01e1-11f1-a30f-fa163e8467db', 'UWIMANA', 'Enock', 'uwimanaenock@gmail.com', '+250785254654', '$2y$10$3w.ueLntOM6exk5WO5Kpae2sUqUDV/7zd51xhyeqVKcTu8M9oyGaG', '1998-03-09', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC753726', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 15:48:51', '2026-02-04 15:48:51'),
(115, '7938fa4f-01e3-11f1-a30f-fa163e8467db', 'BARAKARAMA', 'Dieudonne', 'barakaramadieudonne@gmail.com', '+250782652868', '$2y$10$cu6QOqclnPEftt2.cklSjuXxer78bzoGkXKdYKEdXKlu.QYL6z3nq', '1991-12-08', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC901267', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-04 16:06:32', '2026-02-04 16:06:32'),
(116, '04d4198f-0202-11f1-a30f-fa163e8467db', 'Eric', 'Dusabirane', 'edusabirane985@gmail.com', '+250786396711', '$2y$10$BfuwSXjEHf88S3OmoAoQWu3QXP1nJbbV/T9wVBnglPtbn4AU3su.y', '1994-02-01', 'male', 'Rwanda', 'Rwandan ', 'Kigali', 'Gasabo', '00000', 'Pc1234', '2030-02-04', 'Truck driver ', 'Eric Dusabirane', '+250786396711', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-04 19:45:11', '2026-02-04 19:46:38'),
(117, '71db3166-020e-11f1-a30f-fa163e8467db', 'Igiraneza', 'Edgard', 'igiranezaedgard01@gmail.com', NULL, '$2y$10$u24iTr/9B8DbeIbE7niCLuDbhGEzYpvA1UkwyiYLkAmgBvG/kXPf.', '2004-04-30', NULL, 'Burundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-04 21:14:08', '2026-02-04 21:14:08'),
(118, 'febf3d72-0224-11f1-a30f-fa163e8467db', 'Pacifique', 'Irakoze', 'pacifiqueirakoze999@gmail.com', NULL, '$2y$10$HIKsafJQmR42EW85Y2EqXeTCHaL1U1ppLB2DXGqahm.aD7ozHCSk6', '1999-04-22', NULL, 'Burundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-04 23:55:34', '2026-02-04 23:55:34'),
(119, '73a1264d-0237-11f1-a30f-fa163e8467db', 'Felix', 'Mafubo', 'primysaf20@gmail.com', NULL, '$2y$10$qSVgAEkO/KXK1zijCQc1Nu5jQyXoWsbdPooydO49FRi9LOrg8GziW', '1988-01-01', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-05 02:07:41', '2026-02-05 02:07:41'),
(120, '17223509-0262-11f1-a30f-fa163e8467db', 'NTIVUGURUZWA ', 'Emmanuel', 'emmyntivu@gmail.com', NULL, '$2y$10$pBzRZC8615bbtWI5aS6moO33QYR7ZjOgxHlaI7omIhH9hH4p6g8C6', '1997-06-29', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-05 07:12:54', '2026-02-05 07:12:54'),
(121, '00631bff-0266-11f1-a30f-fa163e8467db', 'iradukunda', 'Diego', 'diegoiradukunda6@gmail.com', '+25771215531', '$2y$10$aWDLUuko9vFuuwcDkwqHwOqFEKbbWRWwDbxGgKXwzl1/NDbURHAJC', '2002-08-11', 'male', 'Burundi', 'Burundian ', 'ROHERO', 'BUJUMBURA', NULL, 'P00041753', '2034-03-15', 'Network Engineering ', 'Ishimwe Bruce', '61530018', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-05 07:40:54', '2026-02-05 07:44:17'),
(122, '0dbc8ae6-026b-11f1-a30f-fa163e8467db', 'MANIRABARUTA', 'Damien', 'manadamien52@gmail.com', '+250728207072', '$2y$10$vus.XXO/0j8tnRX53kPOSurYJ1CUPPa/KsiIpHJPmzjuK6ujB/b3S', '1991-01-01', 'male', 'Rwanda', 'Rwandan', 'Kigali Rwanda', 'Kigali', NULL, 'PC751009', '2033-07-31', 'Agronomist', 'TWIZERIMANAarceline', '+250782531634', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-05 08:17:04', '2026-02-05 08:19:48'),
(123, '37c9f733-026d-11f1-a30f-fa163e8467db', 'Urbain ', 'Elvis', 'ndayizeyeurbainelvis@gmail.com', '+25768155679', '$2y$10$j2xVc0yt1sPbnbpzJJ7p7ezye88uKTaxxm9G0kGSBxofwkIn9C1Ge', '2002-07-09', 'male', 'Burundi', 'Burundi', 'Bujumbura ', 'Bujumbura', '1000', 'P00016606', '2033-12-26', '-', 'Urbain Elvis Ndayizeye', '+25768155679', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-05 08:32:33', '2026-02-05 08:40:07'),
(124, '6e821e29-0271-11f1-a30f-fa163e8467db', 'Ciella ', 'Nininahazwe ', 'nininahazweciella149@gmail.com', NULL, '$2y$10$PiFMv2cRdDN3rlYeQj4U3Ox/zAzLPlOFD6zpwZpC2ZhThrx5nQz8W', '2006-07-25', NULL, 'Burundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-05 09:02:43', '2026-02-05 09:02:43'),
(125, '9b896090-0278-11f1-a30f-fa163e8467db', 'BAYUBAHE', 'LEONIDAS', 'choralesayuni5@gmail.com', '62-40-95-28', '$2y$10$B7xXNko14U7MQJYu8cl4nutHgrE9lBjnVojwqofKz3p2RMC2YORUS', '1996-01-01', 'male', 'Burundi', 'Burundi ', 'Mutakura', 'Bujumbura', NULL, 'P00259208', '2035-11-06', 'General Labourer ', 'BAYUBAHE LEONIDAS', '+25762409528', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-05 09:54:05', '2026-02-05 10:03:18'),
(126, '4945fa10-027e-11f1-a30f-fa163e8467db', 'HATUNGIMANA', 'Francis', 'francishatugimana15@gmail.com', '+259784409112', '$2y$10$Em6F.ypnnd.4rTIn7vMuBuEyuKInMJGI.JWdYnRpCswQT1D1FELsy', '1992-05-08', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1199280221606174', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 10:34:44', '2026-02-05 10:34:44'),
(127, 'fc2825be-027e-11f1-a30f-fa163e8467db', 'KABAGEMA', 'Fabrice', 'manirihoernest@gmail.com', '+250782657829', '$2y$10$yMbM2w7ocrmv.96FjiP1i.bjNxevLYkot7V070kjo0j3h.NkJhI4.', '1996-12-31', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC895270', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 10:39:44', '2026-02-05 10:39:44'),
(128, '670b85cc-027f-11f1-a30f-fa163e8467db', 'NTAGAWA', 'Mwungura', 'ntagawamwungura@gmail.com', '+250783216598', '$2y$10$T/DsyMGd3DuI/BnLMlohN.FEs9G6ybPmWLD.dH8qFhlY1CgCfrMHq', '1997-04-08', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC612298', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 10:42:43', '2026-02-05 10:42:43'),
(129, '0f255198-0280-11f1-a30f-fa163e8467db', 'MUTABAZI', 'Alian Rene', 'mutabazialianrene@gmail.com', '+250788526400', '$2y$10$uSUJhmk9lrggH4rTegHCB.fY03p8yYkodSogVzRjVjeddPxitpyna', '1984-03-06', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '119848009262416', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 10:47:25', '2026-02-05 10:47:25'),
(130, 'c3bcad78-0280-11f1-a30f-fa163e8467db', 'LUBINGA', 'Ivan', 'lubingaivan@gmail.com', '+250784214783', '$2y$10$2eW4eiYD00kMab/Ke2eJq.fNWp4BKJMtoNkx505AKScrmw6H5rVuG', '1997-09-08', 'male', 'New Zealand', 'Uganda', NULL, NULL, NULL, 'A00042896', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 10:52:28', '2026-02-05 10:52:28'),
(131, '36f49225-0281-11f1-a30f-fa163e8467db', 'UWAMAHORO', 'Rachel', 'uwamahororachel@gmail.com', '+250788220733', '$2y$10$zpvvY1K52kJFhezXt2RhIOvjIedEFlj53rjGJsmQxASF42x/VFcga', '1999-03-06', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC506307', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 10:55:42', '2026-02-05 10:55:42'),
(132, '42b90c01-0282-11f1-a30f-fa163e8467db', 'MUKAHIRWA', 'Hosanna', 'terickfr22@gmail.com', '+250788747722', '$2y$10$EdtSRyvN.NqkmkjLbMdB..l8DO/lNLDfKzoyMfwdrCa27i01BetZy', '1977-12-12', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1197770004026295', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 11:03:11', '2026-02-05 11:03:11'),
(133, 'b8b0d97a-0282-11f1-a30f-fa163e8467db', 'HITAYESU', 'Fabrice', 'hitayesufabrice@gmail.com', '+250787495977', '$2y$10$s30hzEF9o1ko8.q7ValjQ.WYxS2FzXLVjHUTiYiqXqB7KtWMWLeNm', '1999-03-06', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC643467', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 11:06:29', '2026-02-05 11:06:29'),
(134, '42ccc215-0283-11f1-a30f-fa163e8467db', 'MUKAGAHIZI', 'Marie Rose', 'mukagahizimarierose@gmail.com', '+250788533236', '$2y$10$aOAASF5lBx5BotoGi.xXHuVJbYXpygqR.0ou/Luj5mym1C.tP4wRW', '1995-12-02', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC863360', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-05 11:10:20', '2026-02-05 11:10:20'),
(137, '3c43f862-02c0-11f1-a30f-fa163e8467db', 'NIYIBIGENA', 'Elysee ', 'niyibigenaelysee@gamail.com', NULL, '$2y$10$BBOK.C.7EunZOaWgiJAfVOITTvWhyJdGy7xSawD0z9m7IloW2Nn..', '2026-02-05', NULL, 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-05 18:26:49', '2026-02-05 18:26:49'),
(138, '230faa41-032d-11f1-a30f-fa163e8467db', 'Igisubizo', 'Shalom', 'igisubizoshalom334@gmail.com', '0794979754', '$2y$10$5yp68//XrmIyn89Pio6Ra.AYeAcbhdWzFUM3rJUnncu5v36CfHpPq', '2004-04-01', 'male', 'Rwanda', NULL, 'Kigali', NULL, '1357?', NULL, '2004-04-01', 'Electrical techenology', 'Igisubizo shalom ', '0794979754', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-06 07:26:22', '2026-02-06 07:34:13'),
(139, '8ce8e70f-032e-11f1-a30f-fa163e8467db', 'jean marie vianney', 'ndenzako', 'shayrayan2017@gmail.com', '+25768123980', '$2y$10$O/Z2OdJf6rLrJwcwYq0k4Og6Icct1.duaufuPq0yFe8hjpIQYgMaS', '1980-09-10', 'male', 'Burundi', 'Burundian', 'avenue 18, Kinanira', 'Bujumbura', NULL, 'OP0234186', '2029-05-07', 'IT', 'jean marie vianney ndenzako', '+25768123980', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-06 07:36:29', '2026-02-06 07:39:26'),
(140, '51b70787-0333-11f1-a30f-fa163e8467db', 'Ndayishimiye shema ', 'Ange christian', 'shemaangechristian@gmail.com', NULL, '$2y$10$GRi0OjTyG0Sm5SDG.WdZgu6sJ7Z3JZbCgYvJrlrxOXAoK39hjr8u.', '2002-10-07', 'male', 'Rwanda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jeanette umwali', '+250783053061', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-06 08:10:37', '2026-02-06 08:12:33'),
(141, 'e2b4ad31-0333-11f1-a30f-fa163e8467db', 'KWIZERA', 'Evase', 'kwizeraevas@gmail.com', '+25078885403', '$2y$10$sTmvQqo785Icj0y0CAD5/.ChldVVk1EsGmjqwIXQvz9mwY92CtIUK', '2000-12-12', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC774081', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:14:40', '2026-02-06 08:14:40'),
(142, '3303cc10-0334-11f1-a30f-fa163e8467db', 'HABIMANA', 'Bosco', 'Habimanabosco@gmail.com', '+250788640097', '$2y$10$798kwLS4DK5bjO83rsvwPez74CrcCuJ8HmrHkMfoC/wnOoiv3.Rza', '1999-03-05', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC751296', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:16:55', '2026-02-06 08:16:55'),
(143, 'd06f41b7-0334-11f1-a30f-fa163e8467db', 'NTAGAWA', 'Mwungura', 'terrickfr22fr22@gmail.com', '+250783216598', '$2y$10$NSC8P93thdneCJt.OPlAMevnox1EQGcnrx2NAsbe8CUgqVYMmhRDa', '1996-03-06', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC612298', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:21:19', '2026-02-06 08:21:19'),
(144, '2eead37d-0335-11f1-a30f-fa163e8467db', 'DUSENGIMANA', 'Chadrack', 'dusengimanachadrack@gmail.com', '+25078724297', '$2y$10$8d8BWnq8UnVysFhT2KlTM.2HuGdMAK/rDv/kUM7AyZzUUvzd8iu3O', '1997-03-06', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '11997880008752008', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:23:58', '2026-02-06 08:23:58'),
(145, '3df80349-0336-11f1-a30f-fa163e8467db', 'NIKOBIBAYE', 'Beatha', 'nikobibayebeatha@gmail.com', '+250782653276', '$2y$10$28IXyGPbzdPmfBpSguWqTuwPJ2aciFgjoKBKfrqAdn2RMxEqL6Wma', '1998-12-12', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC655634', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:31:32', '2026-02-06 08:31:32'),
(146, 'd3e0c530-0336-11f1-a30f-fa163e8467db', 'DUKOMEZIMANA', 'Eric', 'dukomezimanaeric@gmail.com', '+250787788964', '$2y$10$.4WsaMxwHrtvADuCVyGolOHkXYea5Q/IwigNFgaHGK266YeRVNjSS', '1995-03-06', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1199580092479015', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:35:44', '2026-02-06 08:35:44'),
(147, '4eaf6e89-0337-11f1-a30f-fa163e8467db', 'BYIRINGIRO', 'Bayingana', 'byiringirobayingana@gmail.com', '+250784409112', '$2y$10$jHO/xjEgh2EIIOth2d0lzOsJoDTTvONQBW4Hh5C4/AA.UlNRamBVO', '1995-03-06', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1199580006573023', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:39:10', '2026-02-06 08:39:10'),
(148, 'c0c2b991-0337-11f1-a30f-fa163e8467db', 'NDAYISHIMIYE', 'Gad', 'ndayishimiyegad@gmail.com', '+250786146478', '$2y$10$I4NZr5qEO4Qko1xU7uUGLuDAQyGteSGwQ18w99.FF7zFPt8aUU6w.', '1995-02-04', NULL, 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1200080074173010', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:42:21', '2026-02-06 08:42:21'),
(149, '4561a372-0338-11f1-a30f-fa163e8467db', 'UMUNYANA MUGABEKAZI', 'Aurole,Stella', 'umunyanamaurolestella@gmail.com', '+250788474175', '$2y$10$TfOR4N.E02jGIXzhAxSMU..ApnJi8SpRrh89biWvZnS0OqGYyeWxu', '1997-12-12', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC670442', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:46:04', '2026-02-06 08:46:04'),
(150, 'b00fc11e-0338-11f1-a30f-fa163e8467db', 'BYUSA MUKARAGE', 'Martin de poles', 'byusamartin@gmail.com', '+250780004298', '$2y$10$cLE4bun4WFYlD5chc.U7/.4RNeeqCiV6058zKT/OVr8pYSBOI3Bpi', '1997-12-12', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC659418', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:49:03', '2026-02-06 08:49:03'),
(151, 'bb34d980-0339-11f1-a30f-fa163e8467db', 'TABARO', 'Eric', 'tabaroeric@gmail.com', '+250788790006', '$2y$10$//ELyDET7jjBp6LCvGQWVuxaSAVmABPRtKOTY3BFaf8pQiWCqylZO', '1977-12-12', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1197780013345246', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:56:31', '2026-02-06 08:56:31');
INSERT INTO `users` (`id`, `user_uuid`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `date_of_birth`, `gender`, `country`, `nationality`, `address`, `city`, `postal_code`, `passport_number`, `passport_expiry`, `occupation`, `emergency_contact_name`, `emergency_contact_phone`, `profile_picture`, `is_active`, `email_verified`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `last_login`, `created_at`, `updated_at`) VALUES
(152, '1a2190d4-033a-11f1-a30f-fa163e8467db', 'NKURUNZIZA', 'Steven', 'nkurunzizasteven@gmail.com', '+250788209655', '$2y$10$yBBpfbxiwtmO1ispD1NKSe5afAS0KS8wA32Do7eoXgI3VZxKcoFXO', '2004-04-08', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1200480096838058', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 08:59:10', '2026-02-06 08:59:10'),
(153, 'ed6be6c7-033a-11f1-a30f-fa163e8467db', 'INGABIRE', 'Valentin', 'ingabirevalentin@gmail.com', '+250788990513', '$2y$10$pbLkMWBFtYY529pNxr5I..rr4B/zwTTR0dfZOWKT6vz4IxCzlRFKy', '1996-03-06', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC713279', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 09:05:05', '2026-02-06 09:05:05'),
(154, '9ff248ae-034b-11f1-a30f-fa163e8467db', 'NTEZIRYAYO', 'Hardi', 'nteziryayohardi@gmail.com', '+250788269809', '$2y$10$DwEqn.wsHfbBfnXSc65RVOTpsuXZA/eNPb1qGX3W4YNCp0V44yCV2', '1997-02-05', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC644931', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:04:36', '2026-02-06 11:04:36'),
(155, '7a3b563d-034c-11f1-a30f-fa163e8467db', 'MURANDIKIRE', 'Taussaint', 'murandikiretaussaint@gmail.com', '+250788269809', '$2y$10$pvf84xKzl13OqdnLYREE0O25OQFWu9KShonhyJI1SVf6XbP5/4Ib6', '1998-05-10', 'male', 'Australia', 'Rwanda', NULL, NULL, NULL, 'PC812607', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:10:42', '2026-02-06 11:10:42'),
(156, 'e5ada089-034c-11f1-a30f-fa163e8467db', 'MUNYERENKANA', 'Glorieuse', 'munyerenkanaglorieuse@gmail.com', '+250788269809', '$2y$10$W/V9bpxz/q6ePBndJvFVP.94evtSQ2Qxw/jkg.gCu4MI.kvPB9HqK', '2000-12-12', NULL, 'Luxembourg', 'Rwanda', NULL, NULL, NULL, 'PC5609958', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:13:43', '2026-02-06 11:13:43'),
(157, 'd6cf2f70-034d-11f1-a30f-fa163e8467db', 'DUSENGE', 'Avelin', 'dusengeavelin@gmail.com', '+250788269809', '$2y$10$89VTTXrKJM1oMjPAYlJequArXiR9fSa6nfpwbmVB6aYMh1TfLcvkG', '1996-03-06', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC816070', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:20:27', '2026-02-06 11:20:27'),
(158, '26c31c41-034e-11f1-a30f-fa163e8467db', 'BENIMANA', 'Rosette', 'benimanarosette@gmail.com', '+250788269809', '$2y$10$WjBacHMuU.FqKMCxLkWMuOuYdFPDJ1/adKZkgnP.mq0ddw/M2rxcu', '1999-12-12', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC891747', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:22:41', '2026-02-06 11:22:41'),
(159, '7f98d0c3-034f-11f1-a30f-fa163e8467db', 'HARAGIRIMANA', 'Didier', 'haragirimanadidier@gmail.com', '+250788269809', '$2y$10$fntiiH.K0uVR1VAbzQjlpuFJBs6zUWvT7xbD9vc3Dz5TtAqF1ujfC', '1999-12-12', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC721830', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:32:20', '2026-02-06 11:32:20'),
(160, '1085aeab-0350-11f1-a30f-fa163e8467db', 'RUKUNDO', 'Yussuf Raj', 'rukundoyussufraj@gmail.com', '+250788269809', '$2y$10$go0.cy.JB3OF7mrVcoK7sOHdm3fjkp98oruxYQkwtjtsE.SC69TIm', '1990-03-09', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, '1199080156115343', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:36:23', '2026-02-06 11:36:23'),
(161, '629282ab-0350-11f1-a30f-fa163e8467db', 'HAKIZIMANA', 'Jean Paul', 'hakizimanajeanpaul@gmail.com', '+250788269809', '$2y$10$xMyWRSNRcw4WqNbAG89wkeVt0sy6PgOUp4FdPitdxF3cmySDXpdp2', '1998-11-01', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC752265', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:38:41', '2026-02-06 11:38:41'),
(162, 'd7c00bd3-0350-11f1-a30f-fa163e8467db', 'KAMPIRE', 'Jeanne D\'Arc', 'kampirejeane@gmail.com', '+250788269809', '$2y$10$7gRaaVfRnTM0/LvMOuig9OtkcTvF55wAbjYzggIgIyIphzP0p3c6G', '1996-03-06', 'female', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC640828', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:41:57', '2026-02-06 11:41:57'),
(163, '2da818af-0351-11f1-a30f-fa163e8467db', 'NKURUNZIZA', 'Eric', 'nkurunzizaeric@gmail.com', '+250788269809', '$2y$10$a6IRcU0KGMnuLNFbQ88kwOPUchw1XLGTsF82RT2qN5fG04KCT5xRG', '1996-02-08', 'male', 'New Zealand', 'Rwanda', NULL, NULL, NULL, 'PC663110', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-06 11:44:21', '2026-02-06 11:44:21'),
(164, '9ffb3664-0374-11f1-a30f-fa163e8467db', 'Irumva ', 'Mika samuel', 'mikesamuelb@gmail.com', NULL, '$2y$10$RWG4orLQ3jMn/N0VG97WJOA9eZ3nQNZbnjCYhv96CYhuYTTH1Nqq2', '1999-02-25', 'male', 'Rwanda', 'Rwanda', 'kabuga,Rusororo ', 'Kigali', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-06 15:58:06', '2026-02-06 16:04:06'),
(165, 'f507c204-039c-11f1-a30f-fa163e8467db', 'Dieudonne', 'Kwihangana', 'kwihangana92@gmail.com', '0788857044', '$2y$10$vksCgHJXSxYMDT8c4CAlmuT90Vk.ZqHXP.atcy/ynz2Ak3zz3mOYe', '1992-10-30', 'male', 'Rwanda', NULL, 'KK796 ST', NULL, NULL, 'PC714346', '2032-07-19', 'Interior Designer and Landscape Designer', 'Bakareke Mwasi Chantal', '0790696485', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-06 20:46:48', '2026-02-06 20:52:08'),
(166, 'd8b4ed52-0414-11f1-93cf-fa163e8467db', 'NTEGEYE', 'David Patiente', 'davntegeye@gmail.com', '+250784535049', '$2y$10$WEqE5n4KsoaZhblMBhrX.OCwX2lRHOBM/xRZB7ZxJoubam19IF9Vm', '1997-09-21', 'male', 'United Arab Emirate', 'Rwanda', 'Kigali/Rwanda', NULL, NULL, 'PC777343', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 11:05:00', '2026-02-07 11:42:57'),
(167, '20e4197b-0418-11f1-93cf-fa163e8467db', 'FAZIRI', 'Hamida', 'fazirihamida@gmail.com', '+250786554700', '$2y$10$iAITwH5yPaYn5WoLtAYTc.vAuafg2yQek4i6zKbFKZIIWcEi9Bumi', '1983-03-04', 'female', 'United Arab Emirates', 'Rwanda', 'RWAMAGANA', NULL, NULL, 'PC786500', NULL, NULL, NULL, NULL, '69873b0178a58.jpeg', 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 11:28:30', '2026-02-07 13:15:45'),
(168, '37bab7ce-0419-11f1-93cf-fa163e8467db', 'NIRAGIRE', 'HASSAN', 'nirere@gmail.com', '+250782035963', '$2y$10$TD9bHfsFGruSRo2F20l3YuN6sBqDOTESm64Gl8v6BYEKjneJY0lZq', NULL, 'male', 'United Arab Emirates', 'Rwanda', 'HUYE', NULL, NULL, 'PC895039', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 11:36:18', '2026-02-07 11:36:18'),
(169, 'de927d75-041a-11f1-93cf-fa163e8467db', 'MUHIRE', 'XXX', 'muhire478@gmail.com', '+250785767143', '$2y$10$1pdQFf/v7HeUEf95W63UdO/zgLJ1WQovEr/7OHAWAug9rcb3okxmi', '1994-01-01', 'male', 'United Arab Emirates', 'Rwanda', 'RUBAVU', NULL, NULL, 'PC755367', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 11:48:07', '2026-02-07 11:48:07'),
(170, 'a7b0dd1c-041b-11f1-93cf-fa163e8467db', 'Epaphrodite', 'UWAMBAJIMANA', 'uepaphrodite407@gmail.com', '=250788455696', '$2y$10$qLgwNxpweIVAVhkTBefH9e3Cf8gAKbL1ez2.vfDCA/Jrj5BfDNG4a', '1980-01-01', 'male', 'New Zealand', 'Rwanda', 'huye', NULL, NULL, 'pc753047', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 11:53:44', '2026-02-07 11:53:44'),
(171, '13f8934b-041c-11f1-93cf-fa163e8467db', 'sarah delphine', 'MUKABADEGE', 'mukabadege15@gmail.com', '+250788475847', '$2y$10$1KEJczi/dn2MVnFLpMk2oO/n2S3ytBgg39HN/x4IFhpPSjKPFWya6', '1980-01-01', 'female', 'new zealand', 'Rwanda', 'HUYE', NULL, NULL, 'PC752777', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 11:56:46', '2026-02-07 11:57:06'),
(172, 'bc6e4bad-041c-11f1-93cf-fa163e8467db', 'NSENGIMANA', 'Manasseh', 'inksociol@gmail.com', '+250787157159', '$2y$10$JTPKI5Ld56OnCh5HRMfb6umWU4.H4D0qbPuxlQEjvkh/RMnTp58uC', '1994-04-01', 'male', 'United Arab Emirates', 'Rwanda', 'RWAMAGANA', NULL, NULL, 'pc887021', NULL, NULL, NULL, NULL, '69873acf0d2b0.jpeg', 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 12:01:29', '2026-02-07 13:14:55'),
(173, 'c21b7043-0426-11f1-93cf-fa163e8467db', 'NSENGIMANA CYUSA', 'JULESBONHEUR', 'julesbonheur@gmail.com', '+250798445659', '$2y$10$rvmZPHn1v5Fley38MFKs3OlX/HXdSOugOT10I4OTiqrBM5ZBRYdtq', '2005-06-13', 'male', 'United Arab Emirates', 'Rwanda', 'NYARUGENGE', NULL, NULL, 'PC822289', NULL, NULL, NULL, NULL, 'profile_1770469993_2982.jpeg', 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 13:13:13', '2026-02-07 13:13:13'),
(174, 'd62ab9f6-042c-11f1-93cf-fa163e8467db', 'Nikwishaka Misago', 'Fiat', 'nikwishakafiati@gmail.com', '+250798944404', '$2y$10$0WOydNN1y/TmejCNDAaUUek0WPRhjzw7cyx1HHABhqcOgFJ3Rvpki', '2002-05-14', 'male', 'Rwanda', NULL, 'Kk509', NULL, 'None', 'None', NULL, NULL, 'Fiat', '+250798944404', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-07 13:56:44', '2026-02-07 13:58:16'),
(175, '064f147b-0430-11f1-93cf-fa163e8467db', 'BILL ISMAEL', 'UHIRIWE', 'BIILY@gmail.com', '+250780910564', '$2y$10$RbWw//IXs2RjcDzmYKi.S.Maet5oHE5uxD4QPo/j1OEO9eoSQqF9y', '2000-01-01', 'male', 'United Arab Emirates', 'Rwanda', 'KIGALI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 14:19:33', '2026-02-07 14:19:33'),
(176, 'ef51988b-0438-11f1-93cf-fa163e8467db', 'UWIRAGIYE', 'BIENVENUE', 'bienvenueuwiragiye2@gmail.com', '+250781946570', '$2y$10$j1634hTm3m5CstucRPFNp.eE25R2O9KndVlbVKe9ezWMrUXz6ztkK', '2001-11-20', 'male', 'United Arab Emirates', 'Rwanda', 'NYAMIRAMBO', NULL, NULL, 'PC819513', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-07 15:23:20', '2026-02-07 15:23:20'),
(177, '8f41e438-0461-11f1-93cf-fa163e8467db', 'Musiime ', 'Djumah ', 'musiimedjumah@gmail.com', NULL, '$2y$10$IVCEC9U6fr1oczg4SiG1bel.3Vikf2.1fFQ2qDimbjE4W2MrQF8vi', '2006-07-21', NULL, 'Rwanda', 'Rwandan ', 'Nyagatare ', 'Kigali ', '00000', NULL, NULL, 'Mechanical technician ', 'Musiime djumah ', '+250792957910', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-07 20:14:08', '2026-02-07 20:18:57'),
(178, '5fb09c33-04c3-11f1-93cf-fa163e8467db', 'AtoZ', 'Solutions', 'atozgloballinksolutions@gmail.com', NULL, '$2y$10$Y5xlI3SAWGyQ0INI3rflL.bcGSSvyOnmEND/YZcN0bGpJeafjVq1m', '2026-02-10', NULL, 'Belize', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-08 07:54:19', '2026-02-08 07:54:19'),
(179, 'a9b89282-0593-11f1-93cf-fa163e8467db', 'Abbudli', 'Mushinzimana', 'mushinzimana200@gmail.com', '0789285229', '$2y$10$2WLOShMaqrCNoMnjddOioeq9ixtlYXfqy4fo5DBnlPrZSXjI4qymS', '2000-01-08', NULL, 'Rwanda', NULL, 'Kigali Rwanda ', 'Kigali', '1234', NULL, '2027-08-18', 'Saler', 'Abbudli Fatah Mushinzimana', '+250789285229', NULL, 1, 0, NULL, NULL, NULL, NULL, '2026-02-09 08:45:19', '2026-02-09 08:47:02'),
(181, '1f1ae667-05a4-11f1-93cf-fa163e8467db', 'CYUZUZO', 'Regis', 'cyuzuzoregis@gmail.com', '+250784553136', '$2y$10$hk4QbYnYIsbBmkgfb74WD.20sa94TaF/Tfj2/8mFWLI1ndfKGXF6e', '2004-01-01', 'male', 'United Arab Emirates', 'Rwanda', NULL, NULL, NULL, 'PC809239', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-09 10:43:07', '2026-02-09 10:43:07'),
(184, 'c88fe8f9-05c7-11f1-93cf-fa163e8467db', 'DUSABE', 'Fabiola', 'dusabefabiola@gmail.com', '+250 794 723 501', '$2y$10$Rip2NhD7qCo79hYN3zelFOxHdykb2pJ802t4gg4ScP9xR.gUTu.t.', '1994-09-25', 'female', 'United Arab Emirates', 'Rwanda', NULL, NULL, NULL, 'PC 567435', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-09 14:58:24', '2026-02-09 14:58:24'),
(185, '387e9804-0660-11f1-93cf-fa163e8467db', 'olivier', 'MUZIBACYUHO', 'olivier@gmail.com', '+250789486444', '$2y$10$gG82YZliDDeH4YRrLQ2HVeiiI.Mk3K1PEGfAFOd3uswGeWXUd0fMW', '2001-12-12', 'male', 'United Arab Emirates', 'Rwanda', 'kigali', NULL, NULL, 'pc817719', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2026-02-10 09:09:35', '2026-02-10 09:09:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_slug` (`permission_slug`),
  ADD KEY `idx_module` (`module`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_slug` (`role_slug`);

--
-- Indexes for table `admin_role_permissions`
--
ALTER TABLE `admin_role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission` (`role_id`,`permission_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_uuid` (`application_uuid`),
  ADD UNIQUE KEY `application_number` (`application_number`),
  ADD KEY `idx_uuid` (`application_uuid`),
  ADD KEY `idx_number` (`application_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_status` (`status_id`),
  ADD KEY `idx_destination` (`destination_country_id`),
  ADD KEY `idx_submitted` (`submitted_at`);

--
-- Indexes for table `application_messages`
--
ALTER TABLE `application_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_sender` (`sender_type`,`sender_id`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `application_statuses`
--
ALTER TABLE `application_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_code` (`status_code`),
  ADD KEY `idx_code` (`status_code`),
  ADD KEY `idx_final` (`is_final_status`);

--
-- Indexes for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_status_change` (`from_status_id`,`to_status_id`),
  ADD KEY `application_status_history_ibfk_3` (`to_status_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_type`,`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table` (`table_name`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_admin` (`admin_id`),
  ADD KEY `idx_sender` (`sender_type`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_replied` (`replied`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `iso_code_2` (`iso_code_2`),
  ADD UNIQUE KEY `iso_code_3` (`iso_code_3`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_iso2` (`iso_code_2`),
  ADD KEY `idx_iso3` (`iso_code_3`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `destination_countries`
--
ALTER TABLE `destination_countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_country` (`country_id`),
  ADD KEY `idx_featured` (`is_featured`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_requirement` (`requirement_id`),
  ADD KEY `idx_type` (`document_type`),
  ADD KEY `idx_verified` (`is_verified`),
  ADD KEY `documents_ibfk_3` (`uploaded_by`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_uuid` (`payment_uuid`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_uuid` (`payment_uuid`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`payment_status`),
  ADD KEY `idx_reference` (`transaction_reference`);

--
-- Indexes for table `payment_installments`
--
ALTER TABLE `payment_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `idx_application_installment` (`application_id`,`installment_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refund_requests_application` (`application_id`),
  ADD KEY `idx_refund_requests_user` (`user_id`),
  ADD KEY `idx_refund_requests_status` (`status`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_uuid` (`service_uuid`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_uuid` (`service_uuid`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_price` (`base_price`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- Indexes for table `service_requirements`
--
ALTER TABLE `service_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_type` (`requirement_type`),
  ADD KEY `idx_mandatory` (`is_mandatory`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_uuid` (`user_uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_uuid` (`user_uuid`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_country` (`country`),
  ADD KEY `idx_nationality` (`nationality`),
  ADD KEY `idx_passport` (`passport_number`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_email_verified` (`email_verified`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admin_role_permissions`
--
ALTER TABLE `admin_role_permissions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_messages`
--
ALTER TABLE `application_messages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_statuses`
--
ALTER TABLE `application_statuses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `destination_countries`
--
ALTER TABLE `destination_countries`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `payment_installments`
--
ALTER TABLE `payment_installments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `refund_requests`
--
ALTER TABLE `refund_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `service_requirements`
--
ALTER TABLE `service_requirements`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_role_fk` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_role_permissions`
--
ALTER TABLE `admin_role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `admin_permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `applications_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `application_statuses` (`id`),
  ADD CONSTRAINT `applications_ibfk_4` FOREIGN KEY (`destination_country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_messages`
--
ALTER TABLE `application_messages`
  ADD CONSTRAINT `application_messages_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD CONSTRAINT `application_status_history_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_status_history_ibfk_2` FOREIGN KEY (`from_status_id`) REFERENCES `application_statuses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `application_status_history_ibfk_3` FOREIGN KEY (`to_status_id`) REFERENCES `application_statuses` (`id`);

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `destination_countries`
--
ALTER TABLE `destination_countries`
  ADD CONSTRAINT `destination_countries_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`requirement_id`) REFERENCES `service_requirements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  ADD CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `payment_installments`
--
ALTER TABLE `payment_installments`
  ADD CONSTRAINT `payment_installments_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_installments_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`);

--
-- Constraints for table `service_requirements`
--
ALTER TABLE `service_requirements`
  ADD CONSTRAINT `service_requirements_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
