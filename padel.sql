-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 26, 2026 at 07:29 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `padel`
--

-- --------------------------------------------------------

--
-- Table structure for table `academy_sessions`
--

CREATE TABLE `academy_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `club_id` bigint UNSIGNED NOT NULL,
  `court_id` bigint UNSIGNED NOT NULL,
  `coach_user_id` bigint UNSIGNED DEFAULT NULL,
  `created_by_user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'padel',
  `session_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'group_training',
  `skill_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `max_players` smallint UNSIGNED NOT NULL DEFAULT '4',
  `price_per_player` decimal(8,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `session_plan` text COLLATE utf8mb4_unicode_ci,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_urls` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academy_sessions`
--

INSERT INTO `academy_sessions` (`id`, `club_id`, `court_id`, `coach_user_id`, `created_by_user_id`, `title`, `sport_type`, `session_type`, `skill_level`, `start_time`, `end_time`, `max_players`, `price_per_player`, `status`, `notes`, `session_plan`, `video_url`, `video_urls`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 7, 1, 'Weekend Boot Camp', 'squash', 'clinic', 'all_levels', '2026-05-14 21:02:46', '2026-05-14 23:02:46', 12, 30.00, 'scheduled', 'Consectetur a voluptate nulla sequi perspiciatis recusandae.', NULL, NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(2, 1, 1, 7, 1, 'Advanced Techniques', 'squash', 'clinic', 'advanced', '2026-04-25 02:32:42', '2026-04-25 03:32:42', 6, 30.00, 'scheduled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 1, 1, 7, 1, 'Weekend Boot Camp', 'squash', 'clinic', NULL, '2026-05-30 00:33:15', '2026-05-30 02:03:15', 6, 40.00, 'completed', 'Optio quod et quod asperiores nihil est qui perspiciatis.', NULL, NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 1, 1, 7, 1, 'Youth Training Session', 'squash', 'private_lesson', NULL, '2026-05-12 15:01:39', '2026-05-12 16:01:39', 4, 20.00, 'in_progress', NULL, NULL, NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(5, 1, 1, 7, 1, 'Ladies Morning Session', 'squash', 'clinic', NULL, '2026-05-07 18:07:03', '2026-05-07 19:07:03', 8, 40.00, 'cancelled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(6, 1, 1, 7, 1, 'Weekend Boot Camp', 'squash', 'clinic', 'intermediate', '2026-05-19 12:27:50', '2026-05-19 13:57:50', 6, 75.00, 'scheduled', 'Tenetur rem at minima eaque natus vel.', NULL, NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(7, 1, 1, 7, 1, 'Weekend Boot Camp', 'squash', 'group_training', 'advanced', '2026-04-30 07:38:30', '2026-04-30 09:08:30', 8, 40.00, 'completed', NULL, NULL, NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(8, 1, 1, 7, 1, 'Weekend Boot Camp', 'squash', 'group_training', NULL, '2026-04-28 06:57:37', '2026-04-28 08:27:37', 6, 75.00, 'completed', 'Quas earum tenetur ducimus animi illum cum error.', NULL, NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(9, 2, 5, 11, 1, 'Mixed Doubles Practice', 'squash', 'clinic', NULL, '2026-05-04 09:05:54', '2026-05-04 11:05:54', 8, 30.00, 'cancelled', 'Qui animi vero repellendus.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(10, 2, 5, 11, 1, 'Advanced Techniques', 'squash', 'group_training', 'all_levels', '2026-04-11 11:08:33', '2026-04-11 12:08:33', 12, 50.00, 'scheduled', 'Qui magnam dicta voluptate perspiciatis.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(11, 2, 5, 11, 1, 'Pro Players Workshop', 'squash', 'clinic', NULL, '2026-05-31 17:07:07', '2026-05-31 18:07:07', 6, 50.00, 'completed', 'Natus cumque ut quisquam nobis suscipit soluta.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(12, 2, 5, 11, 1, 'Mixed Doubles Practice', 'squash', 'clinic', 'advanced', '2026-05-17 23:54:29', '2026-05-18 01:54:29', 10, 75.00, 'cancelled', 'Cupiditate ut voluptas qui ut voluptatum voluptates et.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(13, 2, 5, 11, 1, 'Fitness & Padel', 'squash', 'private_lesson', NULL, '2026-05-04 01:48:20', '2026-05-04 03:18:20', 10, 40.00, 'completed', 'Veniam saepe totam porro.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(14, 2, 5, 11, 1, 'Mixed Doubles Practice', 'squash', 'group_training', NULL, '2026-05-07 13:17:39', '2026-05-07 15:17:39', 10, 50.00, 'scheduled', 'Labore harum officia tenetur ut laboriosam et in.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(15, 3, 8, 11, 1, 'Pro Players Workshop', 'padel', 'tournament', NULL, '2026-05-18 04:19:39', '2026-05-18 05:19:39', 4, 75.00, 'scheduled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(16, 3, 8, 11, 1, 'Youth Training Session', 'padel', 'private_lesson', NULL, '2026-05-01 10:59:20', '2026-05-01 11:59:20', 12, 30.00, 'completed', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(17, 3, 8, 11, 1, 'Advanced Techniques', 'padel', 'group_training', NULL, '2026-05-01 10:08:01', '2026-05-01 11:08:01', 6, 20.00, 'scheduled', 'Sit optio et blanditiis adipisci dolorem vel qui.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(18, 3, 8, 11, 1, 'Advanced Techniques', 'padel', 'private_lesson', 'advanced', '2026-04-12 03:56:39', '2026-04-12 04:56:39', 12, 20.00, 'scheduled', 'Ut quibusdam sit enim voluptates delectus dolores accusantium.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(19, 3, 8, 11, 1, 'Ladies Morning Session', 'padel', 'group_training', NULL, '2026-05-13 21:15:35', '2026-05-13 23:15:35', 10, 20.00, 'scheduled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(20, 3, 8, 11, 1, 'Advanced Techniques', 'padel', 'tournament', NULL, '2026-04-20 13:23:26', '2026-04-20 14:53:26', 6, 50.00, 'scheduled', 'Officia et quaerat itaque.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(21, 3, 8, 11, 1, 'Beginner Padel Clinic', 'padel', 'tournament', NULL, '2026-05-23 01:44:11', '2026-05-23 03:14:11', 4, 40.00, 'scheduled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(22, 4, 12, 10, 1, 'Pro Players Workshop', 'tennis', 'private_lesson', NULL, '2026-05-01 14:20:44', '2026-05-01 16:20:44', 4, 30.00, 'scheduled', 'Voluptate quas pariatur doloribus tenetur.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(23, 4, 12, 10, 1, 'Advanced Techniques', 'tennis', 'clinic', 'all_levels', '2026-05-16 18:08:54', '2026-05-16 20:08:54', 10, 20.00, 'scheduled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(24, 4, 12, 10, 1, 'Advanced Techniques', 'tennis', 'tournament', 'advanced', '2026-05-10 18:02:14', '2026-05-10 20:02:14', 12, 30.00, 'cancelled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(25, 4, 12, 10, 1, 'Fitness & Padel', 'tennis', 'group_training', 'all_levels', '2026-05-13 05:43:36', '2026-05-13 07:43:36', 6, 50.00, 'cancelled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(26, 4, 12, 10, 1, 'Pro Players Workshop', 'tennis', 'tournament', NULL, '2026-05-28 18:01:46', '2026-05-28 19:01:46', 8, 40.00, 'scheduled', 'Ut incidunt sit earum magni blanditiis laboriosam.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(27, 4, 12, 10, 1, 'Fitness & Padel', 'tennis', 'tournament', NULL, '2026-04-21 14:03:26', '2026-04-21 15:03:26', 4, 50.00, 'scheduled', 'Iste vitae sequi veniam eius eos ducimus voluptates.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(28, 5, 15, 2, 1, 'Youth Training Session', 'padel', 'tournament', NULL, '2026-05-22 21:41:45', '2026-05-22 22:41:45', 10, 50.00, 'scheduled', 'Nam et modi assumenda vel culpa modi.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(29, 5, 15, 2, 1, 'Pro Players Workshop', 'padel', 'tournament', NULL, '2026-05-29 01:43:11', '2026-05-29 03:13:11', 4, 20.00, 'cancelled', 'Dolor dolorem recusandae autem quia.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(30, 5, 15, 2, 1, 'Pro Players Workshop', 'padel', 'group_training', NULL, '2026-05-26 03:46:29', '2026-05-26 05:16:29', 4, 50.00, 'in_progress', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(31, 5, 15, 2, 1, 'Beginner Padel Clinic', 'padel', 'private_lesson', NULL, '2026-04-18 10:24:14', '2026-04-18 11:24:14', 12, 30.00, 'scheduled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(32, 5, 15, 2, 1, 'Advanced Techniques', 'padel', 'private_lesson', NULL, '2026-05-31 15:29:35', '2026-05-31 16:59:35', 4, 30.00, 'in_progress', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(33, 5, 15, 2, 1, 'Weekend Boot Camp', 'padel', 'group_training', NULL, '2026-04-25 11:13:54', '2026-04-25 13:13:54', 10, 40.00, 'scheduled', 'Facere sit earum nulla voluptate est ipsum similique.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(34, 5, 15, 2, 1, 'Ladies Morning Session', 'padel', 'clinic', NULL, '2026-05-21 23:40:25', '2026-05-22 00:40:25', 12, 75.00, 'cancelled', 'Iste corporis sequi sed nihil.', NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(35, 5, 15, 2, 1, 'Ladies Morning Session', 'padel', 'clinic', NULL, '2026-04-22 10:26:41', '2026-04-22 12:26:41', 4, 30.00, 'scheduled', NULL, NULL, NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `academy_session_user`
--

CREATE TABLE `academy_session_user` (
  `id` bigint UNSIGNED NOT NULL,
  `academy_session_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint UNSIGNED NOT NULL,
  `court_id` bigint UNSIGNED NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'padel',
  `owner_user_id` bigint UNSIGNED NOT NULL,
  `coach_user_id` bigint UNSIGNED DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `total_price` decimal(8,2) NOT NULL,
  `coach_fee` decimal(8,2) NOT NULL DEFAULT '0.00',
  `match_type` enum('private','open_match') COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `max_players` smallint UNSIGNED NOT NULL DEFAULT '4',
  `skill_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `court_id`, `sport_type`, `owner_user_id`, `coach_user_id`, `start_time`, `end_time`, `total_price`, `coach_fee`, `match_type`, `session_type`, `max_players`, `skill_level`, `status`, `notes`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'squash', 19, NULL, '2026-05-19 16:24:31', '2026-05-19 17:24:31', 75.00, 0.00, 'private', 'coached', 4, 'advanced', 'confirmed', 'Odit quo et consequatur optio neque labore harum.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(2, 1, 'squash', 19, NULL, '2026-03-30 12:03:28', '2026-03-30 13:33:28', 225.00, 0.00, 'open_match', 'academy', 4, 'beginner', 'pending', 'Nemo qui esse eligendi sapiente est saepe quia labore.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 1, 'squash', 19, NULL, '2026-04-11 10:56:51', '2026-04-11 12:56:51', 200.00, 0.00, 'open_match', 'coached', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 1, 'squash', 19, NULL, '2026-04-19 07:23:40', '2026-04-19 08:53:40', 180.00, 0.00, 'private', 'academy', 2, NULL, 'cancelled', 'Sint consequatur quia vero voluptates sequi.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(5, 1, 'squash', 19, NULL, '2026-05-18 03:18:18', '2026-05-18 04:48:18', 225.00, 0.00, 'private', 'academy', 2, 'intermediate', 'confirmed', 'Molestias sunt consequatur et voluptatem ullam quidem.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(6, 1, 'squash', 19, NULL, '2026-04-16 18:57:50', '2026-04-16 20:57:50', 200.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(7, 1, 'squash', 19, NULL, '2026-04-04 21:43:25', '2026-04-04 23:13:25', 75.00, 0.00, 'private', 'academy', 4, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(8, 1, 'squash', 19, NULL, '2026-05-06 20:55:47', '2026-05-06 22:25:47', 112.50, 0.00, 'private', 'standard', 2, NULL, 'cancelled', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(9, 1, 'squash', 19, NULL, '2026-04-16 13:10:03', '2026-04-16 15:10:03', 240.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', 'Ut voluptas deserunt pariatur pariatur alias placeat.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(10, 1, 'squash', 19, NULL, '2026-03-29 15:18:45', '2026-03-29 17:18:45', 100.00, 0.00, 'open_match', 'standard', 4, NULL, 'cancelled', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(11, 1, 'squash', 19, NULL, '2026-04-05 23:40:31', '2026-04-06 01:10:31', 225.00, 0.00, 'private', 'coached', 2, NULL, 'pending', 'Est et corporis a possimus quia qui nihil.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(12, 1, 'squash', 19, NULL, '2026-04-19 14:40:38', '2026-04-19 16:10:38', 112.50, 0.00, 'private', 'academy', 2, 'advanced', 'confirmed', 'Et quam facere dolores nam aut magni.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(13, 1, 'squash', 19, NULL, '2026-05-05 15:08:48', '2026-05-05 17:08:48', 150.00, 0.00, 'open_match', 'academy', 4, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(14, 1, 'squash', 19, NULL, '2026-04-22 13:27:21', '2026-04-22 14:27:21', 75.00, 0.00, 'private', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(15, 2, 'squash', 24, NULL, '2026-05-14 01:03:00', '2026-05-14 02:03:00', 100.00, 0.00, 'open_match', 'coached', 2, 'intermediate', 'pending', 'Voluptatem aliquam non officia voluptates maiores facilis laborum dolor.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(16, 2, 'squash', 24, NULL, '2026-04-16 11:30:42', '2026-04-16 12:30:42', 50.00, 0.00, 'private', 'standard', 4, 'advanced', 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(17, 2, 'squash', 24, NULL, '2026-04-30 07:43:15', '2026-04-30 08:43:15', 120.00, 0.00, 'open_match', 'coached', 2, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(18, 2, 'squash', 24, NULL, '2026-05-02 03:58:09', '2026-05-02 05:58:09', 200.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(19, 2, 'squash', 24, NULL, '2026-05-12 22:13:03', '2026-05-12 23:13:03', 100.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(20, 2, 'squash', 24, NULL, '2026-05-12 11:00:26', '2026-05-12 12:30:26', 225.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(21, 2, 'squash', 24, NULL, '2026-04-19 12:04:35', '2026-04-19 13:04:35', 75.00, 0.00, 'private', 'coached', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(22, 2, 'squash', 24, NULL, '2026-04-26 23:32:01', '2026-04-27 00:32:01', 120.00, 0.00, 'open_match', 'standard', 4, 'beginner', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(23, 3, 'squash', 35, NULL, '2026-04-24 09:29:49', '2026-04-24 10:59:49', 112.50, 0.00, 'private', 'standard', 2, NULL, 'confirmed', 'Sunt hic voluptates et quod dicta rerum iste.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(24, 3, 'squash', 35, NULL, '2026-04-22 19:41:47', '2026-04-22 20:41:47', 120.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', 'Delectus placeat qui corrupti ut voluptas facilis magni.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(25, 3, 'squash', 35, NULL, '2026-05-10 12:50:13', '2026-05-10 13:50:13', 75.00, 0.00, 'open_match', 'standard', 2, 'beginner', 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(26, 3, 'squash', 35, NULL, '2026-04-27 21:14:56', '2026-04-27 22:44:56', 75.00, 0.00, 'open_match', 'academy', 4, 'beginner', 'cancelled', 'Ratione repudiandae saepe ea soluta est.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(27, 3, 'squash', 35, NULL, '2026-03-30 19:22:01', '2026-03-30 20:52:01', 112.50, 0.00, 'open_match', 'standard', 4, NULL, 'confirmed', 'Hic in sed et repellendus consequatur ipsam porro.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(28, 3, 'squash', 35, NULL, '2026-05-24 18:17:50', '2026-05-24 19:47:50', 180.00, 0.00, 'private', 'standard', 2, 'advanced', 'pending', 'Officiis voluptas reiciendis hic et officia esse quisquam.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(29, 3, 'squash', 35, NULL, '2026-05-18 22:10:33', '2026-05-19 00:10:33', 100.00, 0.00, 'open_match', 'academy', 2, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(30, 3, 'squash', 35, NULL, '2026-04-06 06:28:31', '2026-04-06 07:28:31', 50.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', 'Rem reiciendis quia at architecto ipsa ea.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(31, 3, 'squash', 35, NULL, '2026-05-03 21:52:22', '2026-05-03 23:52:22', 300.00, 0.00, 'open_match', 'standard', 2, NULL, 'pending', 'Consectetur minima quo quos et at ut.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(32, 3, 'squash', 35, NULL, '2026-05-17 20:18:09', '2026-05-17 21:18:09', 75.00, 0.00, 'private', 'coached', 4, 'advanced', 'confirmed', 'Magni sed quia ut quia.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(33, 3, 'squash', 35, NULL, '2026-04-09 18:36:25', '2026-04-09 19:36:25', 100.00, 0.00, 'private', 'academy', 2, 'advanced', 'pending', 'Autem dicta provident labore ut non.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(34, 3, 'squash', 35, NULL, '2026-04-13 03:56:27', '2026-04-13 04:56:27', 75.00, 0.00, 'private', 'academy', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(35, 3, 'squash', 35, NULL, '2026-05-06 08:02:45', '2026-05-06 10:02:45', 240.00, 0.00, 'private', 'academy', 4, 'intermediate', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(36, 3, 'squash', 35, NULL, '2026-05-21 04:01:26', '2026-05-21 05:01:26', 100.00, 0.00, 'private', 'standard', 2, NULL, 'cancelled', 'Sunt odio quia temporibus a labore id harum facilis.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(37, 3, 'squash', 35, NULL, '2026-03-31 06:38:41', '2026-03-31 07:38:41', 150.00, 0.00, 'open_match', 'coached', 4, NULL, 'confirmed', 'Tenetur cum ut sapiente accusamus.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(38, 4, 'squash', 61, NULL, '2026-05-14 16:59:18', '2026-05-14 18:59:18', 100.00, 0.00, 'private', 'standard', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(39, 4, 'squash', 61, NULL, '2026-04-17 19:00:16', '2026-04-17 20:00:16', 120.00, 0.00, 'open_match', 'academy', 4, 'intermediate', 'confirmed', 'Voluptas sint aut molestiae ut est cupiditate.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(40, 4, 'squash', 61, NULL, '2026-05-21 13:30:59', '2026-05-21 14:30:59', 50.00, 0.00, 'private', 'standard', 2, NULL, 'confirmed', 'Ab quos odit eius autem praesentium perspiciatis repellendus dolores.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(41, 4, 'squash', 61, NULL, '2026-05-16 07:28:54', '2026-05-16 09:28:54', 240.00, 0.00, 'private', 'academy', 4, NULL, 'confirmed', 'Praesentium voluptas adipisci magnam dolorem consectetur.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(42, 4, 'squash', 61, NULL, '2026-03-28 12:45:13', '2026-03-28 14:45:13', 150.00, 0.00, 'private', 'academy', 4, 'intermediate', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(43, 4, 'squash', 61, NULL, '2026-04-08 22:50:10', '2026-04-09 00:50:10', 150.00, 0.00, 'open_match', 'academy', 4, NULL, 'confirmed', 'Est quisquam et blanditiis aut provident est.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(44, 4, 'squash', 61, NULL, '2026-05-08 19:16:28', '2026-05-08 21:16:28', 300.00, 0.00, 'private', 'academy', 4, 'beginner', 'confirmed', 'Voluptas aperiam sed consequatur quis distinctio.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(45, 4, 'squash', 61, NULL, '2026-03-26 13:35:58', '2026-03-26 15:05:58', 150.00, 0.00, 'open_match', 'coached', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(46, 4, 'squash', 61, NULL, '2026-03-25 23:49:11', '2026-03-26 01:19:11', 75.00, 0.00, 'private', 'coached', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(47, 4, 'squash', 61, NULL, '2026-04-06 20:58:07', '2026-04-06 22:28:07', 225.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(48, 4, 'squash', 61, NULL, '2026-05-06 18:24:41', '2026-05-06 19:54:41', 180.00, 0.00, 'private', 'coached', 2, 'intermediate', 'confirmed', 'Cumque voluptatem magni iusto dignissimos minima dolores eum.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(49, 4, 'squash', 61, NULL, '2026-04-29 04:25:31', '2026-04-29 05:25:31', 100.00, 0.00, 'private', 'coached', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(50, 4, 'squash', 61, NULL, '2026-04-14 02:09:51', '2026-04-14 03:09:51', 100.00, 0.00, 'open_match', 'coached', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(51, 4, 'squash', 61, NULL, '2026-04-14 16:02:48', '2026-04-14 17:32:48', 75.00, 0.00, 'open_match', 'coached', 2, 'advanced', 'cancelled', 'Illo sit excepturi vel excepturi non.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(52, 4, 'squash', 61, NULL, '2026-04-02 18:56:38', '2026-04-02 20:26:38', 225.00, 0.00, 'open_match', 'coached', 4, NULL, 'confirmed', 'Et maiores et quo minima doloribus.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(53, 5, 'squash', 24, 3, '2026-04-05 06:05:28', '2026-04-05 08:05:28', 100.00, 0.00, 'private', 'coached', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(54, 5, 'squash', 24, 3, '2026-05-14 03:10:29', '2026-05-14 04:10:29', 150.00, 0.00, 'private', 'coached', 4, 'beginner', 'cancelled', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(55, 5, 'squash', 24, 3, '2026-05-24 09:30:02', '2026-05-24 11:30:02', 300.00, 0.00, 'private', 'academy', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(56, 5, 'squash', 24, 3, '2026-05-03 23:21:53', '2026-05-04 00:21:53', 50.00, 0.00, 'private', 'standard', 4, 'intermediate', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(57, 5, 'squash', 24, 3, '2026-05-08 23:06:29', '2026-05-09 00:36:29', 225.00, 0.00, 'open_match', 'academy', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(58, 5, 'squash', 24, 3, '2026-03-26 07:14:17', '2026-03-26 09:14:17', 240.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', 'Est maiores magnam commodi ut et ut illo quia.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(59, 5, 'squash', 24, 3, '2026-05-05 12:49:33', '2026-05-05 14:19:33', 150.00, 0.00, 'private', 'academy', 4, 'beginner', 'confirmed', 'Totam quod tempora sed et eos soluta.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(60, 5, 'squash', 24, 3, '2026-04-09 02:16:37', '2026-04-09 04:16:37', 300.00, 0.00, 'private', 'standard', 2, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(61, 5, 'squash', 24, 3, '2026-05-24 06:06:16', '2026-05-24 07:36:16', 75.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', 'Consequuntur rerum incidunt dolore.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(62, 5, 'squash', 24, 3, '2026-05-03 06:19:00', '2026-05-03 07:19:00', 150.00, 0.00, 'private', 'standard', 4, 'beginner', 'cancelled', 'Qui vero qui iste voluptas iure.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(63, 6, 'squash', 23, NULL, '2026-05-01 22:18:54', '2026-05-01 23:48:54', 112.50, 0.00, 'private', 'coached', 4, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(64, 6, 'squash', 23, NULL, '2026-03-30 05:37:53', '2026-03-30 07:37:53', 100.00, 0.00, 'private', 'academy', 2, 'intermediate', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(65, 6, 'squash', 23, NULL, '2026-05-03 02:09:29', '2026-05-03 04:09:29', 300.00, 0.00, 'open_match', 'academy', 2, 'advanced', 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(66, 6, 'squash', 23, NULL, '2026-04-01 14:45:49', '2026-04-01 15:45:49', 50.00, 0.00, 'private', 'coached', 4, NULL, 'cancelled', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(67, 6, 'squash', 23, NULL, '2026-05-11 01:57:10', '2026-05-11 03:27:10', 180.00, 0.00, 'open_match', 'coached', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(68, 6, 'squash', 23, NULL, '2026-05-13 05:03:29', '2026-05-13 06:33:29', 150.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(69, 6, 'squash', 23, NULL, '2026-04-30 10:39:11', '2026-04-30 11:39:11', 75.00, 0.00, 'open_match', 'coached', 4, NULL, 'cancelled', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(70, 6, 'squash', 23, NULL, '2026-05-08 17:44:06', '2026-05-08 18:44:06', 120.00, 0.00, 'private', 'coached', 2, 'advanced', 'confirmed', 'Aut voluptatem blanditiis nihil velit.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(71, 6, 'squash', 23, NULL, '2026-04-12 08:55:47', '2026-04-12 10:55:47', 240.00, 0.00, 'private', 'coached', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(72, 6, 'squash', 23, NULL, '2026-05-13 21:34:55', '2026-05-13 22:34:55', 120.00, 0.00, 'private', 'coached', 4, 'advanced', 'cancelled', 'Ut quia sequi eum sed.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(73, 6, 'squash', 23, NULL, '2026-04-13 03:52:08', '2026-04-13 04:52:08', 150.00, 0.00, 'open_match', 'standard', 4, 'advanced', 'confirmed', 'Nobis perferendis modi eum.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(74, 6, 'squash', 23, NULL, '2026-04-21 16:44:57', '2026-04-21 18:14:57', 75.00, 0.00, 'open_match', 'academy', 4, NULL, 'confirmed', 'Cumque aut provident eligendi.', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(75, 6, 'squash', 23, NULL, '2026-04-01 07:10:40', '2026-04-01 09:10:40', 200.00, 0.00, 'open_match', 'coached', 4, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(76, 7, 'squash', 21, 10, '2026-05-04 16:05:15', '2026-05-04 18:05:15', 150.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', 'Dignissimos architecto non aut quidem laudantium saepe.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(77, 7, 'squash', 21, 10, '2026-05-15 07:56:25', '2026-05-15 08:56:25', 50.00, 0.00, 'private', 'coached', 2, 'intermediate', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(78, 7, 'squash', 21, 10, '2026-05-20 17:45:50', '2026-05-20 19:45:50', 150.00, 0.00, 'open_match', 'standard', 2, 'beginner', 'confirmed', 'Voluptatibus in vitae qui accusantium perspiciatis cum.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(79, 7, 'squash', 21, 10, '2026-04-02 00:58:08', '2026-04-02 02:58:08', 240.00, 0.00, 'open_match', 'coached', 2, 'intermediate', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(80, 7, 'squash', 21, 10, '2026-04-18 16:03:13', '2026-04-18 18:03:13', 300.00, 0.00, 'open_match', 'academy', 4, NULL, 'confirmed', 'Accusamus unde iusto et fugit eligendi omnis.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(81, 7, 'squash', 21, 10, '2026-04-14 06:39:23', '2026-04-14 08:09:23', 150.00, 0.00, 'open_match', 'academy', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(82, 7, 'squash', 21, 10, '2026-04-16 19:42:35', '2026-04-16 21:42:35', 300.00, 0.00, 'open_match', 'academy', 2, 'advanced', 'confirmed', 'Non tempore aut voluptatem.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(83, 7, 'squash', 21, 10, '2026-05-06 08:14:20', '2026-05-06 09:44:20', 225.00, 0.00, 'open_match', 'coached', 4, NULL, 'confirmed', 'Voluptate asperiores placeat qui sint.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(84, 7, 'squash', 21, 10, '2026-05-16 04:56:22', '2026-05-16 05:56:22', 150.00, 0.00, 'open_match', 'coached', 2, 'intermediate', 'pending', 'Officiis necessitatibus expedita consequatur atque nihil dicta quis.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(85, 7, 'squash', 21, 10, '2026-04-26 20:00:21', '2026-04-26 22:00:21', 240.00, 0.00, 'open_match', 'standard', 4, 'beginner', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(86, 7, 'squash', 21, 10, '2026-04-17 12:17:00', '2026-04-17 14:17:00', 100.00, 0.00, 'private', 'coached', 4, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(87, 7, 'squash', 21, 10, '2026-05-22 15:17:17', '2026-05-22 16:47:17', 150.00, 0.00, 'open_match', 'academy', 2, NULL, 'cancelled', 'Hic et autem et ut ut consequatur ut voluptas.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(88, 7, 'squash', 21, 10, '2026-04-24 14:19:02', '2026-04-24 15:19:02', 75.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', 'Consequatur aliquid ipsum est repellendus molestiae dolore tempora sunt.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(89, 7, 'squash', 21, 10, '2026-04-19 21:47:33', '2026-04-19 23:17:33', 75.00, 0.00, 'private', 'coached', 4, 'beginner', 'confirmed', 'Facere modi iste nam molestias deleniti aperiam non.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(90, 7, 'squash', 21, 10, '2026-04-30 03:08:44', '2026-04-30 04:38:44', 180.00, 0.00, 'open_match', 'standard', 4, 'beginner', 'confirmed', 'Voluptate illo voluptatem voluptatem rerum eius iure.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(91, 8, 'padel', 52, 7, '2026-04-22 12:45:00', '2026-04-22 13:45:00', 100.00, 0.00, 'private', 'standard', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(92, 8, 'padel', 52, 7, '2026-03-29 12:39:18', '2026-03-29 13:39:18', 150.00, 0.00, 'open_match', 'standard', 4, NULL, 'confirmed', 'Occaecati est error natus amet sed.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(93, 8, 'padel', 52, 7, '2026-05-05 00:52:00', '2026-05-05 02:52:00', 150.00, 0.00, 'open_match', 'standard', 4, NULL, 'confirmed', 'Tempore dolore deleniti reprehenderit officiis sit voluptatem voluptatibus.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(94, 8, 'padel', 52, 7, '2026-04-02 19:58:45', '2026-04-02 21:58:45', 150.00, 0.00, 'private', 'academy', 4, NULL, 'confirmed', 'Porro dolor a voluptate nostrum.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(95, 8, 'padel', 52, 7, '2026-05-02 19:33:15', '2026-05-02 20:33:15', 150.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(96, 8, 'padel', 52, 7, '2026-03-29 12:55:45', '2026-03-29 14:55:45', 150.00, 0.00, 'open_match', 'standard', 4, 'intermediate', 'cancelled', 'Laudantium et dolor soluta ex perferendis eum.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(97, 8, 'padel', 52, 7, '2026-04-14 22:00:58', '2026-04-14 23:30:58', 75.00, 0.00, 'open_match', 'coached', 4, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(98, 8, 'padel', 52, 7, '2026-04-22 20:17:44', '2026-04-22 21:17:44', 100.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', 'Omnis hic asperiores ipsum sit.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(99, 8, 'padel', 52, 7, '2026-03-28 00:24:00', '2026-03-28 01:54:00', 150.00, 0.00, 'private', 'academy', 4, NULL, 'confirmed', 'In expedita neque deserunt perspiciatis.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(100, 8, 'padel', 52, 7, '2026-03-26 02:54:22', '2026-03-26 04:24:22', 112.50, 0.00, 'open_match', 'standard', 2, NULL, 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(101, 8, 'padel', 52, 7, '2026-04-03 03:02:34', '2026-04-03 05:02:34', 300.00, 0.00, 'open_match', 'academy', 4, NULL, 'cancelled', 'Quod laborum quis quo et ut corrupti voluptatum.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(102, 8, 'padel', 52, 7, '2026-05-12 16:15:26', '2026-05-12 17:15:26', 50.00, 0.00, 'open_match', 'academy', 4, 'advanced', 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(103, 9, 'padel', 16, NULL, '2026-04-19 09:59:43', '2026-04-19 10:59:43', 150.00, 0.00, 'private', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(104, 9, 'padel', 16, NULL, '2026-05-05 16:52:46', '2026-05-05 18:22:46', 225.00, 0.00, 'private', 'coached', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(105, 9, 'padel', 16, NULL, '2026-03-27 22:54:51', '2026-03-28 00:24:51', 75.00, 0.00, 'open_match', 'standard', 4, NULL, 'confirmed', 'Atque tempore natus enim eos molestias iusto.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(106, 9, 'padel', 16, NULL, '2026-04-12 08:42:43', '2026-04-12 10:42:43', 300.00, 0.00, 'open_match', 'academy', 2, NULL, 'confirmed', 'Ut quia quos fuga provident et.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(107, 9, 'padel', 16, NULL, '2026-04-09 01:57:30', '2026-04-09 03:57:30', 300.00, 0.00, 'open_match', 'coached', 4, 'advanced', 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(108, 9, 'padel', 16, NULL, '2026-04-24 02:37:45', '2026-04-24 04:37:45', 300.00, 0.00, 'private', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(109, 9, 'padel', 16, NULL, '2026-04-05 13:53:47', '2026-04-05 15:23:47', 180.00, 0.00, 'private', 'academy', 2, NULL, 'pending', 'Sed at sed sint saepe.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(110, 9, 'padel', 16, NULL, '2026-04-04 10:37:21', '2026-04-04 11:37:21', 50.00, 0.00, 'open_match', 'standard', 2, 'intermediate', 'cancelled', 'Ad quisquam temporibus odio ut ut quia sit quis.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(111, 10, 'padel', 32, NULL, '2026-03-28 03:47:07', '2026-03-28 05:47:07', 200.00, 0.00, 'private', 'coached', 2, NULL, 'pending', 'Voluptas aut impedit rem eius.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(112, 10, 'padel', 32, NULL, '2026-05-20 07:42:50', '2026-05-20 09:12:50', 75.00, 0.00, 'private', 'standard', 4, 'beginner', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(113, 10, 'padel', 32, NULL, '2026-05-24 16:37:00', '2026-05-24 17:37:00', 75.00, 0.00, 'private', 'coached', 2, 'beginner', 'confirmed', 'Voluptate dolore dolorum quidem molestias saepe perferendis.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(114, 10, 'padel', 32, NULL, '2026-04-22 22:58:38', '2026-04-23 00:58:38', 150.00, 0.00, 'open_match', 'standard', 2, NULL, 'pending', 'Explicabo eius ut quia possimus.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(115, 10, 'padel', 32, NULL, '2026-03-26 23:41:31', '2026-03-27 01:11:31', 180.00, 0.00, 'open_match', 'academy', 4, NULL, 'confirmed', 'Blanditiis quidem accusantium beatae eaque facere ut.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(116, 10, 'padel', 32, NULL, '2026-05-04 07:56:10', '2026-05-04 08:56:10', 50.00, 0.00, 'private', 'standard', 2, 'beginner', 'cancelled', 'Omnis iure nihil sit molestias ut accusantium.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(117, 10, 'padel', 32, NULL, '2026-05-20 12:49:03', '2026-05-20 14:49:03', 100.00, 0.00, 'open_match', 'standard', 4, 'beginner', 'pending', 'Mollitia fuga cumque maiores repudiandae id soluta.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(118, 10, 'padel', 32, NULL, '2026-04-20 13:30:08', '2026-04-20 15:00:08', 225.00, 0.00, 'open_match', 'academy', 2, NULL, 'confirmed', 'Magnam quo animi praesentium.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(119, 10, 'padel', 32, NULL, '2026-05-02 14:37:29', '2026-05-02 16:07:29', 150.00, 0.00, 'private', 'coached', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(120, 10, 'padel', 32, NULL, '2026-05-24 13:54:06', '2026-05-24 15:54:06', 300.00, 0.00, 'open_match', 'academy', 4, 'advanced', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(121, 10, 'padel', 32, NULL, '2026-04-18 22:43:22', '2026-04-19 00:13:22', 112.50, 0.00, 'private', 'standard', 4, NULL, 'confirmed', 'Dicta repudiandae quod in rem modi et.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(122, 10, 'padel', 32, NULL, '2026-04-20 15:52:46', '2026-04-20 16:52:46', 120.00, 0.00, 'private', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(123, 11, 'tennis', 12, 7, '2026-05-20 04:05:25', '2026-05-20 05:35:25', 75.00, 0.00, 'private', 'standard', 4, 'intermediate', 'pending', 'Voluptates perferendis perspiciatis aliquam ipsa commodi reiciendis voluptas labore.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(124, 11, 'tennis', 12, 7, '2026-05-18 08:06:14', '2026-05-18 09:36:14', 180.00, 0.00, 'private', 'academy', 2, 'beginner', 'confirmed', 'Deserunt voluptas aspernatur voluptatibus placeat.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(125, 11, 'tennis', 12, 7, '2026-04-26 23:40:43', '2026-04-27 00:40:43', 120.00, 0.00, 'private', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(126, 11, 'tennis', 12, 7, '2026-03-26 22:41:24', '2026-03-26 23:41:24', 100.00, 0.00, 'private', 'standard', 4, NULL, 'cancelled', 'Est exercitationem voluptas iusto fugit.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(127, 11, 'tennis', 12, 7, '2026-04-11 14:27:58', '2026-04-11 16:27:58', 100.00, 0.00, 'private', 'coached', 4, NULL, 'confirmed', 'Quo saepe aliquam sed.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(128, 11, 'tennis', 12, 7, '2026-04-18 07:10:29', '2026-04-18 09:10:29', 150.00, 0.00, 'private', 'standard', 2, NULL, 'cancelled', 'Itaque voluptate sequi aut voluptatem.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(129, 11, 'tennis', 12, 7, '2026-05-06 22:31:42', '2026-05-07 00:31:42', 240.00, 0.00, 'open_match', 'standard', 4, NULL, 'confirmed', 'Aut aut harum est itaque temporibus qui libero non.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(130, 11, 'tennis', 12, 7, '2026-05-24 09:54:24', '2026-05-24 11:54:24', 300.00, 0.00, 'private', 'academy', 2, 'advanced', 'confirmed', 'Qui omnis non inventore omnis eaque.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(131, 11, 'tennis', 12, 7, '2026-05-06 01:09:09', '2026-05-06 03:09:09', 150.00, 0.00, 'private', 'coached', 4, NULL, 'confirmed', 'Officia illum magni reiciendis.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(132, 11, 'tennis', 12, 7, '2026-04-10 03:27:28', '2026-04-10 05:27:28', 300.00, 0.00, 'open_match', 'standard', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(133, 11, 'tennis', 12, 7, '2026-04-02 08:53:08', '2026-04-02 09:53:08', 50.00, 0.00, 'private', 'academy', 2, 'beginner', 'confirmed', 'Labore quisquam odio nulla.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(134, 11, 'tennis', 12, 7, '2026-04-23 02:02:55', '2026-04-23 04:02:55', 240.00, 0.00, 'open_match', 'academy', 4, 'beginner', 'confirmed', 'Vitae deleniti eos est asperiores aut voluptas odio.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(135, 11, 'tennis', 12, 7, '2026-04-19 13:12:23', '2026-04-19 15:12:23', 150.00, 0.00, 'open_match', 'standard', 4, 'intermediate', 'confirmed', 'Sunt quae voluptate temporibus numquam dolor vel in.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(136, 11, 'tennis', 12, 7, '2026-05-12 10:54:49', '2026-05-12 12:54:49', 200.00, 0.00, 'private', 'standard', 2, 'intermediate', 'confirmed', 'Vero dolores ducimus voluptas omnis ea ut.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(137, 11, 'tennis', 12, 7, '2026-03-29 09:04:57', '2026-03-29 10:34:57', 112.50, 0.00, 'open_match', 'coached', 4, 'intermediate', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 20:12:01'),
(138, 12, 'tennis', 33, NULL, '2026-04-17 01:24:40', '2026-04-17 03:24:40', 100.00, 0.00, 'private', 'coached', 2, 'advanced', 'confirmed', 'Labore ab aliquid et distinctio labore totam.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(139, 12, 'tennis', 33, NULL, '2026-05-11 00:35:03', '2026-05-11 02:05:03', 225.00, 0.00, 'open_match', 'academy', 2, 'advanced', 'confirmed', 'Dolore ipsum numquam odio provident voluptate.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(140, 12, 'tennis', 33, NULL, '2026-04-23 16:25:52', '2026-04-23 18:25:52', 200.00, 0.00, 'open_match', 'academy', 2, NULL, 'confirmed', 'Et consequatur minus officia aspernatur culpa aut qui consequuntur.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(141, 12, 'tennis', 33, NULL, '2026-05-17 11:25:34', '2026-05-17 13:25:34', 200.00, 0.00, 'open_match', 'academy', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(142, 12, 'tennis', 33, NULL, '2026-04-08 10:29:36', '2026-04-08 12:29:36', 300.00, 0.00, 'private', 'standard', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(143, 12, 'tennis', 33, NULL, '2026-04-12 05:41:13', '2026-04-12 07:11:13', 225.00, 0.00, 'open_match', 'standard', 4, 'intermediate', 'confirmed', 'Dolor voluptas occaecati officia.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(144, 12, 'tennis', 33, NULL, '2026-05-19 07:05:48', '2026-05-19 09:05:48', 150.00, 0.00, 'private', 'academy', 2, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(145, 12, 'tennis', 33, NULL, '2026-05-14 16:43:51', '2026-05-14 18:43:51', 100.00, 0.00, 'open_match', 'academy', 4, 'beginner', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(146, 12, 'tennis', 33, NULL, '2026-05-21 07:10:17', '2026-05-21 08:10:17', 75.00, 0.00, 'private', 'coached', 2, 'advanced', 'confirmed', 'Magni sit ratione quod sed tempora commodi.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(147, 12, 'tennis', 33, NULL, '2026-04-12 19:52:07', '2026-04-12 20:52:07', 150.00, 0.00, 'private', 'standard', 2, 'advanced', 'confirmed', 'Ea esse vel fuga vel commodi veritatis est dolor.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(148, 12, 'tennis', 33, NULL, '2026-04-18 14:57:22', '2026-04-18 15:57:22', 100.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', 'Dolores et rerum sit et voluptatibus.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(149, 12, 'tennis', 33, NULL, '2026-05-24 16:16:19', '2026-05-24 17:46:19', 225.00, 0.00, 'open_match', 'coached', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(150, 12, 'tennis', 33, NULL, '2026-05-13 02:24:24', '2026-05-13 04:24:24', 240.00, 0.00, 'open_match', 'academy', 2, 'intermediate', 'pending', 'Velit quisquam amet voluptatem.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(151, 12, 'tennis', 33, NULL, '2026-04-08 01:30:52', '2026-04-08 03:30:52', 300.00, 0.00, 'private', 'coached', 4, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(152, 13, 'tennis', 23, NULL, '2026-05-16 04:28:03', '2026-05-16 05:28:03', 150.00, 0.00, 'open_match', 'academy', 4, 'intermediate', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(153, 13, 'tennis', 23, NULL, '2026-05-11 08:57:02', '2026-05-11 09:57:02', 50.00, 0.00, 'private', 'standard', 2, NULL, 'confirmed', 'Ipsa eos modi ducimus reiciendis sequi autem.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(154, 13, 'tennis', 23, NULL, '2026-05-11 04:51:26', '2026-05-11 06:51:26', 100.00, 0.00, 'private', 'academy', 2, 'intermediate', 'cancelled', 'Illo et laborum atque consequatur.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(155, 13, 'tennis', 23, NULL, '2026-05-01 09:50:32', '2026-05-01 11:20:32', 150.00, 0.00, 'open_match', 'academy', 2, 'beginner', 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(156, 13, 'tennis', 23, NULL, '2026-05-19 17:24:27', '2026-05-19 18:24:27', 50.00, 0.00, 'open_match', 'coached', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(157, 13, 'tennis', 23, NULL, '2026-04-11 12:46:01', '2026-04-11 14:16:01', 180.00, 0.00, 'open_match', 'academy', 2, 'beginner', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(158, 13, 'tennis', 23, NULL, '2026-04-17 22:13:50', '2026-04-17 23:13:50', 120.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', 'Dolores eius veniam occaecati consequatur autem dolore nesciunt.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(159, 13, 'tennis', 23, NULL, '2026-04-12 21:33:10', '2026-04-12 23:03:10', 180.00, 0.00, 'private', 'standard', 4, NULL, 'pending', 'Et sit fugiat ipsa quibusdam.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(160, 13, 'tennis', 23, NULL, '2026-04-13 17:55:30', '2026-04-13 18:55:30', 120.00, 0.00, 'private', 'coached', 2, NULL, 'confirmed', 'Et voluptatem voluptatem velit atque aliquam amet fugit.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(161, 13, 'tennis', 23, NULL, '2026-04-27 18:55:51', '2026-04-27 20:25:51', 180.00, 0.00, 'private', 'academy', 2, 'intermediate', 'confirmed', 'Laboriosam laborum perferendis maxime et et.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(162, 13, 'tennis', 23, NULL, '2026-04-30 13:14:10', '2026-04-30 15:14:10', 100.00, 0.00, 'private', 'standard', 4, NULL, 'confirmed', 'Rem distinctio quo molestias.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(163, 13, 'tennis', 23, NULL, '2026-03-29 03:00:40', '2026-03-29 05:00:40', 300.00, 0.00, 'private', 'academy', 2, 'beginner', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(164, 13, 'tennis', 23, NULL, '2026-04-10 13:39:20', '2026-04-10 15:39:20', 100.00, 0.00, 'open_match', 'coached', 2, NULL, 'pending', 'Similique rem sit earum iste.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(165, 14, 'padel', 20, NULL, '2026-05-14 01:10:19', '2026-05-14 02:10:19', 150.00, 0.00, 'private', 'coached', 2, 'beginner', 'cancelled', 'Sed aliquid modi dolor reiciendis at quis corrupti voluptas.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(166, 14, 'padel', 20, NULL, '2026-05-23 02:30:08', '2026-05-23 04:30:08', 100.00, 0.00, 'private', 'coached', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(167, 14, 'padel', 20, NULL, '2026-04-22 14:34:21', '2026-04-22 16:04:21', 225.00, 0.00, 'private', 'standard', 4, 'intermediate', 'pending', 'A praesentium fugiat doloribus.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(168, 14, 'padel', 20, NULL, '2026-03-28 01:27:38', '2026-03-28 03:27:38', 200.00, 0.00, 'private', 'coached', 2, NULL, 'confirmed', 'Corporis quisquam praesentium laudantium et odit magnam voluptas.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(169, 14, 'padel', 20, NULL, '2026-05-07 02:01:47', '2026-05-07 04:01:47', 100.00, 0.00, 'private', 'coached', 2, 'beginner', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(170, 14, 'padel', 20, NULL, '2026-04-14 10:01:54', '2026-04-14 12:01:54', 200.00, 0.00, 'private', 'standard', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(171, 14, 'padel', 20, NULL, '2026-05-05 10:07:27', '2026-05-05 11:07:27', 120.00, 0.00, 'private', 'academy', 2, 'beginner', 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(172, 14, 'padel', 20, NULL, '2026-03-30 00:37:48', '2026-03-30 01:37:48', 75.00, 0.00, 'private', 'academy', 2, NULL, 'cancelled', 'Quisquam qui nihil ut dolorum harum.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(173, 14, 'padel', 20, NULL, '2026-05-13 11:08:29', '2026-05-13 12:38:29', 225.00, 0.00, 'private', 'academy', 4, 'intermediate', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(174, 15, 'padel', 42, 10, '2026-05-18 01:42:39', '2026-05-18 03:42:39', 150.00, 0.00, 'private', 'academy', 2, NULL, 'confirmed', 'Est minus non ad facere.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(175, 15, 'padel', 42, 10, '2026-05-10 14:47:55', '2026-05-10 15:47:55', 150.00, 0.00, 'private', 'coached', 2, 'beginner', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(176, 15, 'padel', 42, 10, '2026-05-01 19:25:38', '2026-05-01 20:25:38', 50.00, 0.00, 'open_match', 'academy', 2, NULL, 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(177, 15, 'padel', 42, 10, '2026-04-11 05:44:00', '2026-04-11 07:14:00', 225.00, 0.00, 'private', 'coached', 2, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(178, 15, 'padel', 42, 10, '2026-04-10 15:14:07', '2026-04-10 16:44:07', 150.00, 0.00, 'open_match', 'coached', 4, 'advanced', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(179, 15, 'padel', 42, 10, '2026-04-25 11:15:41', '2026-04-25 12:15:41', 150.00, 0.00, 'private', 'coached', 4, NULL, 'confirmed', 'Nihil voluptatum eaque aut velit non sed error.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(180, 15, 'padel', 42, 10, '2026-05-24 08:04:27', '2026-05-24 09:34:27', 75.00, 0.00, 'open_match', 'coached', 2, NULL, 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(181, 15, 'padel', 42, 10, '2026-04-15 05:16:35', '2026-04-15 06:46:35', 75.00, 0.00, 'private', 'coached', 2, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(182, 16, 'padel', 49, NULL, '2026-04-16 22:53:35', '2026-04-16 23:53:35', 75.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', 'Porro id totam ut dicta.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(183, 16, 'padel', 49, NULL, '2026-05-02 10:58:07', '2026-05-02 12:58:07', 150.00, 0.00, 'open_match', 'coached', 4, NULL, 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(184, 16, 'padel', 49, NULL, '2026-04-26 19:06:51', '2026-04-26 20:06:51', 100.00, 0.00, 'open_match', 'academy', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(185, 16, 'padel', 49, NULL, '2026-04-14 21:25:45', '2026-04-14 22:55:45', 112.50, 0.00, 'open_match', 'standard', 2, 'intermediate', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(186, 16, 'padel', 49, NULL, '2026-05-10 20:16:49', '2026-05-10 22:16:49', 150.00, 0.00, 'private', 'standard', 2, 'beginner', 'confirmed', 'In sint ipsa odio sit.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(187, 16, 'padel', 49, NULL, '2026-03-31 14:31:01', '2026-03-31 16:01:01', 180.00, 0.00, 'open_match', 'coached', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(188, 16, 'padel', 49, NULL, '2026-03-31 22:37:01', '2026-04-01 00:07:01', 180.00, 0.00, 'open_match', 'academy', 2, 'beginner', 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(189, 16, 'padel', 49, NULL, '2026-04-14 11:59:47', '2026-04-14 13:29:47', 75.00, 0.00, 'open_match', 'standard', 2, NULL, 'cancelled', 'Doloribus corrupti exercitationem facilis aut et quam.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(190, 16, 'padel', 49, NULL, '2026-04-12 16:19:40', '2026-04-12 18:19:40', 240.00, 0.00, 'open_match', 'coached', 2, 'intermediate', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(191, 16, 'padel', 49, NULL, '2026-04-29 21:20:58', '2026-04-29 23:20:58', 100.00, 0.00, 'private', 'standard', 4, 'intermediate', 'confirmed', 'Illum autem laboriosam ab sunt a sit cupiditate.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(192, 16, 'padel', 49, NULL, '2026-04-21 18:40:21', '2026-04-21 19:40:21', 120.00, 0.00, 'open_match', 'academy', 2, NULL, 'confirmed', 'Quaerat voluptatem veniam consequatur nihil quas eligendi deleniti.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(193, 17, 'padel', 60, 10, '2026-04-29 18:23:46', '2026-04-29 19:53:46', 180.00, 0.00, 'private', 'standard', 4, NULL, 'confirmed', 'Minima autem provident dolor vero in dolore.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(194, 17, 'padel', 60, 10, '2026-05-03 08:57:59', '2026-05-03 09:57:59', 100.00, 0.00, 'open_match', 'standard', 2, 'beginner', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(195, 17, 'padel', 60, 10, '2026-05-21 20:27:24', '2026-05-21 22:27:24', 240.00, 0.00, 'open_match', 'coached', 4, NULL, 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(196, 17, 'padel', 60, 10, '2026-05-23 11:59:02', '2026-05-23 13:29:02', 75.00, 0.00, 'private', 'coached', 4, 'advanced', 'confirmed', 'Consequuntur omnis id ducimus sit qui vitae pariatur.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(197, 17, 'padel', 60, 10, '2026-03-25 22:46:11', '2026-03-25 23:46:11', 100.00, 0.00, 'open_match', 'standard', 2, NULL, 'confirmed', 'Suscipit pariatur inventore commodi.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(198, 17, 'padel', 60, 10, '2026-04-11 17:42:34', '2026-04-11 19:12:34', 150.00, 0.00, 'open_match', 'academy', 4, 'intermediate', 'confirmed', 'Doloremque adipisci ea qui perferendis mollitia voluptatem.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(199, 17, 'padel', 60, 10, '2026-05-11 22:26:34', '2026-05-11 23:56:34', 180.00, 0.00, 'open_match', 'standard', 2, 'beginner', 'pending', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(200, 17, 'padel', 60, 10, '2026-04-05 02:31:50', '2026-04-05 03:31:50', 50.00, 0.00, 'private', 'coached', 2, 'beginner', 'cancelled', 'Odit voluptatibus est voluptatem tempora maxime in officiis.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(201, 17, 'padel', 60, 10, '2026-04-28 03:54:17', '2026-04-28 05:24:17', 180.00, 0.00, 'private', 'standard', 4, NULL, 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(202, 17, 'padel', 60, 10, '2026-04-03 03:32:08', '2026-04-03 05:32:08', 300.00, 0.00, 'open_match', 'standard', 2, 'intermediate', 'confirmed', 'Debitis ratione esse quia dignissimos.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(203, 17, 'padel', 60, 10, '2026-04-11 00:19:49', '2026-04-11 01:49:49', 180.00, 0.00, 'private', 'academy', 4, 'advanced', 'confirmed', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(204, 17, 'padel', 60, 10, '2026-05-23 11:21:00', '2026-05-23 12:21:00', 100.00, 0.00, 'open_match', 'coached', 2, 'beginner', 'cancelled', 'Quas expedita et veritatis provident.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(205, 17, 'padel', 60, 10, '2026-05-16 21:28:04', '2026-05-16 22:28:04', 75.00, 0.00, 'private', 'academy', 4, 'intermediate', 'cancelled', 'Aut perferendis corporis consequatur et est natus quam.', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(206, 17, 'padel', 60, 10, '2026-04-20 12:53:17', '2026-04-20 13:53:17', 120.00, 0.00, 'open_match', 'standard', 4, 'advanced', 'cancelled', NULL, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `booking_participants`
--

CREATE TABLE `booking_participants` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `amount_due` decimal(8,2) NOT NULL,
  `payment_status` enum('pending','paid') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'padel',
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscription_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `settings` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `name`, `sport_type`, `address`, `subscription_status`, `settings`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Skiles-Hackett Padel Club', 'squash', '3004 Bessie Locks, Dubai', 'trial', '{\"currency\": \"SAR\", \"timezone\": \"Asia/Dubai\", \"closing_hour\": 23, \"opening_hour\": 6}', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(2, 'Walker-Shanahan Padel Club', 'squash', '7726 Mills Meadows, Abu Dhabi', 'trial', '{\"currency\": \"AED\", \"timezone\": \"Asia/Riyadh\", \"closing_hour\": 23, \"opening_hour\": 6}', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 'Braun-Tromp Padel Club', 'padel', '416 Medhurst Stravenue Apt. 874, Casablanca', 'inactive', '{\"currency\": \"USD\", \"timezone\": \"Asia/Riyadh\", \"closing_hour\": 23, \"opening_hour\": 6}', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 'Balistreri Ltd Padel Club', 'tennis', '843 Garfield Squares Suite 720, Abu Dhabi', 'trial', '{\"currency\": \"USD\", \"timezone\": \"Africa/Cairo\", \"closing_hour\": 23, \"opening_hour\": 6}', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(5, 'Kunde Inc Padel Club', 'padel', '189 Carlie Mission Apt. 858, Tunis', 'active', '{\"currency\": \"QAR\", \"timezone\": \"Africa/Cairo\", \"closing_hour\": 23, \"opening_hour\": 6}', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20');

-- --------------------------------------------------------

--
-- Table structure for table `club_users`
--

CREATE TABLE `club_users` (
  `id` bigint UNSIGNED NOT NULL,
  `club_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `role` enum('owner','manager','staff') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `club_users`
--

INSERT INTO `club_users` (`id`, `club_id`, `user_id`, `role`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'owner', '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(2, 1, 3, 'staff', '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 1, 11, 'staff', '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 2, 1, 'owner', '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(5, 2, 4, 'staff', '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(6, 2, 11, 'staff', '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(7, 3, 1, 'owner', '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(8, 3, 6, 'staff', '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(9, 3, 10, 'staff', '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(10, 4, 1, 'owner', '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(11, 4, 4, 'staff', '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(12, 4, 6, 'staff', '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(13, 5, 1, 'owner', '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(14, 5, 8, 'staff', '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(15, 5, 10, 'staff', '2026-04-24 19:36:21', '2026-04-24 19:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `courts`
--

CREATE TABLE `courts` (
  `id` bigint UNSIGNED NOT NULL,
  `club_id` bigint UNSIGNED NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'padel',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('indoor','outdoor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_per_hour` decimal(8,2) NOT NULL,
  `capacity` smallint UNSIGNED NOT NULL DEFAULT '4',
  `slot_duration_minutes` int UNSIGNED NOT NULL DEFAULT '60',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`id`, `club_id`, `sport_type`, `name`, `type`, `price_per_hour`, `capacity`, `slot_duration_minutes`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'squash', 'Court 13', 'outdoor', 120.00, 4, 90, 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(2, 1, 'squash', 'Court 9', 'indoor', 50.00, 4, 90, 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 1, 'squash', 'Court 12', 'outdoor', 150.00, 4, 90, 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 2, 'squash', 'Court 7', 'indoor', 200.00, 4, 90, 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(5, 2, 'squash', 'Court 13', 'indoor', 75.00, 2, 90, 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(6, 2, 'squash', 'Court 6', 'indoor', 120.00, 2, 90, 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(7, 2, 'squash', 'Court 17', 'outdoor', 100.00, 2, 60, 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(8, 3, 'padel', 'Court 10', 'outdoor', 150.00, 4, 60, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(9, 3, 'padel', 'Court 18', 'indoor', 75.00, 4, 90, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(10, 3, 'padel', 'Court 19', 'indoor', 120.00, 2, 90, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(11, 4, 'tennis', 'Court 20', 'outdoor', 50.00, 2, 90, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(12, 4, 'tennis', 'Court 19', 'outdoor', 150.00, 2, 60, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(13, 4, 'tennis', 'Court 6', 'outdoor', 150.00, 4, 90, 0, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(14, 5, 'padel', 'Court 3', 'indoor', 50.00, 4, 60, 0, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(15, 5, 'padel', 'Court 19', 'outdoor', 120.00, 4, 60, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(16, 5, 'padel', 'Court 9', 'outdoor', 120.00, 2, 60, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(17, 5, 'padel', 'Court 8', 'outdoor', 200.00, 2, 60, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `court_slots`
--

CREATE TABLE `court_slots` (
  `id` bigint UNSIGNED NOT NULL,
  `court_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'padel',
  `slot_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'training',
  `day_of_week` tinyint UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `coach_user_id` bigint UNSIGNED DEFAULT NULL,
  `max_players` smallint UNSIGNED NOT NULL DEFAULT '4',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `skill_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `court_slots`
--

INSERT INTO `court_slots` (`id`, `court_id`, `title`, `sport_type`, `slot_type`, `day_of_week`, `start_time`, `end_time`, `coach_user_id`, `max_players`, `price`, `skill_level`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Weekend Match', 'squash', 'open_play', 4, '11:00:00', '12:00:00', 8, 4, 100.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(2, 1, 'Weekend Match', 'squash', 'training', 1, '12:00:00', '13:30:00', 8, 2, 100.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 1, 'Beginner Class', 'squash', 'open_play', 5, '10:00:00', '11:00:00', 8, 4, 75.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 1, 'Pro Session', 'squash', 'academy', 5, '14:00:00', '15:30:00', 8, 2, 30.00, 'intermediate', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(5, 1, 'Open Play', 'squash', 'match', 5, '08:00:00', '09:30:00', 8, 4, 100.00, 'beginner', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(6, 1, 'Evening Session', 'squash', 'match', 1, '20:00:00', '21:00:00', 8, 2, 50.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(7, 2, 'Pro Session', 'squash', 'training', 5, '10:00:00', '11:30:00', 8, 2, 75.00, 'beginner', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(8, 2, 'Morning Training', 'squash', 'training', 3, '14:00:00', '15:30:00', 8, 4, 75.00, 'intermediate', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(9, 2, 'Open Play', 'squash', 'academy', 6, '13:00:00', '14:30:00', 8, 2, 30.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(10, 2, 'Pro Session', 'squash', 'open_play', 5, '16:00:00', '17:30:00', 8, 2, 75.00, 'advanced', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(11, 2, 'Evening Session', 'squash', 'training', 4, '18:00:00', '19:30:00', 8, 4, 100.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(12, 2, 'Morning Training', 'squash', 'academy', 1, '11:00:00', '12:30:00', 8, 2, 30.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(13, 2, 'Pro Session', 'squash', 'open_play', 5, '18:00:00', '19:00:00', 8, 4, 50.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(14, 2, 'Pro Session', 'squash', 'match', 2, '08:00:00', '09:00:00', 8, 4, 75.00, 'intermediate', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(15, 3, 'Evening Session', 'squash', 'open_play', 6, '09:00:00', '10:30:00', 9, 4, 50.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(16, 3, 'Weekend Match', 'squash', 'training', 1, '09:00:00', '10:00:00', 9, 2, 50.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(17, 3, 'Beginner Class', 'squash', 'open_play', 6, '09:00:00', '10:30:00', 9, 2, 30.00, 'advanced', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(18, 3, 'Beginner Class', 'squash', 'training', 4, '17:00:00', '18:30:00', 9, 2, 100.00, 'beginner', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(19, 3, 'Open Play', 'squash', 'match', 3, '08:00:00', '09:00:00', 9, 4, 30.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(20, 3, 'Evening Session', 'squash', 'academy', 6, '16:00:00', '17:00:00', 9, 4, 100.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(21, 4, 'Morning Training', 'squash', 'match', 2, '16:00:00', '17:30:00', 10, 4, 75.00, 'intermediate', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(22, 4, 'Weekend Match', 'squash', 'training', 6, '13:00:00', '14:30:00', 10, 2, 75.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(23, 4, 'Pro Session', 'squash', 'open_play', 5, '08:00:00', '09:00:00', 10, 2, 50.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(24, 4, 'Evening Session', 'squash', 'match', 5, '08:00:00', '09:30:00', 10, 2, 75.00, 'intermediate', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(25, 4, 'Open Play', 'squash', 'open_play', 6, '14:00:00', '15:30:00', 10, 2, 30.00, 'intermediate', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(26, 4, 'Morning Training', 'squash', 'match', 2, '20:00:00', '21:00:00', 10, 2, 100.00, 'advanced', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(27, 5, 'Open Play', 'squash', 'academy', 3, '12:00:00', '13:00:00', 9, 2, 75.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(28, 5, 'Evening Session', 'squash', 'match', 3, '12:00:00', '13:30:00', 9, 2, 50.00, 'beginner', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(29, 5, 'Beginner Class', 'squash', 'academy', 6, '15:00:00', '16:30:00', 9, 2, 30.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(30, 5, 'Weekend Match', 'squash', 'academy', 6, '15:00:00', '16:30:00', 9, 2, 75.00, 'intermediate', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(31, 5, 'Weekend Match', 'squash', 'open_play', 0, '14:00:00', '15:00:00', 9, 2, 50.00, 'advanced', 0, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(32, 6, 'Pro Session', 'squash', 'open_play', 6, '08:00:00', '09:00:00', 3, 2, 50.00, 'beginner', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(33, 6, 'Evening Session', 'squash', 'match', 4, '17:00:00', '18:00:00', 3, 4, 100.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(34, 6, 'Morning Training', 'squash', 'match', 5, '09:00:00', '10:00:00', 3, 2, 30.00, 'intermediate', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(35, 6, 'Beginner Class', 'squash', 'open_play', 1, '18:00:00', '19:30:00', 3, 4, 30.00, 'beginner', 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(36, 6, 'Morning Training', 'squash', 'open_play', 4, '13:00:00', '14:30:00', 3, 4, 50.00, NULL, 1, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(37, 7, 'Weekend Match', 'squash', 'open_play', 1, '16:00:00', '17:00:00', 5, 2, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(38, 7, 'Evening Session', 'squash', 'match', 2, '15:00:00', '16:00:00', 5, 2, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(39, 7, 'Weekend Match', 'squash', 'match', 6, '18:00:00', '19:00:00', 5, 2, 50.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(40, 7, 'Pro Session', 'squash', 'open_play', 3, '14:00:00', '15:30:00', 5, 4, 30.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(41, 7, 'Pro Session', 'squash', 'open_play', 0, '08:00:00', '09:30:00', 5, 2, 50.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(42, 7, 'Open Play', 'squash', 'training', 1, '10:00:00', '11:00:00', 5, 4, 50.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(43, 7, 'Beginner Class', 'squash', 'academy', 2, '10:00:00', '11:00:00', 5, 4, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(44, 7, 'Morning Training', 'squash', 'academy', 5, '19:00:00', '20:00:00', 5, 2, 30.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(45, 8, 'Weekend Match', 'padel', 'open_play', 5, '17:00:00', '18:30:00', 5, 2, 30.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(46, 8, 'Evening Session', 'padel', 'open_play', 5, '15:00:00', '16:00:00', 5, 4, 75.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(47, 8, 'Morning Training', 'padel', 'training', 3, '15:00:00', '16:00:00', 5, 4, 50.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(48, 8, 'Morning Training', 'padel', 'training', 0, '15:00:00', '16:30:00', 5, 4, 75.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(49, 8, 'Morning Training', 'padel', 'training', 3, '16:00:00', '17:30:00', 5, 4, 30.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(50, 8, 'Beginner Class', 'padel', 'match', 6, '17:00:00', '18:00:00', 5, 2, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(51, 8, 'Beginner Class', 'padel', 'match', 4, '19:00:00', '20:30:00', 5, 2, 50.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(52, 8, 'Open Play', 'padel', 'training', 0, '20:00:00', '21:30:00', 5, 2, 100.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(53, 9, 'Evening Session', 'padel', 'match', 4, '11:00:00', '12:00:00', 8, 4, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(54, 9, 'Evening Session', 'padel', 'training', 2, '20:00:00', '21:30:00', 8, 2, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(55, 9, 'Pro Session', 'padel', 'open_play', 4, '09:00:00', '10:00:00', 8, 4, 50.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(56, 9, 'Open Play', 'padel', 'academy', 1, '18:00:00', '19:00:00', 8, 2, 50.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(57, 9, 'Evening Session', 'padel', 'academy', 6, '15:00:00', '16:30:00', 8, 4, 30.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(58, 10, 'Evening Session', 'padel', 'open_play', 3, '16:00:00', '17:00:00', 3, 2, 100.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(59, 10, 'Evening Session', 'padel', 'training', 2, '12:00:00', '13:00:00', 3, 4, 30.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(60, 10, 'Beginner Class', 'padel', 'match', 3, '16:00:00', '17:30:00', 3, 2, 100.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(61, 10, 'Morning Training', 'padel', 'match', 5, '08:00:00', '09:30:00', 3, 4, 30.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(62, 10, 'Morning Training', 'padel', 'academy', 4, '17:00:00', '18:00:00', 3, 4, 30.00, NULL, 0, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(63, 11, 'Pro Session', 'tennis', 'training', 5, '20:00:00', '21:30:00', 5, 2, 50.00, 'intermediate', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(64, 11, 'Weekend Match', 'tennis', 'academy', 2, '10:00:00', '11:30:00', 5, 4, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(65, 11, 'Beginner Class', 'tennis', 'open_play', 3, '09:00:00', '10:30:00', 5, 4, 100.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(66, 11, 'Evening Session', 'tennis', 'academy', 5, '18:00:00', '19:00:00', 5, 4, 30.00, 'intermediate', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(67, 11, 'Beginner Class', 'tennis', 'match', 0, '14:00:00', '15:30:00', 5, 2, 30.00, 'beginner', 0, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(68, 11, 'Weekend Match', 'tennis', 'match', 2, '18:00:00', '19:00:00', 5, 2, 75.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(69, 11, 'Weekend Match', 'tennis', 'match', 6, '12:00:00', '13:00:00', 5, 4, 75.00, 'intermediate', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(70, 11, 'Open Play', 'tennis', 'match', 6, '13:00:00', '14:00:00', 5, 2, 30.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(71, 12, 'Open Play', 'tennis', 'match', 5, '09:00:00', '10:00:00', 9, 2, 30.00, 'intermediate', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(72, 12, 'Weekend Match', 'tennis', 'academy', 0, '08:00:00', '09:00:00', 9, 4, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(73, 12, 'Weekend Match', 'tennis', 'match', 4, '19:00:00', '20:00:00', 9, 2, 30.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(74, 12, 'Beginner Class', 'tennis', 'training', 6, '20:00:00', '21:30:00', 9, 4, 30.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(75, 12, 'Beginner Class', 'tennis', 'open_play', 2, '09:00:00', '10:00:00', 9, 2, 30.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(76, 12, 'Evening Session', 'tennis', 'match', 4, '17:00:00', '18:00:00', 9, 4, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(77, 12, 'Beginner Class', 'tennis', 'academy', 6, '17:00:00', '18:30:00', 9, 4, 75.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(78, 13, 'Evening Session', 'tennis', 'academy', 5, '15:00:00', '16:30:00', 6, 4, 75.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(79, 13, 'Pro Session', 'tennis', 'academy', 2, '12:00:00', '13:00:00', 6, 4, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(80, 13, 'Evening Session', 'tennis', 'match', 3, '08:00:00', '09:00:00', 6, 4, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(81, 13, 'Open Play', 'tennis', 'open_play', 3, '13:00:00', '14:30:00', 6, 2, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(82, 13, 'Beginner Class', 'tennis', 'match', 6, '12:00:00', '13:30:00', 6, 4, 30.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(83, 13, 'Evening Session', 'tennis', 'open_play', 2, '11:00:00', '12:30:00', 6, 2, 100.00, 'intermediate', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(84, 13, 'Evening Session', 'tennis', 'open_play', 3, '20:00:00', '21:30:00', 6, 2, 30.00, 'advanced', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(85, 14, 'Evening Session', 'padel', 'training', 1, '11:00:00', '12:30:00', 7, 2, 50.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(86, 14, 'Weekend Match', 'padel', 'training', 5, '11:00:00', '12:30:00', 7, 2, 50.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(87, 14, 'Beginner Class', 'padel', 'open_play', 1, '17:00:00', '18:00:00', 7, 2, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(88, 14, 'Beginner Class', 'padel', 'training', 4, '13:00:00', '14:00:00', 7, 2, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(89, 14, 'Open Play', 'padel', 'open_play', 4, '12:00:00', '13:00:00', 7, 2, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(90, 14, 'Weekend Match', 'padel', 'open_play', 3, '07:00:00', '08:00:00', 7, 4, 30.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(91, 14, 'Open Play', 'padel', 'open_play', 6, '12:00:00', '13:30:00', 7, 4, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(92, 15, 'Morning Training', 'padel', 'open_play', 0, '18:00:00', '19:30:00', 5, 4, 75.00, 'intermediate', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(93, 15, 'Morning Training', 'padel', 'open_play', 2, '19:00:00', '20:30:00', 5, 2, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(94, 15, 'Weekend Match', 'padel', 'match', 0, '12:00:00', '13:30:00', 5, 2, 30.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(95, 15, 'Beginner Class', 'padel', 'academy', 4, '10:00:00', '11:30:00', 5, 2, 100.00, NULL, 0, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(96, 15, 'Evening Session', 'padel', 'training', 2, '15:00:00', '16:30:00', 5, 4, 100.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(97, 15, 'Morning Training', 'padel', 'training', 5, '09:00:00', '10:00:00', 5, 2, 30.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(98, 15, 'Pro Session', 'padel', 'academy', 5, '16:00:00', '17:30:00', 5, 4, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(99, 16, 'Evening Session', 'padel', 'match', 1, '12:00:00', '13:00:00', 2, 2, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(100, 16, 'Beginner Class', 'padel', 'match', 6, '20:00:00', '21:30:00', 2, 2, 50.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(101, 16, 'Pro Session', 'padel', 'academy', 2, '13:00:00', '14:30:00', 2, 4, 30.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(102, 16, 'Morning Training', 'padel', 'academy', 4, '18:00:00', '19:00:00', 2, 4, 50.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(103, 16, 'Pro Session', 'padel', 'open_play', 3, '20:00:00', '21:30:00', 2, 2, 100.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(104, 16, 'Evening Session', 'padel', 'match', 3, '07:00:00', '08:00:00', 2, 2, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(105, 17, 'Evening Session', 'padel', 'match', 5, '14:00:00', '15:30:00', 3, 4, 50.00, 'beginner', 0, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(106, 17, 'Evening Session', 'padel', 'match', 1, '14:00:00', '15:30:00', 3, 2, 30.00, 'beginner', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(107, 17, 'Open Play', 'padel', 'training', 0, '20:00:00', '21:00:00', 3, 2, 30.00, 'intermediate', 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(108, 17, 'Morning Training', 'padel', 'academy', 4, '16:00:00', '17:00:00', 3, 2, 100.00, NULL, 0, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(109, 17, 'Beginner Class', 'padel', 'open_play', 6, '09:00:00', '10:30:00', 3, 4, 75.00, NULL, 1, '2026-04-24 19:36:21', '2026-04-24 19:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_21_005704_create_clubs_table', 1),
(5, '2026_03_21_005714_create_club_users_table', 1),
(6, '2026_03_21_005718_create_courts_table', 1),
(7, '2026_03_21_005721_create_bookings_table', 1),
(8, '2026_03_21_005724_create_booking_participants_table', 1),
(9, '2026_03_21_011456_create_payment_transactions_table', 1),
(10, '2026_04_05_211641_create_personal_access_tokens_table', 1),
(11, '2026_04_05_220000_expand_sports_academy_logic', 1),
(12, '2026_04_07_090000_add_user_roles_and_staff_management', 1),
(13, '2026_04_25_000001_create_packages_table', 1),
(14, '2026_04_25_000002_add_player_profile_fields_to_users', 1),
(15, '2026_04_24_232401_add_video_url_to_academy_sessions_table', 2),
(16, '2026_04_24_233626_add_session_plan_and_video_urls_to_academy_sessions_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `club_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'padel',
  `type` enum('sessions','monthly','quarterly','yearly','custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_count` smallint UNSIGNED DEFAULT NULL COMMENT 'Number of sessions included (for sessions type)',
  `duration_days` smallint UNSIGNED DEFAULT NULL COMMENT 'Validity period in days',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `club_id`, `name`, `sport_type`, `type`, `session_count`, `duration_days`, `price`, `description`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Academy Yearly', 'tennis', 'quarterly', NULL, NULL, 100.00, 'Eos accusamus praesentium et qui.', 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(2, 1, 'Pro Monthly', 'tennis', 'sessions', 7, NULL, 200.00, 'Quod doloremque est veritatis nisi eius.', 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 1, 'Summer Intensive', 'tennis', 'monthly', NULL, NULL, 100.00, NULL, 1, NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 2, 'Beginner Starter Pack', 'squash', 'custom', NULL, 14, 1500.00, NULL, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(5, 2, 'Pro Monthly', 'padel', 'yearly', NULL, NULL, 500.00, NULL, 0, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(6, 3, 'Junior Academy', 'padel', 'monthly', NULL, NULL, 1000.00, NULL, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(7, 3, 'Summer Intensive', 'padel', 'quarterly', NULL, NULL, 1000.00, NULL, 0, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(8, 3, 'Weekend Warrior', 'squash', 'sessions', 12, NULL, 100.00, NULL, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(9, 4, 'Pro Monthly', 'padel', 'custom', NULL, 60, 200.00, NULL, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(10, 4, 'Open Court Pass', 'tennis', 'yearly', NULL, NULL, 350.00, NULL, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(11, 4, 'Academy Yearly', 'squash', 'monthly', NULL, NULL, 500.00, NULL, 0, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(12, 5, 'Junior Academy', 'padel', 'yearly', NULL, NULL, 350.00, 'Aut delectus vero nulla rerum dicta explicabo.', 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(13, 5, 'Open Court Pass', 'tennis', 'monthly', NULL, NULL, 100.00, NULL, 1, NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `package_subscriptions`
--

CREATE TABLE `package_subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `starts_at` date NOT NULL,
  `expires_at` date NOT NULL,
  `sessions_remaining` smallint UNSIGNED DEFAULT NULL COMMENT 'Remaining sessions for session-based packages',
  `status` enum('active','expired','suspended','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `package_subscriptions`
--

INSERT INTO `package_subscriptions` (`id`, `package_id`, `user_id`, `starts_at`, `expires_at`, `sessions_remaining`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 15, '2026-03-01', '2026-05-30', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(2, 1, 31, '2026-04-09', '2026-07-08', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 1, 36, '2026-04-23', '2026-07-22', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 1, 61, '2026-03-01', '2026-05-30', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(5, 2, 15, '2026-04-23', '2026-06-22', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(6, 2, 16, '2026-03-31', '2026-05-30', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(7, 2, 19, '2026-03-23', '2026-05-22', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(8, 2, 27, '2026-04-15', '2026-06-14', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(9, 2, 40, '2026-04-22', '2026-06-21', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(10, 2, 43, '2026-03-01', '2026-04-30', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(11, 2, 48, '2026-03-07', '2026-05-06', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(12, 2, 49, '2026-03-22', '2026-05-21', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(13, 2, 55, '2026-03-29', '2026-05-28', 7, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(14, 3, 15, '2026-04-22', '2026-05-22', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(15, 3, 18, '2026-02-28', '2026-03-30', NULL, 'expired', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(16, 3, 33, '2026-04-22', '2026-05-22', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(17, 3, 43, '2026-04-16', '2026-05-16', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(18, 3, 48, '2026-04-05', '2026-05-05', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(19, 3, 55, '2026-04-23', '2026-05-23', NULL, 'active', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(20, 4, 12, '2026-04-23', '2026-05-07', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(21, 4, 29, '2026-04-13', '2026-04-27', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(22, 4, 34, '2026-03-12', '2026-03-26', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(23, 4, 54, '2026-03-22', '2026-04-05', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(24, 5, 32, '2026-04-04', '2027-04-04', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(25, 5, 33, '2026-04-16', '2027-04-16', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(26, 5, 37, '2026-04-17', '2027-04-17', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(27, 5, 39, '2026-03-04', '2027-03-04', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(28, 5, 41, '2026-04-09', '2027-04-09', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(29, 5, 53, '2026-04-17', '2027-04-17', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(30, 6, 21, '2026-04-24', '2026-05-24', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(31, 6, 22, '2026-03-17', '2026-04-16', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(32, 6, 32, '2026-04-23', '2026-05-23', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(33, 6, 39, '2026-04-21', '2026-05-21', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(34, 6, 40, '2026-04-13', '2026-05-13', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(35, 6, 41, '2026-04-01', '2026-05-01', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(36, 6, 48, '2026-03-02', '2026-04-01', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(37, 6, 52, '2026-03-07', '2026-04-06', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(38, 6, 59, '2026-03-25', '2026-04-24', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(39, 6, 61, '2026-04-05', '2026-05-05', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(40, 7, 14, '2026-04-22', '2026-07-21', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(41, 7, 21, '2026-04-10', '2026-07-09', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(42, 7, 29, '2026-04-05', '2026-07-04', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(43, 7, 34, '2026-04-20', '2026-07-19', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(44, 7, 37, '2026-04-04', '2026-07-03', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(45, 7, 50, '2026-03-03', '2026-06-01', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(46, 8, 24, '2026-03-26', '2026-05-25', 12, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(47, 8, 32, '2026-03-17', '2026-05-16', 12, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(48, 8, 42, '2026-03-04', '2026-05-03', 12, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(49, 8, 44, '2026-03-13', '2026-05-12', 12, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(50, 8, 45, '2026-04-02', '2026-06-01', 12, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(51, 8, 50, '2026-04-19', '2026-06-18', 12, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(52, 8, 53, '2026-03-21', '2026-05-20', 12, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(53, 9, 18, '2026-03-18', '2026-05-17', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(54, 9, 28, '2026-03-14', '2026-05-13', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(55, 9, 31, '2026-03-27', '2026-05-26', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(56, 9, 40, '2026-02-24', '2026-04-25', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(57, 9, 45, '2026-04-09', '2026-06-08', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(58, 9, 52, '2026-04-15', '2026-06-14', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(59, 10, 12, '2026-03-25', '2027-03-25', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(60, 10, 17, '2026-03-11', '2027-03-11', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(61, 10, 28, '2026-04-21', '2027-04-21', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(62, 10, 31, '2026-04-04', '2027-04-04', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(63, 10, 40, '2026-04-15', '2027-04-15', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(64, 10, 41, '2026-04-10', '2027-04-10', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(65, 10, 46, '2026-02-28', '2027-02-28', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(66, 10, 49, '2026-04-14', '2027-04-14', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(67, 10, 51, '2026-04-23', '2027-04-23', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(68, 10, 52, '2026-02-28', '2027-02-28', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(69, 11, 13, '2026-03-23', '2026-04-22', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(70, 11, 15, '2026-03-10', '2026-04-09', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(71, 11, 20, '2026-04-04', '2026-05-04', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(72, 11, 24, '2026-04-17', '2026-05-17', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(73, 11, 29, '2026-04-15', '2026-05-15', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(74, 11, 32, '2026-03-15', '2026-04-14', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(75, 11, 57, '2026-04-21', '2026-05-21', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(76, 11, 58, '2026-02-23', '2026-03-25', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(77, 12, 14, '2026-04-17', '2027-04-17', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(78, 12, 16, '2026-03-24', '2027-03-24', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(79, 12, 22, '2026-04-09', '2027-04-09', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(80, 12, 42, '2026-03-04', '2027-03-04', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(81, 12, 46, '2026-03-27', '2027-03-27', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(82, 12, 50, '2026-03-14', '2027-03-14', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(83, 12, 57, '2026-03-09', '2027-03-09', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(84, 13, 18, '2026-03-20', '2026-04-19', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(85, 13, 21, '2026-03-24', '2026-04-23', NULL, 'expired', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(86, 13, 50, '2026-04-06', '2026-05-06', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21'),
(87, 13, 60, '2026-04-06', '2026-05-06', NULL, 'active', NULL, '2026-04-24 19:36:21', '2026-04-24 19:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `paymob_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_payload` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('wNvRcIxNdjO7zMfF5MWinf31VSsnI0JHojaokUhD', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', 'eyJfdG9rZW4iOiJBN2F6T05uY3J5SDc0ZXJUVnBWdnNWUkpDN1AyMDVDMEprUUpNRFJUIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvYm9va2luZ3MiLCJyb3V0ZSI6ImZpbGFtZW50LmFkbWluLnJlc291cmNlcy5ib29raW5ncy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6ImE0NzAwZTVkZjUzMjQ2ZWQwOTMwNmQxMGI3NzdhYWY2MjE2MzAzZGUwM2MwYmM2YzQ1MGZmNDU2M2E4YmNmZjIiLCJ0YWJsZXMiOnsiM2ZkYTdjMWNmMTJlZjg1NDQ2MDE3YzM1YTViYWVkZDNfY29sdW1ucyI6W3sidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjbHViLm5hbWUiLCJsYWJlbCI6IkNsdWIiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoibmFtZSIsImxhYmVsIjoiTmFtZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzcG9ydF90eXBlIiwibGFiZWwiOiJTcG9ydCB0eXBlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InR5cGUiLCJsYWJlbCI6IlBhY2thZ2UgVHlwZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzZXNzaW9uX2NvdW50IiwibGFiZWwiOiJTZXNzaW9ucyIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJkdXJhdGlvbl9kYXlzIiwibGFiZWwiOiJEYXlzIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InByaWNlIiwibGFiZWwiOiJQcmljZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzdWJzY3JpcHRpb25zX2NvdW50IiwibGFiZWwiOiJTdWJzY3JpYmVycyIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJpc19hY3RpdmUiLCJsYWJlbCI6IkFjdGl2ZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjcmVhdGVkX2F0IiwibGFiZWwiOiJDcmVhdGVkIGF0IiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOmZhbHNlLCJpc1RvZ2dsZWFibGUiOnRydWUsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6dHJ1ZX1dLCIyNjNkYzIzMzhiZjIzNjgyZjA4NjU3MWQ4NWYyMTM0ZV9jb2x1bW5zIjpbeyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6Im5hbWUiLCJsYWJlbCI6IlBsYXllciIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJza2lsbF9sZXZlbCIsImxhYmVsIjoiTGV2ZWwiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiZW1haWwiLCJsYWJlbCI6IkVtYWlsIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6dHJ1ZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpmYWxzZX0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InBpdm90LnN0YXJ0c19hdCIsImxhYmVsIjoiU3RhcnQgRGF0ZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJwaXZvdC5leHBpcmVzX2F0IiwibGFiZWwiOiJFeHBpcnkgRGF0ZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJwaXZvdC5zZXNzaW9uc19yZW1haW5pbmciLCJsYWJlbCI6IlNlc3Npb25zIExlZnQiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoicGl2b3Quc3RhdHVzIiwibGFiZWwiOiJTdGF0dXMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfV0sIjBkMjIwYzA1ZDFiNGYyNDg2MzExZjE2ZDZiYTcyNGJmX2NvbHVtbnMiOlt7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiY291cnQubmFtZSIsImxhYmVsIjoiQ291cnQiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoidGl0bGUiLCJsYWJlbCI6IlRpdGxlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InNsb3RfdHlwZSIsImxhYmVsIjoiU2xvdCB0eXBlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImRheV9vZl93ZWVrIiwibGFiZWwiOiJEYXkgb2Ygd2VlayIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzdGFydF90aW1lIiwibGFiZWwiOiJTdGFydCB0aW1lIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImVuZF90aW1lIiwibGFiZWwiOiJFbmQgdGltZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjb2FjaC5uYW1lIiwibGFiZWwiOiJDb2FjaCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOnRydWUsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6ZmFsc2V9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJtYXhfcGxheWVycyIsImxhYmVsIjoiTWF4IHBsYXllcnMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoicHJpY2UiLCJsYWJlbCI6IlByaWNlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InNraWxsX2xldmVsIiwibGFiZWwiOiJTa2lsbCBsZXZlbCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJpc19hY3RpdmUiLCJsYWJlbCI6IkFjdGl2ZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjcmVhdGVkX2F0IiwibGFiZWwiOiJDcmVhdGVkIGF0IiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOmZhbHNlLCJpc1RvZ2dsZWFibGUiOnRydWUsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6dHJ1ZX0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InVwZGF0ZWRfYXQiLCJsYWJlbCI6IlVwZGF0ZWQgYXQiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6ZmFsc2UsImlzVG9nZ2xlYWJsZSI6dHJ1ZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0Ijp0cnVlfV0sIjI5MDFkMWE3ODU1OGQ0ZWEzNGIxNmJhNTk1YzBmMGU3X2NvbHVtbnMiOlt7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiY2x1Yi5uYW1lIiwibGFiZWwiOiJDbHViIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImNvdXJ0Lm5hbWUiLCJsYWJlbCI6IkNvdXJ0IiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImNvYWNoLm5hbWUiLCJsYWJlbCI6IkNvYWNoIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6dHJ1ZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpmYWxzZX0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InRpdGxlIiwibGFiZWwiOiJUaXRsZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzZXNzaW9uX3R5cGUiLCJsYWJlbCI6IlBhY2thZ2UgVHlwZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzdGFydF90aW1lIiwibGFiZWwiOiJTdGFydCB0aW1lIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImVuZF90aW1lIiwibGFiZWwiOiJFbmQgdGltZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJwbGF5ZXJzX2NvdW50IiwibGFiZWwiOiJQbGF5ZXJzIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6Im1heF9wbGF5ZXJzIiwibGFiZWwiOiJNYXggcGxheWVycyIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJwcmljZV9wZXJfcGxheWVyIiwibGFiZWwiOiJQcmljZSBwZXIgcGxheWVyIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InN0YXR1cyIsImxhYmVsIjoiU3RhdHVzIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImNyZWF0ZWRfYXQiLCJsYWJlbCI6IkNyZWF0ZWQgYXQiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6ZmFsc2UsImlzVG9nZ2xlYWJsZSI6dHJ1ZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0Ijp0cnVlfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoidXBkYXRlZF9hdCIsImxhYmVsIjoiVXBkYXRlZCBhdCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjpmYWxzZSwiaXNUb2dnbGVhYmxlIjp0cnVlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOnRydWV9XSwiMjg2YmQ0MDU0ZDcyMGI5MjI5MDI5M2U1MWEyMjY4Y2ZfY29sdW1ucyI6W3sidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjb3VydC5uYW1lIiwibGFiZWwiOiJDb3VydCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJvd25lci5uYW1lIiwibGFiZWwiOiJPd25lciIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjb2FjaC5uYW1lIiwibGFiZWwiOiJDb2FjaCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOnRydWUsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6ZmFsc2V9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzdGFydF90aW1lIiwibGFiZWwiOiJTdGFydCB0aW1lIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImVuZF90aW1lIiwibGFiZWwiOiJFbmQgdGltZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJ0b3RhbF9wcmljZSIsImxhYmVsIjoiVG90YWwgcHJpY2UiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoibWF0Y2hfdHlwZSIsImxhYmVsIjoiTWF0Y2ggdHlwZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzZXNzaW9uX3R5cGUiLCJsYWJlbCI6IlNlc3Npb24gdHlwZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJtYXhfcGxheWVycyIsImxhYmVsIjoiTWF4IHBsYXllcnMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoic3RhdHVzIiwibGFiZWwiOiJTdGF0dXMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiZGVsZXRlZF9hdCIsImxhYmVsIjoiRGVsZXRlZCBhdCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjpmYWxzZSwiaXNUb2dnbGVhYmxlIjp0cnVlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOnRydWV9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjcmVhdGVkX2F0IiwibGFiZWwiOiJDcmVhdGVkIGF0IiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOmZhbHNlLCJpc1RvZ2dsZWFibGUiOnRydWUsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6dHJ1ZX0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InVwZGF0ZWRfYXQiLCJsYWJlbCI6IlVwZGF0ZWQgYXQiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6ZmFsc2UsImlzVG9nZ2xlYWJsZSI6dHJ1ZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0Ijp0cnVlfV0sIjc5ZTk0NTU5MWY1YWFlZDVkNzdjMzVmYjZhNWM1YzFiX2NvbHVtbnMiOlt7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoibmFtZSIsImxhYmVsIjoiTmFtZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzcG9ydF90eXBlIiwibGFiZWwiOiJTcG9ydCB0eXBlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InN1YnNjcmlwdGlvbl9zdGF0dXMiLCJsYWJlbCI6IlN1YnNjcmlwdGlvbiBzdGF0dXMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiY291cnRzX2NvdW50IiwibGFiZWwiOiJDb3VydHMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiYWRkcmVzcyIsImxhYmVsIjoiQWRkcmVzcyIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOnRydWUsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6ZmFsc2V9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJkZWxldGVkX2F0IiwibGFiZWwiOiJEZWxldGVkIGF0IiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOmZhbHNlLCJpc1RvZ2dsZWFibGUiOnRydWUsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6dHJ1ZX0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImNyZWF0ZWRfYXQiLCJsYWJlbCI6IkNyZWF0ZWQgYXQiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6ZmFsc2UsImlzVG9nZ2xlYWJsZSI6dHJ1ZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0Ijp0cnVlfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoidXBkYXRlZF9hdCIsImxhYmVsIjoiVXBkYXRlZCBhdCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjpmYWxzZSwiaXNUb2dnbGVhYmxlIjp0cnVlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOnRydWV9XX19', 1777074391),
('xS7hxddZarMfqJwz348ZATeAgOnRDusAnrNmwzZK', 12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJvd1lRODlvTVB6VzhGeDhIVGN6Q0N1RkxGT2ZPWFZidHN4NkdOaThlIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9wbGF5ZXJcL215LXRyYWluaW5nIiwicm91dGUiOiJmaWxhbWVudC5wbGF5ZXIucGFnZXMubXktdHJhaW5pbmcifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MTIsInBhc3N3b3JkX2hhc2hfd2ViIjoiYjc3YTViMGRiOTlhNjU0N2RlNGY3MzM2MzQyNTc5ZjllYjc0MmI3MjRmOTIxYWFiZjEzYWVmNDYyNDQ3N2ZjYSIsInRhYmxlcyI6eyJkMzkzNmYwNWY5MGQ2MTc5MGQ3ZTk1MTBhZDYzOTM0ZF9jb2x1bW5zIjpbeyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InBhY2thZ2UuY2x1Yi5uYW1lIiwibGFiZWwiOiJDbHViIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InBhY2thZ2UubmFtZSIsImxhYmVsIjoiUGFja2FnZSIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJwYWNrYWdlLnR5cGUiLCJsYWJlbCI6IlR5cGUiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoic3RhcnRzX2F0IiwibGFiZWwiOiJTdGFydCBEYXRlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImV4cGlyZXNfYXQiLCJsYWJlbCI6IkV4cGlyeSBEYXRlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InNlc3Npb25zX3JlbWFpbmluZyIsImxhYmVsIjoiU2Vzc2lvbnMgTGVmdCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzdGF0dXMiLCJsYWJlbCI6IlN0YXR1cyIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9XSwiMGFmNTljYTQwOTYxZDgwNDAxZGM0OGNiZGIxYTc1MWVfY29sdW1ucyI6W3sidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJ0aXRsZSIsImxhYmVsIjoiVGl0bGUiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiY2x1Yi5uYW1lIiwibGFiZWwiOiJDbHViIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImNvYWNoLm5hbWUiLCJsYWJlbCI6IkNvYWNoIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InNlc3Npb25fdHlwZSIsImxhYmVsIjoiUGFja2FnZSBUeXBlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InNraWxsX2xldmVsIiwibGFiZWwiOiJMZXZlbCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzdGFydF90aW1lIiwibGFiZWwiOiJEYXRlICZhbXA7IFRpbWUiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoic3RhdHVzIiwibGFiZWwiOiJTdGF0dXMiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfV0sIjEwMDExN2NkZWRiMDNhNjc0NjgzNGRiZWNjNDZkZjYzX2NvbHVtbnMiOlt7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoiY291cnQuY2x1Yi5uYW1lIiwibGFiZWwiOiJDbHViIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6ImNvdXJ0Lm5hbWUiLCJsYWJlbCI6IkNvdXJ0IiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6Im1hdGNoX3R5cGUiLCJsYWJlbCI6Ik1hdGNoIFR5cGUiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoic2tpbGxfbGV2ZWwiLCJsYWJlbCI6IkxldmVsIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6Im93bmVyLm5hbWUiLCJsYWJlbCI6Ik9yZ2FuaXNlciIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJjb2FjaC5uYW1lIiwibGFiZWwiOiJDb2FjaCIsImlzSGlkZGVuIjpmYWxzZSwiaXNUb2dnbGVkIjp0cnVlLCJpc1RvZ2dsZWFibGUiOmZhbHNlLCJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiOm51bGx9LHsidHlwZSI6ImNvbHVtbiIsIm5hbWUiOiJzdGFydF90aW1lIiwibGFiZWwiOiJEYXRlICZhbXA7IFRpbWUiLCJpc0hpZGRlbiI6ZmFsc2UsImlzVG9nZ2xlZCI6dHJ1ZSwiaXNUb2dnbGVhYmxlIjpmYWxzZSwiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjpudWxsfSx7InR5cGUiOiJjb2x1bW4iLCJuYW1lIjoidG90YWxfcHJpY2UiLCJsYWJlbCI6IlByaWNlIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH0seyJ0eXBlIjoiY29sdW1uIiwibmFtZSI6InN0YXR1cyIsImxhYmVsIjoiU3RhdHVzIiwiaXNIaWRkZW4iOmZhbHNlLCJpc1RvZ2dsZWQiOnRydWUsImlzVG9nZ2xlYWJsZSI6ZmFsc2UsImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI6bnVsbH1dfSwiZmlsYW1lbnQiOltdfQ==', 1777074022);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'player',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `skill_level` tinyint UNSIGNED DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `preferred_sport` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'padel',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `is_active`, `skill_level`, `date_of_birth`, `preferred_sport`, `remember_token`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@padel.test', NULL, '2026-04-24 19:36:20', '$2y$12$6bINpaU2uxcoS6lp0dmKVesZj.I116I1vMuH9dXt3qK5PyvG5Ry.i', 'super_admin', 1, 4, '1999-10-31', 'padel', 'eOKC3FOL2K', NULL, '2026-04-24 19:36:20', '2026-04-24 19:39:01'),
(2, 'Cristina Huels', 'cnikolaus@example.net', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 5, '2006-02-19', 'squash', 'kbtMRA10Jb', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(3, 'Geovanni Sauer', 'helen73@example.org', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 5, '1998-05-10', 'tennis', 'GM9z3K9kwU', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(4, 'Gino Mertz', 'gschiller@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 4, '2005-01-24', 'padel', 'QTMj7snORW', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(5, 'Trever Lind', 'barton.blanche@example.org', '818.344.7146', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 5, '2002-08-25', 'squash', 'yHj82axudh', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(6, 'Theo Rodriguez Jr.', 'nikita.wintheiser@example.org', '1-820-523-7425', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 1, '2006-03-14', 'padel', 'nu5MTTobZJ', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(7, 'Dr. Shayna Murray V', 'timothy14@example.com', '(779) 514-5318', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 2, '1989-09-02', 'squash', '0eqwCG9cIL', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(8, 'Jovanny Wunsch', 'trystan91@example.net', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 1, '1977-05-27', 'tennis', 'jVNMOuCCx1', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(9, 'Carli Ziemann', 'robel.elyssa@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 2, '2007-05-16', 'tennis', '2VOq4Dh7Sg', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(10, 'Suzanne Smith IV', 'shanelle.kreiger@example.net', '+14425031695', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 1, '1978-02-23', 'tennis', 'uMk5UnWHHx', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(11, 'Katrina Feeney', 'delbert96@example.net', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'coach', 1, 1, '1985-11-28', 'padel', '1Y4Sfchcfb', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(12, 'Prof. Alva Hyatt V', 'kristian.cronin@example.com', '1-913-685-3705', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '2004-03-10', 'padel', 'LoCVvvmIU0', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(13, 'Lea Wilkinson PhD', 'linnie.hermiston@example.net', '+1 (234) 610-4335', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '2008-06-14', 'padel', 'cyIPO6hRpE', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(14, 'Prof. Lorena Botsford', 'kyler.mraz@example.net', '731-572-5770', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '2001-06-18', 'squash', 'mPjbtesLtg', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(15, 'Dr. Santiago Padberg', 'bobbie.lockman@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '2006-10-10', 'padel', 'AntDTnTlyE', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(16, 'Jillian Bogan', 'friedrich79@example.org', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 1, '1997-06-08', 'padel', '4RNQfsul7m', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(17, 'Araceli Kling', 'henri24@example.net', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '2006-06-21', 'padel', 'WKDopDtZ4P', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(18, 'Jessika Reichert', 'wilson22@example.com', '+1.248.931.6123', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '1997-10-27', 'padel', 'S11V2gwJx4', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(19, 'Lysanne Ullrich', 'wklocko@example.com', '484-466-3613', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '1998-07-28', 'tennis', 'R9dTmHt8Wv', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(20, 'Magnus Nicolas', 'sienna.heller@example.org', '+1 (920) 617-8636', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '2001-08-12', 'padel', 'tREGjkat57', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(21, 'Rose Nitzsche', 'elwin.kling@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 1, '2004-09-06', 'padel', 'h1IKHDKytH', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(22, 'Christy Sporer DDS', 'keyon.ward@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '1982-03-24', 'padel', 'JiKUXzS4bG', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(23, 'Dr. Fatima Reichel', 'sklocko@example.net', '(858) 526-4585', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '2000-02-22', 'squash', 'XkPvpSBFbY', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(24, 'Miss Shirley Purdy', 'robin44@example.org', '831-636-5246', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '1981-04-05', 'padel', '2ewk2HTSAN', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(25, 'Prof. Bill Bins Jr.', 'vjast@example.org', '201-472-7958', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '1991-05-02', 'padel', 'JMvOpfdGGH', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(26, 'George Trantow', 'oleta63@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '1983-02-06', 'padel', '5GoEKZtgur', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(27, 'Euna Johnson', 'soledad94@example.net', '1-970-747-0129', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 1, '1979-04-14', 'padel', 'EYHLd07kIz', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(28, 'Dallas Nienow', 'corine.renner@example.org', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '1998-08-25', 'padel', 'KA9Zj5OAuR', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(29, 'Lou Mills', 'lakin.vito@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '2007-09-13', 'padel', 'sOnfUM3hUJ', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(30, 'Francesca Stehr', 'heller.chad@example.com', '407.676.2145', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 1, '2001-06-11', 'padel', 'ucQSOpWmWS', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(31, 'Brooklyn Daniel PhD', 'russel89@example.net', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '1990-08-27', 'padel', 'AUp9k3bclh', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(32, 'Mauricio Stehr', 'giuseppe34@example.com', '1-458-758-3102', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '1993-07-08', 'padel', 'DrhUp94pST', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(33, 'Nikolas Schultz', 'little.vivian@example.com', '+1-231-532-2673', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '1982-03-10', 'padel', 'BtCEKgl74I', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(34, 'Leland Bruen', 'varmstrong@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '1982-03-06', 'padel', 'n4orlZVS4b', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(35, 'Colton Stamm', 'kemmer.gail@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '1992-12-05', 'padel', 'qVuCDysQRG', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(36, 'Russell Runte', 'mosciski.arvel@example.net', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '2003-09-19', 'padel', 'F7ByFPGoOj', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(37, 'Jamison Schaefer', 'hannah69@example.org', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '1983-02-15', 'squash', 'g1jVNcoxVF', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(38, 'Miss Thea Gleason II', 'dooley.keenan@example.net', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '1985-07-03', 'padel', 'HxSemRqZ7U', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(39, 'Candelario Strosin IV', 'yasmine.walsh@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 1, '2009-05-29', 'tennis', 'gZJ5dRszXl', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(40, 'Mrs. Cheyanne Volkman', 'bertrand21@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '2002-07-28', 'tennis', '3yrDcDO4Fa', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(41, 'Delia Swaniawski', 'weimann.gianni@example.com', '+14409517927', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '2004-11-07', 'padel', 'GX7P0qRrA1', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(42, 'Prof. Deron Kautzer Sr.', 'leatha00@example.org', '+1-580-475-8542', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '1998-10-06', 'squash', 'LL5LmWU5hI', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(43, 'Ruthie Dickinson II', 'demarco72@example.org', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '2006-03-13', 'padel', 'nDtDECRKyH', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(44, 'Prof. Pietro Koss', 'wyman.clara@example.net', '651-412-7663', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '2008-10-24', 'squash', 'ZWgdt1Y41r', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(45, 'Mr. Mackenzie Langworth DVM', 'willms.cassie@example.org', '1-703-769-8934', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '1995-10-17', 'tennis', 'HRNtSJ02DM', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(46, 'Kayla Ankunding', 'ullrich.jordi@example.com', '(775) 396-8981', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 1, '2001-08-27', 'squash', '9GBKM1xh4W', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(47, 'Jake Terry', 'nitzsche.isai@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '2000-02-23', 'padel', 'AzHW8FrnE3', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(48, 'Dillon Jaskolski III', 'gracie94@example.com', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '2000-11-13', 'squash', 'm5zCeh4nw2', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(49, 'Dr. Ardella Gleichner', 'braxton43@example.org', '843.497.6512', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '2010-03-29', 'tennis', 'du33mocKIG', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(50, 'Kennedi Wilkinson', 'celestino.mertz@example.net', '463.240.9221', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '2004-05-16', 'padel', 'cSZteuFdJY', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(51, 'Dr. Madison Bechtelar', 'jacobs.tre@example.org', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '1989-06-23', 'padel', '7Aihi9pvP1', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(52, 'Prof. Diamond Koepp II', 'harvey.erin@example.org', '+1-848-838-3537', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 1, '1977-06-21', 'padel', 'bXb9R1PVkf', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(53, 'Shanny Hermiston', 'aditya10@example.org', '256-229-8772', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '1992-12-28', 'squash', '28xOY91MSG', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(54, 'Dr. Lea Bins MD', 'ewald27@example.com', '1-303-742-8421', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '1993-01-31', 'squash', 'fmQZhvtzK2', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(55, 'Fleta McClure', 'skye.lemke@example.net', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 1, '1986-11-23', 'padel', 'bYgiJGPHaS', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(56, 'Arnulfo Harvey', 'verner.schmeler@example.net', '(772) 521-1893', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '1995-01-04', 'squash', 'SxM7o6YJg4', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(57, 'Gretchen Rutherford', 'genesis11@example.org', NULL, '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 4, '2003-10-07', 'padel', 'tzg4pEHeMY', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(58, 'Oral Runolfsson', 'jacobson.birdie@example.net', '+1 (786) 727-6524', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 2, '2008-03-29', 'tennis', 'CJHnGeuYDP', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(59, 'Prof. Tyrique Harris IV', 'tania46@example.net', '+1-352-571-4999', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '1997-01-06', 'tennis', 'setHla3Vky', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(60, 'Mrs. Roslyn Kiehn', 'qbreitenberg@example.net', '724-993-1230', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 5, '1987-08-19', 'padel', '5RtWmgl5sE', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20'),
(61, 'Raphael Turcotte', 'vida.gislason@example.net', '425-930-6576', '2026-04-24 19:36:20', '$2y$12$d3ZYke3SXvInhDIdI7/F8e3tomtHrgSQpyUdHurQkl4LE4sMMNSgO', 'player', 1, 3, '1985-11-08', 'tennis', 'BCrmOn9xso', NULL, '2026-04-24 19:36:20', '2026-04-24 19:36:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academy_sessions`
--
ALTER TABLE `academy_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `academy_sessions_coach_user_id_foreign` (`coach_user_id`),
  ADD KEY `academy_sessions_created_by_user_id_foreign` (`created_by_user_id`),
  ADD KEY `academy_sessions_club_id_start_time_index` (`club_id`,`start_time`),
  ADD KEY `academy_sessions_court_id_start_time_index` (`court_id`,`start_time`);

--
-- Indexes for table `academy_session_user`
--
ALTER TABLE `academy_session_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `academy_session_user_academy_session_id_user_id_unique` (`academy_session_id`,`user_id`),
  ADD KEY `academy_session_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_court_id_foreign` (`court_id`),
  ADD KEY `bookings_owner_user_id_foreign` (`owner_user_id`),
  ADD KEY `bookings_coach_user_id_foreign` (`coach_user_id`);

--
-- Indexes for table `booking_participants`
--
ALTER TABLE `booking_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_participants_booking_id_foreign` (`booking_id`),
  ADD KEY `booking_participants_user_id_foreign` (`user_id`);

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
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `club_users`
--
ALTER TABLE `club_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_users_club_id_foreign` (`club_id`),
  ADD KEY `club_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `courts`
--
ALTER TABLE `courts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courts_club_id_foreign` (`club_id`);

--
-- Indexes for table `court_slots`
--
ALTER TABLE `court_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `court_slots_court_id_foreign` (`court_id`),
  ADD KEY `court_slots_coach_user_id_foreign` (`coach_user_id`);

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
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `packages_club_id_foreign` (`club_id`);

--
-- Indexes for table `package_subscriptions`
--
ALTER TABLE `package_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_subscriptions_user_id_status_index` (`user_id`,`status`),
  ADD KEY `package_subscriptions_package_id_status_index` (`package_id`,`status`),
  ADD KEY `package_subscriptions_expires_at_index` (`expires_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_transactions_paymob_transaction_id_unique` (`paymob_transaction_id`),
  ADD KEY `payment_transactions_booking_id_foreign` (`booking_id`),
  ADD KEY `payment_transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_is_active_index` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academy_sessions`
--
ALTER TABLE `academy_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `academy_session_user`
--
ALTER TABLE `academy_session_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- AUTO_INCREMENT for table `booking_participants`
--
ALTER TABLE `booking_participants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `club_users`
--
ALTER TABLE `club_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `courts`
--
ALTER TABLE `courts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `court_slots`
--
ALTER TABLE `court_slots`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `package_subscriptions`
--
ALTER TABLE `package_subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academy_sessions`
--
ALTER TABLE `academy_sessions`
  ADD CONSTRAINT `academy_sessions_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `academy_sessions_coach_user_id_foreign` FOREIGN KEY (`coach_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `academy_sessions_court_id_foreign` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `academy_sessions_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `academy_session_user`
--
ALTER TABLE `academy_session_user`
  ADD CONSTRAINT `academy_session_user_academy_session_id_foreign` FOREIGN KEY (`academy_session_id`) REFERENCES `academy_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `academy_session_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_coach_user_id_foreign` FOREIGN KEY (`coach_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_court_id_foreign` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_participants`
--
ALTER TABLE `booking_participants`
  ADD CONSTRAINT `booking_participants_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_users`
--
ALTER TABLE `club_users`
  ADD CONSTRAINT `club_users_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courts`
--
ALTER TABLE `courts`
  ADD CONSTRAINT `courts_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `court_slots`
--
ALTER TABLE `court_slots`
  ADD CONSTRAINT `court_slots_coach_user_id_foreign` FOREIGN KEY (`coach_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `court_slots_court_id_foreign` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_subscriptions`
--
ALTER TABLE `package_subscriptions`
  ADD CONSTRAINT `package_subscriptions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
