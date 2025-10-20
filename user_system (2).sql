-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 20, 2025 at 06:43 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_members`
--

CREATE TABLE `community_members` (
  `id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `community_members`
--

INSERT INTO `community_members` (`id`, `community_id`, `user_id`, `joined_at`) VALUES
(9, 8, 1, '2025-10-17 09:00:58'),
(10, 8, 2, '2025-10-17 09:00:58'),
(11, 9, 1, '2025-10-17 09:00:58'),
(12, 10, 1, '2025-10-17 09:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `followee_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `followee_id`, `created_at`) VALUES
(4, 1, 3, '2025-10-07 13:34:17'),
(6, 1, 4, '2025-10-07 18:46:13');

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`id`, `user_id`, `joined_at`) VALUES
(10, 1, '2025-10-08 12:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `institutions`
--

CREATE TABLE `institutions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT 'img/default-logo.png',
  `followers` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `institutions`
--

INSERT INTO `institutions` (`id`, `name`, `logo`, `followers`) VALUES
(1, 'Middlesex University', '', 9000),
(2, 'Cornell University', '', 800),
(3, 'University of Cambridge', '', 20000),
(4, 'University of Oxford', '', 133500);

-- --------------------------------------------------------

--
-- Table structure for table `institution_details`
--

CREATE TABLE `institution_details` (
  `id` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `about_text` text DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `students` int(11) DEFAULT NULL,
  `faculty` int(11) DEFAULT NULL,
  `departments` int(11) DEFAULT NULL,
  `campuses` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `institution_details`
--

INSERT INTO `institution_details` (`id`, `NAME`, `location`, `about_text`, `mission`, `vision`, `email`, `phone`, `website`, `students`, `faculty`, `departments`, `campuses`, `updated_at`) VALUES
(1, 'University of Lagos (UNILAG)', 'Akoka, Yaba, Lagos State, Nigeria', 'oiuytrfdo1', 'yhy', 'yhy', 'info@unilag.edu.ng', '+2348034567890', 'http://www.unilag.edu.ng', 2, 10, 1, 1, '2025-10-20 04:42:51');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `image`, `is_read`, `created_at`) VALUES
(2, 1, 1, 'Welcome to UNILAG! Please review the new community guidelines.', NULL, 0, '2025-10-17 12:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE `polls` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `polls`
--

INSERT INTO `polls` (`id`, `question`, `created_by`, `created_at`) VALUES
(1, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:30'),
(2, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:32'),
(3, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:32'),
(4, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:33'),
(5, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:33'),
(6, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:33'),
(7, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:33'),
(8, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:37'),
(9, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:37'),
(10, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:38'),
(11, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:38'),
(12, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:36:38'),
(13, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:37:58'),
(14, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:37:58'),
(15, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:37:58'),
(16, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:37:59'),
(17, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:37:59'),
(18, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:37:59'),
(19, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:37:59'),
(20, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:17'),
(21, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:17'),
(22, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:17'),
(23, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:17'),
(24, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:18'),
(25, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:18'),
(26, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:18'),
(27, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:18'),
(28, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:38:18'),
(29, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:50:57'),
(30, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:50:58'),
(31, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:50:58'),
(32, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:50:58'),
(33, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:50:58'),
(34, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:50:58'),
(35, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:21'),
(36, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:22'),
(37, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:22'),
(38, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:22'),
(39, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:22'),
(40, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:32'),
(41, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:32'),
(42, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:32'),
(43, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:33'),
(44, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:33'),
(45, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:33'),
(46, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:40'),
(47, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:40'),
(48, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:40'),
(49, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:40'),
(50, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:41'),
(51, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:41'),
(52, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:41'),
(53, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:51:41'),
(54, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:52:20'),
(55, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:52:21'),
(56, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:52:21'),
(57, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:52:21'),
(58, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:52:21'),
(59, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:52:21'),
(60, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:53:30'),
(61, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:53:30'),
(62, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:53:31'),
(63, 'Which school offers the best education in nigeria', 1, '2025-10-07 18:53:31'),
(64, 'which dog do you like best in the school', 1, '2025-10-07 19:15:21'),
(65, 'best club in school', 1, '2025-10-07 19:24:08');

-- --------------------------------------------------------

--
-- Table structure for table `poll_options`
--

CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `votes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll_options`
--

INSERT INTO `poll_options` (`id`, `poll_id`, `option_text`, `votes`) VALUES
(1, 1, 'MiddleSex', 0),
(2, 1, 'BabCock', 0),
(3, 2, 'MiddleSex', 0),
(4, 2, 'BabCock', 0),
(5, 3, 'MiddleSex', 0),
(6, 3, 'BabCock', 0),
(7, 4, 'MiddleSex', 0),
(8, 4, 'BabCock', 0),
(9, 5, 'MiddleSex', 0),
(10, 5, 'BabCock', 0),
(11, 6, 'MiddleSex', 0),
(12, 6, 'BabCock', 0),
(13, 7, 'MiddleSex', 0),
(14, 7, 'BabCock', 0),
(15, 8, 'MiddleSex', 0),
(16, 8, 'BabCock', 0),
(17, 9, 'MiddleSex', 0),
(18, 9, 'BabCock', 0),
(19, 10, 'MiddleSex', 0),
(20, 10, 'BabCock', 0),
(21, 11, 'MiddleSex', 0),
(22, 11, 'BabCock', 0),
(23, 12, 'MiddleSex', 0),
(24, 12, 'BabCock', 0),
(25, 13, 'MiddleSex', 0),
(26, 13, 'BabCock', 0),
(27, 14, 'MiddleSex', 0),
(28, 14, 'BabCock', 0),
(29, 15, 'MiddleSex', 0),
(30, 15, 'BabCock', 0),
(31, 16, 'MiddleSex', 0),
(32, 16, 'BabCock', 0),
(33, 17, 'MiddleSex', 0),
(34, 17, 'BabCock', 0),
(35, 18, 'MiddleSex', 0),
(36, 18, 'BabCock', 0),
(37, 19, 'MiddleSex', 0),
(38, 19, 'BabCock', 0),
(39, 20, 'MiddleSex', 0),
(40, 20, 'babcock', 0),
(41, 21, 'MiddleSex', 0),
(42, 21, 'babcock', 0),
(43, 22, 'MiddleSex', 0),
(44, 22, 'babcock', 0),
(45, 23, 'MiddleSex', 0),
(46, 23, 'babcock', 0),
(47, 24, 'MiddleSex', 0),
(48, 24, 'babcock', 0),
(49, 25, 'MiddleSex', 0),
(50, 25, 'babcock', 0),
(51, 26, 'MiddleSex', 0),
(52, 26, 'babcock', 0),
(53, 27, 'MiddleSex', 0),
(54, 27, 'babcock', 0),
(55, 28, 'MiddleSex', 0),
(56, 28, 'babcock', 0),
(57, 29, 'MiddleSex', 0),
(58, 29, 'Babcock', 0),
(59, 30, 'MiddleSex', 0),
(60, 30, 'Babcock', 0),
(61, 31, 'MiddleSex', 0),
(62, 31, 'Babcock', 0),
(63, 32, 'MiddleSex', 0),
(64, 32, 'Babcock', 0),
(65, 33, 'MiddleSex', 0),
(66, 33, 'Babcock', 0),
(67, 34, 'MiddleSex', 0),
(68, 34, 'Babcock', 0),
(69, 35, 'MiddleSex', 0),
(70, 35, 'Aptech', 0),
(71, 36, 'MiddleSex', 0),
(72, 36, 'Aptech', 0),
(73, 37, 'MiddleSex', 0),
(74, 37, 'Aptech', 0),
(75, 38, 'MiddleSex', 0),
(76, 38, 'Aptech', 0),
(77, 39, 'MiddleSex', 0),
(78, 39, 'Aptech', 0),
(79, 40, 'MiddleSex', 0),
(80, 40, 'Aptech', 0),
(81, 40, 'Babcock', 0),
(82, 41, 'MiddleSex', 0),
(83, 41, 'Aptech', 0),
(84, 41, 'Babcock', 0),
(85, 42, 'MiddleSex', 0),
(86, 42, 'Aptech', 0),
(87, 42, 'Babcock', 0),
(88, 43, 'MiddleSex', 0),
(89, 43, 'Aptech', 0),
(90, 43, 'Babcock', 0),
(91, 44, 'MiddleSex', 0),
(92, 44, 'Aptech', 0),
(93, 44, 'Babcock', 0),
(94, 45, 'MiddleSex', 0),
(95, 45, 'Aptech', 0),
(96, 45, 'Babcock', 0),
(97, 46, 'MiddleSex', 0),
(98, 46, 'Aptech', 0),
(99, 46, 'Babcock', 0),
(100, 46, 'imsu', 0),
(101, 47, 'MiddleSex', 0),
(102, 47, 'Aptech', 0),
(103, 47, 'Babcock', 0),
(104, 47, 'imsu', 0),
(105, 48, 'MiddleSex', 0),
(106, 48, 'Aptech', 0),
(107, 48, 'Babcock', 0),
(108, 48, 'imsu', 0),
(109, 49, 'MiddleSex', 0),
(110, 49, 'Aptech', 0),
(111, 49, 'Babcock', 0),
(112, 49, 'imsu', 0),
(113, 50, 'MiddleSex', 0),
(114, 50, 'Aptech', 0),
(115, 50, 'Babcock', 0),
(116, 50, 'imsu', 0),
(117, 51, 'MiddleSex', 0),
(118, 51, 'Aptech', 0),
(119, 51, 'Babcock', 0),
(120, 51, 'imsu', 0),
(121, 52, 'MiddleSex', 0),
(122, 52, 'Aptech', 0),
(123, 52, 'Babcock', 0),
(124, 52, 'imsu', 0),
(125, 53, 'MiddleSex', 0),
(126, 53, 'Aptech', 0),
(127, 53, 'Babcock', 0),
(128, 53, 'imsu', 0),
(129, 54, 'MiddleSex', 0),
(130, 54, 'Aptech', 0),
(131, 54, 'Babcock', 0),
(132, 54, 'imsu', 0),
(133, 55, 'MiddleSex', 0),
(134, 55, 'Aptech', 0),
(135, 55, 'Babcock', 0),
(136, 55, 'imsu', 0),
(137, 56, 'MiddleSex', 0),
(138, 56, 'Aptech', 0),
(139, 56, 'Babcock', 0),
(140, 56, 'imsu', 0),
(141, 57, 'MiddleSex', 0),
(142, 57, 'Aptech', 0),
(143, 57, 'Babcock', 0),
(144, 57, 'imsu', 0),
(145, 58, 'MiddleSex', 0),
(146, 58, 'Aptech', 0),
(147, 58, 'Babcock', 0),
(148, 58, 'imsu', 0),
(149, 59, 'MiddleSex', 0),
(150, 59, 'Aptech', 0),
(151, 59, 'Babcock', 0),
(152, 59, 'imsu', 0),
(153, 60, 'MiddleSex', 0),
(154, 60, 'Aptech', 0),
(155, 60, 'Babcock', 0),
(156, 60, 'imsu', 0),
(157, 61, 'MiddleSex', 0),
(158, 61, 'Aptech', 0),
(159, 61, 'Babcock', 0),
(160, 61, 'imsu', 0),
(161, 62, 'MiddleSex', 0),
(162, 62, 'Aptech', 0),
(163, 62, 'Babcock', 0),
(164, 62, 'imsu', 0),
(165, 63, 'MiddleSex', 0),
(166, 63, 'Aptech', 0),
(167, 63, 'Babcock', 0),
(168, 63, 'imsu', 0),
(169, 64, 'BullDog', 0),
(170, 64, 'GermanShephard', 0),
(171, 65, 'chelsea', 0),
(172, 65, 'liverpool', 0);

-- --------------------------------------------------------

--
-- Table structure for table `poll_votes`
--

CREATE TABLE `poll_votes` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `community_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `poll_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shares` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `community_id`, `user_id`, `content`, `file_path`, `image`, `poll_id`, `created_at`, `shares`) VALUES
(22, 8, 1, 'Welcome to Biology 101!', NULL, NULL, NULL, '2025-10-17 09:00:58', 0),
(23, 9, 1, 'Check out my latest artwork!', NULL, NULL, NULL, '2025-10-17 09:00:58', 0),
(24, 10, 1, 'Coding challenge this weekend!', NULL, NULL, NULL, '2025-10-17 09:00:58', 0),
(25, 14, 1, 'No school this year!!', NULL, NULL, NULL, '2025-10-17 23:28:38', 0),
(26, 14, 1, 'School na scam but education no be scam ohh', NULL, NULL, NULL, '2025-10-18 00:05:54', 0),
(27, 14, 1, 'In all you do, no forget God ohh!', NULL, NULL, NULL, '2025-10-18 00:20:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `stories`
--

CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `type` enum('image','video') NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `media_type` enum('image','video') NOT NULL DEFAULT 'image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stories`
--

INSERT INTO `stories` (`id`, `user_id`, `media_url`, `file_path`, `type`, `caption`, `created_at`, `media_type`) VALUES
(1, 1, '', 'uploads/stories/1760010783_a14b76b27486.jpg', 'image', 'I loe', '2025-10-09 12:53:03', 'image'),
(2, 1, 'uploads/stories/1760013809_dogs.webp', '', 'image', 'Dog hand', '2025-10-09 13:43:29', 'image');

-- --------------------------------------------------------

--
-- Table structure for table `student_communities`
--

CREATE TABLE `student_communities` (
  `id` int(11) NOT NULL,
  `institution_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(10) DEFAULT '?',
  `cover_color` varchar(20) DEFAULT '#f0e6ff',
  `logo_color` varchar(20) DEFAULT '#793DDC',
  `logo_icon` varchar(50) DEFAULT 'fas fa-users',
  `members_count` int(11) DEFAULT 0,
  `posts_count` int(11) DEFAULT 0,
  `joined` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_communities`
--

INSERT INTO `student_communities` (`id`, `institution_id`, `name`, `description`, `icon`, `cover_color`, `logo_color`, `logo_icon`, `members_count`, `posts_count`, `joined`, `created_at`, `status`) VALUES
(8, 1, 'Biology 103', 'Introduction to biological sciences for first-year students.!!!!!!!!!Vivian!!!!!!!', '🧪', '#f0e6ff', '#793DDC', 'fas fa-users', 2, 1, 0, '2025-10-17 08:38:49', '1'),
(9, 1, 'Art & Design Studio!!!', 'Creative space for art and design students to share work.!!!!!!', '🎨', '#f0e6ff', '#793DDC', 'fas fa-users', 1, 1, 0, '2025-10-17 08:38:49', '1'),
(10, 1, 'CS Club', 'Computer Science student organization focused on coding.', '💻', '#f0e6ff', '#793DDC', 'fas fa-users', 1, 1, 0, '2025-10-17 08:38:49', '0'),
(13, 1, 'CS Club 101', 'Computer Science student organization focused on codinggggg', '💻', '#f0e6ff', '#793DDC', 'fas fa-users', 0, 0, 0, '2025-10-17 08:40:16', '1'),
(14, 1, 'SOP', 'IUYTDRDFi', '📚', '#f0e6ff', '#793DDC', 'fas fa-users', 0, 0, 0, '2025-10-18 00:20:56', '1');

-- --------------------------------------------------------

--
-- Table structure for table `student_institutions`
--

CREATE TABLE `student_institutions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `logo_color` varchar(20) DEFAULT NULL,
  `logo_icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_institutions`
--

INSERT INTO `student_institutions` (`id`, `student_id`, `name`, `role`, `is_primary`, `logo_color`, `logo_icon`, `created_at`) VALUES
(1, 1, 'BABCOCK UNIVERSITY', 'Linguistics Student, 2020 - Present', 1, '#793DDC', 'fas fa-graduation-cap', '2025-10-07 14:52:43'),
(2, 1, 'Lagos State High School', 'Alumnus, Graduated 2019', 0, '#ff9800', 'fas fa-school', '2025-10-07 14:52:43'),
(3, 1, 'Future Forward Academy', 'Data Science Course, 2023', 0, '#4caf50', 'fas fa-laptop-code', '2025-10-07 14:52:43'),
(5, 50, 'University of Lagos', 'Professor', 1, '#793DDC', 'fas fa-graduation-cap', '2025-10-17 10:51:56');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(11) NOT NULL,
  `topic` varchar(255) DEFAULT NULL,
  `posts_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `topic`, `posts_count`) VALUES
(1, 'DataScience', 2800),
(2, 'ExamPrep', 1300),
(3, 'ArtSchool', 12600);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL DEFAULT '',
  `birthday` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `faculty` varchar(100) DEFAULT NULL,
  `institution` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `matric_no` varchar(50) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default-avatar.png',
  `bio` text DEFAULT NULL,
  `followers` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `profile_pic`, `NAME`, `email`, `phone`, `birthday`, `gender`, `address`, `password`, `created_at`, `faculty`, `institution`, `department`, `role`, `matric_no`, `interests`, `avatar`, `bio`, `followers`) VALUES
(1, '', 'nobleman', 'sneh', 'uploads/profile_photos/1759867696_Lamborghini_Urus.jpg', 'Noble', 'Noble20@gmail.com', '814 245 2801', '0000-00-00', 'Male', 'nigeria', '$2y$10$Sr2Q1QilaKxVdSvFgECizOxUkxGjI44dMOp8ZMxXCylyg/vn8/8PS', '2025-10-06 17:25:02', NULL, NULL, NULL, NULL, NULL, 'i\'m a data scientist who study at Aptech', 'default-avatar.png', NULL, 0),
(2, 'student1', NULL, NULL, NULL, '', 'student1@example.com', '', NULL, NULL, NULL, '$2y$10$6NOKhXq6lX3O3qZ2uO.4rO0X9nXqX2X3O3qZ2uO.4rO0X9nXqX2X3', '2025-10-17 08:29:21', NULL, NULL, NULL, 'student', NULL, NULL, 'default-avatar.png', NULL, 0),
(3, '', NULL, NULL, NULL, 'prosper', 'Prosperibe834@gmail.com', '987465625', NULL, NULL, NULL, '$2y$10$oneg3gbVEae7LfDj3I85k.fBcV6xUsz7kfL4TbH1rbRfB.jxFw6c.', '2025-10-07 13:32:36', NULL, NULL, NULL, NULL, NULL, NULL, 'default-avatar.png', NULL, 0),
(4, '', '', '', NULL, 'valentinne', 'valentine10@gmail.com', '812 236 2889', '2007-12-09', 'Male', '', '$2y$10$ulttvDjH2FzUIz.JrtwtxusJ.7mlf5fO3PIchXMioRZehvaNDpnIy', '2025-10-07 13:34:06', NULL, NULL, NULL, NULL, NULL, NULL, 'default-avatar.png', NULL, 0),
(8, '', NULL, NULL, NULL, 'Alice Smith', 'alice@example.com', '', NULL, NULL, NULL, '', '2025-10-09 13:28:48', NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.png', 'Computer Science Student', 0),
(9, '', NULL, NULL, NULL, 'Bob Johnson', 'bob@example.com', '', NULL, NULL, NULL, '', '2025-10-09 13:28:48', NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.png', 'Engineering Student', 0),
(10, '', NULL, NULL, NULL, 'Charlie Davis', 'charlie@example.com', '', NULL, NULL, NULL, '', '2025-10-09 13:28:48', NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.png', 'Psychology Student', 0),
(11, '', NULL, NULL, NULL, 'David Chimeremeze', 'freddavidc@gmail.com', '09137229170', NULL, NULL, NULL, '$2y$10$kDC9kQAF0eFdnP8dv2y8Rez4ypKwJ3dWF1zBgDdeBeun7bNwbU6QW', '2025-10-17 06:32:24', '', NULL, NULL, 'admin', NULL, NULL, 'default-avatar.png', NULL, 0),
(50, 'Vivky', NULL, NULL, NULL, 'Vivian', 'vicky@gmail.com', '', NULL, NULL, NULL, '$2y$10$E.SHhPgVxDE2zvQPWd0asOZVIz3fnImOvZ4ywCjoTLiAyAm9lvPv2', '2025-10-17 10:51:56', NULL, 'University of Lagos', 'Software Engineering', 'Professor', NULL, NULL, 'default-avatar.png', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_comments_created` (`created_at`);

--
-- Indexes for table `community_members`
--
ALTER TABLE `community_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member` (`community_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`followee_id`),
  ADD KEY `followee_id` (`followee_id`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `friend_id` (`friend_id`);

--
-- Indexes for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `institutions`
--
ALTER TABLE `institutions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `institution_details`
--
ALTER TABLE `institution_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poll_options`
--
ALTER TABLE `poll_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poll_votes`
--
ALTER TABLE `poll_votes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_posts_created` (`created_at`),
  ADD KEY `community_id` (`community_id`);

--
-- Indexes for table `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `student_communities`
--
ALTER TABLE `student_communities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `institution_id` (`institution_id`);

--
-- Indexes for table `student_institutions`
--
ALTER TABLE `student_institutions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `community_members`
--
ALTER TABLE `community_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `institutions`
--
ALTER TABLE `institutions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `institution_details`
--
ALTER TABLE `institution_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `polls`
--
ALTER TABLE `polls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `poll_options`
--
ALTER TABLE `poll_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `poll_votes`
--
ALTER TABLE `poll_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_communities`
--
ALTER TABLE `student_communities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `student_institutions`
--
ALTER TABLE `student_institutions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`followee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stories`
--
ALTER TABLE `stories`
  ADD CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_communities`
--
ALTER TABLE `student_communities`
  ADD CONSTRAINT `student_communities_ibfk_2` FOREIGN KEY (`institution_id`) REFERENCES `institution_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_communities_ibfk_3` FOREIGN KEY (`institution_id`) REFERENCES `institution_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_institutions`
--
ALTER TABLE `student_institutions`
  ADD CONSTRAINT `student_institutions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
