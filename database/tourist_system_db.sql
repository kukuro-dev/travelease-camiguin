-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2026 at 08:20 AM
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
-- Database: `tourist_system_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` varchar(20) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `fullname`, `email`, `contact_number`, `username`, `password`) VALUES
('ADID001', 'admin', 'admin@gmail.com', '09533591578', 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `tourist_id` varchar(20) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `form_type` varchar(50) NOT NULL,
  `place_staying` varchar(255) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_date` datetime NOT NULL DEFAULT current_timestamp(),
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `requested_count` int(11) NOT NULL DEFAULT 1,
  `guests` int(11) DEFAULT 0,
  `units` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `date_created`) VALUES
(1, 'Cottage', 'Cottages for short-term rental in Camiguin', '2025-09-06 02:50:18'),
(2, 'Resort', 'Resorts available for accommodation', '2025-09-06 02:50:18'),
(3, 'Snorkeling', 'Snorkeling activities and equipment rental', '2025-09-06 02:50:18'),
(4, 'Rent Bike', 'Bicycles available for rent', '2025-09-06 02:50:18'),
(5, 'Motorbike', 'Motorbikes available for rent', '2025-09-06 02:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` varchar(50) NOT NULL,
  `receiver_id` varchar(50) NOT NULL,
  `rental_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `rental_id`, `message`, `is_read`, `date_created`) VALUES
(1, 'USR10000', 'USR10001', NULL, 'Your booking for \'SAJ Motor Rental\' has been approved!', 0, '2025-09-16 09:47:45'),
(2, 'USR10001', 'USR10000', NULL, 'Hi', 1, '2025-09-16 09:49:51'),
(4, 'USR10001', 'USR10000', 4, 'hi', 1, '2025-09-16 09:53:36'),
(5, 'USR10000', 'USR10001', 4, 'Your booking for \'SAJ Motor Rental\' has been approved!', 1, '2025-09-16 09:57:02'),
(6, 'USR10000', 'USR10001', 4, 'you can get your motor in 10 mins', 1, '2025-09-16 09:58:54'),
(7, 'USR10001', 'USR10000', 4, 'thank you Sir', 1, '2025-09-16 09:59:36'),
(8, 'USR10000', 'USR10001', 4, 'welcome', 1, '2025-09-16 10:00:38'),
(9, 'USR10000', 'USR10001', 1, 'Your booking for \'Ellianas Homestay\' has been approved!', 1, '2025-09-16 10:13:25'),
(12, 'USR10000', 'USR10001', 1, 'How many people?', 1, '2025-09-16 10:16:53'),
(14, 'USR10001', 'USR10000', 1, 'yes?', 1, '2025-09-16 11:28:10'),
(15, 'USR10000', 'USR10001', 1, 'hi', 1, '2025-09-16 11:29:07'),
(16, 'USR10000', 'USR10001', 1, 'thank you Sir', 1, '2025-09-16 11:30:06'),
(17, 'USR10000', 'USR10001', 4, 'welcome', 1, '2025-09-16 11:30:09'),
(18, 'USR10002', 'USR10000', 4, 'Hi im taking the color gray motor click 160', 1, '2025-09-16 12:34:32'),
(19, 'USR10000', 'USR10002', 4, 'Your booking for \'SAJ Motor Rental\' has been approved!', 1, '2025-09-16 12:35:27'),
(20, 'USR10000', 'USR10002', 4, 'wait for 30 minutes heading in your way', 1, '2025-09-16 12:35:54'),
(21, 'USR10000', 'USR10002', 1, 'Your booking for \'Ellianas Homestay\' has been approved!', 1, '2025-09-24 11:47:25'),
(22, 'USR10000', 'USR10001', 4, 'rent now!', 1, '2025-09-25 00:30:29'),
(23, 'USR10001', 'USR10000', 4, 'way available oi', 1, '2025-09-25 00:31:27'),
(24, 'USR10000', 'USR10001', 4, 'diay', 1, '2025-10-12 07:35:03'),
(25, 'USR10000', 'USR10005', 4, 'Your booking for \'SAJ Motor Rental\' has been approved!', 1, '2025-12-16 08:07:18'),
(26, 'USR10005', 'USR10000', 4, 'hi', 0, '2025-12-16 08:20:28'),
(27, 'USR10000', 'USR10005', 7, 'Your booking for \'Island Dive\' has been approved!', 0, '2025-12-16 08:23:57'),
(28, 'USR10002', 'USR10000', 1, 'yes?', 1, '2026-01-28 02:04:18'),
(29, 'USR10002', 'USR10000', 1, 'you can get your motor in 10 mins', 1, '2026-01-28 02:04:22'),
(30, 'USR10006', 'USR10000', 8, 'Hi! I’m visiting the area and I’d like to rent a bike.', 1, '2026-03-30 07:04:55'),
(31, 'USR10000', 'USR10006', 8, 'Hello! Welcome 😊 Sure, we have several bikes available. What type are you looking for?', 1, '2026-03-30 07:06:39'),
(32, 'USR10006', 'USR10000', 8, 'Just a simple bike for island touring. Do you have something comfortable for long rides?', 0, '2026-03-30 07:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `tourist_id` varchar(20) NOT NULL,
  `provider_id` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('Paid','Not Paid') DEFAULT 'Not Paid',
  `date_created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `provider_id` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `location_address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `availability` enum('available','unavailable') DEFAULT 'available',
  `profile_image` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL DEFAULT 0,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `guests` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `provider_id`, `category_id`, `title`, `description`, `price`, `location_address`, `city`, `province`, `country`, `availability`, `profile_image`, `date_created`, `quantity`, `capacity`, `guests`) VALUES
(1, 'USR10000', 1, 'Ellianas Homestay', 'Escape to the serene beauty of Camiguin and experience true comfort at Ellianas Homestay. Nestled in a peaceful neighborhood in Yumbing, Mambajao, our homestay offers a perfect blend of modern convenience and homey charm. Ideal for families, friends, or solo travelers, Ellianas Homestay provides a relaxing environment where you can unwind after a day of exploring the island’s pristine beaches, waterfalls, and natural wonders.', 6000.00, 'Yumbing', 'Mambajao', 'Camiguin', 'Philippines', 'available', '../uploads/rentals/1757132848_elliana3.jpg', '2025-09-06 03:47:40', 0, 2, 6),
(4, 'USR10000', 5, 'SAJ Motor Rental', 'Easy to get, Easy to ride', 400.00, 'Purok 2, Yumbing', 'Mambajao', 'Camiguin', 'Philippines', 'available', '../uploads/rentals/68bbd6909768a_click1.jpg', '2025-09-06 06:37:04', 20, 0, 0),
(5, 'USR10000', 4, 'BNV Bike Rental', 'A mountain bike is a bicycle designed for off-road cycling on varied terrain like dirt trails, rocks, and unpaved paths. They are built with features like stronger frames, wide tires with deep treads, and powerful brakes to handle demanding conditions.', 300.00, 'Kuguita', 'Mambajao', 'Camiguin', 'Philippines', 'available', '../uploads/rentals/68d3ec3ae79c5_images (13).jpg', '2025-09-24 13:03:54', 16, 0, 0),
(6, 'USR10000', 2, 'Paras Beach Resort', 'Paras Beach Resort is a family-friendly, 3-star resort located in Mambajao, Camiguin, Philippines. Known for its excellent location right across from the famous White Island sandbar, it offers guests easy access to the scenic attraction.', 6400.00, 'Yumbing', 'Mambajao', 'Camiguin', 'Philippines', 'available', '../uploads/rentals/1758719265_Paras-Beach-Resort.jpg', '2025-09-24 13:07:18', 0, 80, 200),
(7, 'USR10000', 3, 'Island Dive', 'Snorkeling is a popular recreational activity that involves swimming on the surface of the water while breathing through a tube, called a snorkel, attached to a mask that covers your eyes', 300.00, 'Yumbing', 'Mambajao', 'Camiguin', 'Philippines', 'available', '../uploads/rentals/68d3f111cecd0_haveseen-snorkel-equipment-on-a-tropical-beach_u-l-q1056p60.jpg', '2025-09-24 13:24:33', 20, 0, 0),
(8, 'USR10000', 4, 'YTZ Bike Rental', 'Bike for rent', 500.00, 'Yumbing', 'Mambajao', 'Camiguin', 'Philippines', 'available', '../uploads/rentals/68da60f1f057e_images (14).jpg', '2025-09-29 10:35:29', 16, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rental_images`
--

CREATE TABLE `rental_images` (
  `id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_images`
--

INSERT INTO `rental_images` (`id`, `rental_id`, `image_path`, `date_created`) VALUES
(18, 1, '../uploads/rentals/1757132839_elliana3.jpg', '2025-09-06 04:27:19'),
(19, 1, '../uploads/rentals/1757132857_elliana5.jpg', '2025-09-06 04:27:37'),
(20, 1, '../uploads/rentals/1757132865_elliana2.jpg', '2025-09-06 04:27:45'),
(21, 1, '../uploads/rentals/1757132874_elliana1.jpg', '2025-09-06 04:27:54'),
(24, 1, '../uploads/rentals/1757132903_elliana4.jpg', '2025-09-06 04:28:23'),
(27, 4, '../uploads/rentals/68bbd6909768a_click1.jpg', '2025-09-06 06:37:04'),
(28, 4, '../uploads/rentals/1757140688_clcik2.jpg', '2025-09-06 06:38:08'),
(29, 4, '../uploads/rentals/1757140700_click3.jpg', '2025-09-06 06:38:20'),
(30, 4, '../uploads/rentals/1757140708_click4.jpg', '2025-09-06 06:38:28'),
(31, 5, '../uploads/rentals/68d3ec3ae79c5_images (13).jpg', '2025-09-24 13:03:54'),
(32, 5, '../uploads/rentals/1758719045_2022 canyon spectral 125 rear 3q.jpg', '2025-09-24 13:04:05'),
(33, 5, '../uploads/rentals/1758719051_4A5qKHefPrzqEvJSjZBunF.jpg', '2025-09-24 13:04:11'),
(34, 5, '../uploads/rentals/1758719058_images (14).jpg', '2025-09-24 13:04:18'),
(35, 5, '../uploads/rentals/1758719062_Orbea_Occam_2020.jpg', '2025-09-24 13:04:22'),
(36, 6, '../uploads/rentals/1758719274_643101027.jpg', '2025-09-24 13:07:54'),
(37, 6, '../uploads/rentals/1758719283_643101043.jpg', '2025-09-24 13:08:03'),
(38, 6, '../uploads/rentals/1758719289_a89b00a144056ee0a1ad72a497f71de7.jpg', '2025-09-24 13:08:09'),
(40, 6, '../uploads/rentals/1758719304_Paras-Beach-Resort-Camiguin-Aerial-View-Copyright-to-Project-LUPAD-23.jpg', '2025-09-24 13:08:24'),
(41, 6, '../uploads/rentals/1758719316_mobile_paras-beach-resort-new-banner-i.jpg', '2025-09-24 13:08:36'),
(42, 7, '../uploads/rentals/68d3f111cecd0_haveseen-snorkel-equipment-on-a-tropical-beach_u-l-q1056p60.jpg', '2025-09-24 13:24:33'),
(43, 7, '../uploads/rentals/1758720289_water-actvites.jpg', '2025-09-24 13:24:49'),
(44, 7, '../uploads/rentals/1758720293_2000x2000-0-70-0108d6ef0c0ed208dc8b4f8cf67d9ffb.jpg', '2025-09-24 13:24:53'),
(45, 7, '../uploads/rentals/1758720301_oahu_snorkel_rental.jpg', '2025-09-24 13:25:01'),
(46, 7, '../uploads/rentals/1758720310_a-diving-mask-and-snorkel-on-a-rock-near-the-sea-c_1200x1200.jpg', '2025-09-24 13:25:10'),
(47, 8, '../uploads/rentals/68da60f1f057e_images (14).jpg', '2025-09-29 10:35:29'),
(48, 8, '../uploads/rentals/1759142316_2022 canyon spectral 125 rear 3q.jpg', '2025-09-29 10:38:36'),
(49, 8, '../uploads/rentals/1759142324_2022 canyon spectral 125 rear 3q.jpg', '2025-09-29 10:38:44'),
(50, 8, '../uploads/rentals/1759142333_images (13).jpg', '2025-09-29 10:38:53'),
(51, 8, '../uploads/rentals/1759142345_4A5qKHefPrzqEvJSjZBunF.jpg', '2025-09-29 10:39:05');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `tourist_id` varchar(20) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spot_pictures`
--

CREATE TABLE `spot_pictures` (
  `picture_id` varchar(20) NOT NULL,
  `spot_id` varchar(20) DEFAULT NULL,
  `picture_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spot_pictures`
--

INSERT INTO `spot_pictures` (`picture_id`, `spot_id`, `picture_path`, `uploaded_at`) VALUES
('TSPID000001', 'TSID00001', '../uploads/tourist_spots/68d3b546b40b2_white-island-is-an-uninhabited.jpg', '2025-09-24 09:09:26'),
('TSPID000002', 'TSID00001', '../uploads/tourist_spots/1758705395_white-island-camiguin-13.jpg', '2025-09-24 09:16:35'),
('TSPID000003', 'TSID00001', '../uploads/tourist_spots/1758705400_white-island-camiguin (1).jpg', '2025-09-24 09:16:40'),
('TSPID000004', 'TSID00001', '../uploads/tourist_spots/1758705407_Captivating-White-Island-in-Camiguin-Copyright-to-Project-LUPAD-4.jpg', '2025-09-24 09:16:47'),
('TSPID000005', 'TSID00001', '../uploads/tourist_spots/1758705443_white-island-camiguin-sandbar.jpg', '2025-09-24 09:17:23'),
('TSPID000006', 'TSID00002', '../uploads/tourist_spots/68d3b7d1d6b3a_Camiguin-Hot-Springs.jpg', '2025-09-24 09:20:17'),
('TSPID000007', 'TSID00002', '../uploads/tourist_spots/1758705628_ardent-2.jpg', '2025-09-24 09:20:28'),
('TSPID000008', 'TSID00002', '../uploads/tourist_spots/1758705634_images (2).jpg', '2025-09-24 09:20:34'),
('TSPID000009', 'TSID00002', '../uploads/tourist_spots/1758705639_images (1).jpg', '2025-09-24 09:20:39'),
('TSPID000010', 'TSID00002', '../uploads/tourist_spots/1758705645_ardent-hot-spring-camiguin-2.jpg', '2025-09-24 09:20:45'),
('TSPID000011', 'TSID00002', '../uploads/tourist_spots/1758705652_images.jpg', '2025-09-24 09:20:52'),
('TSPID000012', 'TSID00003', '../uploads/tourist_spots/68d3b8d822300_maxresdefault.jpg', '2025-09-24 09:24:40'),
('TSPID000013', 'TSID00003', '../uploads/tourist_spots/1758705895_sto-nino-cold-spring (1).jpg', '2025-09-24 09:24:55'),
('TSPID000014', 'TSID00003', '../uploads/tourist_spots/1758705901_sto-nino-7-1024x576 (1).jpg', '2025-09-24 09:25:01'),
('TSPID000016', 'TSID00003', '../uploads/tourist_spots/1758705925_sto-nino-cold-spring.jpg', '2025-09-24 09:25:25'),
('TSPID000018', 'TSID00003', '../uploads/tourist_spots/1758706203_sto-nino-6.jpg', '2025-09-24 09:30:03'),
('TSPID000019', 'TSID00004', '../uploads/tourist_spots/68d3e471b87e8_Katibawasan-Falls.jpg', '2025-09-24 12:30:41'),
('TSPID000020', 'TSID00004', '../uploads/tourist_spots/1758717058_images (3).jpg', '2025-09-24 12:30:58'),
('TSPID000021', 'TSID00004', '../uploads/tourist_spots/1758717065_Katibawasan-7-1024x576.jpg', '2025-09-24 12:31:05'),
('TSPID000022', 'TSID00004', '../uploads/tourist_spots/1758717070_caption (1).jpg', '2025-09-24 12:31:10'),
('TSPID000023', 'TSID00004', '../uploads/tourist_spots/1758717075_images (4).jpg', '2025-09-24 12:31:15'),
('TSPID000024', 'TSID00005', '../uploads/tourist_spots/68d3e573ac5c1_maxresdefault (1).jpg', '2025-09-24 12:34:59'),
('TSPID000025', 'TSID00005', '../uploads/tourist_spots/1758717317_mantigue-island-camiguin-aerial.jpg', '2025-09-24 12:35:17'),
('TSPID000026', 'TSID00005', '../uploads/tourist_spots/1758717321_images (5).jpg', '2025-09-24 12:35:21'),
('TSPID000027', 'TSID00005', '../uploads/tourist_spots/1758717328_Mantigue-Island-Camiguin-1.jpg', '2025-09-24 12:35:28'),
('TSPID000028', 'TSID00005', '../uploads/tourist_spots/1758717333_14150289510_a840bee5ee_o.jpg', '2025-09-24 12:35:33'),
('TSPID000029', 'TSID00005', '../uploads/tourist_spots/1758717338_mantigue-island-camiguin-4.jpg', '2025-09-24 12:35:38'),
('TSPID000030', 'TSID00006', '../uploads/tourist_spots/68d3e78c99316_images (6).jpg', '2025-09-24 12:43:56'),
('TSPID000031', 'TSID00006', '../uploads/tourist_spots/1758717903_65245395_354201205291342_3217573353934028800_o-1.jpg', '2025-09-24 12:45:03'),
('TSPID000032', 'TSID00006', '../uploads/tourist_spots/1758717908_images (8).jpg', '2025-09-24 12:45:08'),
('TSPID000033', 'TSID00006', '../uploads/tourist_spots/1758717913_images (7).jpg', '2025-09-24 12:45:13'),
('TSPID000034', 'TSID00006', '../uploads/tourist_spots/1758717917_mountain-in-camiguin.jpg', '2025-09-24 12:45:17'),
('TSPID000035', 'TSID00006', '../uploads/tourist_spots/1758717922_images (9).jpg', '2025-09-24 12:45:22'),
('TSPID000036', 'TSID00007', '../uploads/tourist_spots/68d3e95d716b7_sddefault.jpg', '2025-09-24 12:51:41'),
('TSPID000037', 'TSID00007', '../uploads/tourist_spots/1758718309_16589045295_f7b50247de_b.jpg', '2025-09-24 12:51:49'),
('TSPID000038', 'TSID00007', '../uploads/tourist_spots/1758718314_images (11).jpg', '2025-09-24 12:51:54'),
('TSPID000039', 'TSID00007', '../uploads/tourist_spots/1758718318_J & A Fishpen and Taguines Lagoon.JPG', '2025-09-24 12:51:58'),
('TSPID000040', 'TSID00007', '../uploads/tourist_spots/1758718323_images (10).jpg', '2025-09-24 12:52:03'),
('TSPID000041', 'TSID00007', '../uploads/tourist_spots/1758718327_seaside.jpg', '2025-09-24 12:52:07'),
('TSPID000042', 'TSID00008', '../uploads/tourist_spots/68d3ead113fd2_maxresdefault (2).jpg', '2025-09-24 12:57:53'),
('TSPID000043', 'TSID00008', '../uploads/tourist_spots/1758718682_images (12).jpg', '2025-09-24 12:58:02'),
('TSPID000044', 'TSID00008', '../uploads/tourist_spots/1758718686_katibawasan-falls-camiguin-aerial-2-640x427.jpg', '2025-09-24 12:58:06'),
('TSPID000045', 'TSID00008', '../uploads/tourist_spots/1758718691_tuasan-falls-8.jpg', '2025-09-24 12:58:11'),
('TSPID000046', 'TSID00008', '../uploads/tourist_spots/1758718696_78728dkrir.jpg', '2025-09-24 12:58:16');

-- --------------------------------------------------------

--
-- Table structure for table `spot_reviews`
--

CREATE TABLE `spot_reviews` (
  `id` int(11) NOT NULL,
  `spot_id` varchar(20) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `tourist_id` varchar(50) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spot_reviews`
--

INSERT INTO `spot_reviews` (`id`, `spot_id`, `booking_id`, `tourist_id`, `rating`, `comment`, `date_created`) VALUES
(1, 'TSID00001', NULL, 'USR10001', 4, 'enjoying in the white sand', '2025-10-11 12:44:29'),
(2, 'TSID00007', NULL, 'USR10001', 4, 'inting is gay', '2025-10-13 09:39:24');

-- --------------------------------------------------------

--
-- Table structure for table `tourist_spot`
--

CREATE TABLE `tourist_spot` (
  `spot_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `maintenance_fee` varchar(255) NOT NULL,
  `admin_id` varchar(20) DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT '',
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tourist_spot`
--

INSERT INTO `tourist_spot` (`spot_id`, `name`, `barangay`, `municipality`, `description`, `maintenance_fee`, `admin_id`, `category`, `date_created`) VALUES
('TSID00001', 'White Island Beach', 'Yumbing', 'Mambajao', 'Camiguin\'s White Island is a must-visit destination for any traveler seeking the best white sand beaches in the Philippines. Along with its powdery shore, the island is known for its tendency to change shape depending on the tide.', '700.00 per 4 person', 'ADID001', 'Beach', '2025-10-11 11:28:07'),
('TSID00002', 'Ardent Hot Spring', 'Tagdo', 'Mambajao', 'Located in Brgy. Esperanza Tagdo, Mambajao, Camiguin, is a popular tourist destination featuring natural, geothermally heated springs from Mt. Hibok-Hibok that cascade into man-made rock pools. Open daily from 6 AM to 10 PM with a ₱50 entrance fee, it\'s a relaxing spot for visitors to enjoy warm waters, picnic with food available from stalls, and purchase souvenirs.', '50.00 per head', 'ADID001', 'Spring', '2025-10-11 11:28:07'),
('TSID00003', 'Santo Niño Cold Spring', 'Mainit', 'Catarman', 'Sto. Niño Cold Spring is a popular natural attraction in Catarman, Camiguin, known for its huge, crystal-clear pool fed by icy, spring water from Mount Mambajao. It\'s a refreshing escape from the island\'s tropical heat and is suitable for families and groups.', '75.00 per head', 'ADID001', 'Spring', '2025-10-11 11:28:07'),
('TSID00004', 'Katibawasan Falls', 'Pandan', 'Mambajao', 'Katibawasan Falls is the tallest of the Camiguin Island waterfalls. In fact, it is one of the tallest single-drop waterfalls in the Philippines. The water effortlessly flows from the nearby Mount Timpoong, before plunging 250 ft into a shallow basin of water', '75 per person', 'ADID001', 'Falls', '2025-10-11 11:28:07'),
('TSID00005', 'Mantigue Island', 'San Roque', 'Mahinog', 'Mantigue Island is a hidden gem located off the coast of Camiguin, known for its stunning white sand beaches, crystal-clear waters, and vibrant marine life. A haven for divers and snorkelers, the island offers a rich underwater ecosystem, with colorful coral gardens and diverse fish species', '560 for 6 people', 'ADID001', 'Island', '2025-10-11 11:28:07'),
('TSID00006', 'Mount Hibok-Hibok', 'Yumbing', 'Mambajao', 'Hibok-Hibok, which is an active volcano located in the northwest area of the island. It is one of the important Key Biodiversity Areas of the Philippines. It is rich in species diversity, and level of endemism, as this Heritage Park is the habitat of Camiguin\'s endemic birds and trees. Mts.', 'guide fee of PHP 1,200 per group (up to 3 people) and a daily environmental fee of PHP 200 per person, totaling PHP 1,400 for a single person or small group.', 'ADID001', 'Mountain', '2025-10-11 11:28:07'),
('TSID00007', 'Taguines Lagoon', 'Benoni', 'Mahinog', 'Taguines Lagoon is a man-made lagoon and aquatic sports facility located in Mahinog, Camiguin, Philippines. Situated near the Benoni Port and surrounded by mountains, it offers a tranquil environment for both relaxation and adventure.', 'Taguines Lagoon does not have a fixed price itself, but accommodations and activities there do.', 'ADID001', 'Cave', '2025-10-11 11:28:07'),
('TSID00008', 'Tuasan Falls', 'Mainit', 'Catarman', 'Tuasan Falls is a picturesque waterfall located in the highlands of Catarman, Camiguin, in the Philippines. It is known for its powerful cascades, crystal-clear and cool water, and lush jungle setting.', 'Entrance fee for visitors is typically between ₱50 and ₱75 per person.', 'ADID001', 'Falls', '2025-10-11 11:28:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(50) NOT NULL,
  `user_code` varchar(50) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('tourist','provider') NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_code`, `fullname`, `email`, `password`, `user_type`, `contact_number`, `address`, `city`, `province`, `country`, `date_created`) VALUES
('USR10000', 'USC10000', 'Aaron ', 'useraaron@gmail.com', '$2y$10$yNqyfmMn5Gt/e7WJIQ7SQuXPnzTf2qEWp1HxWRIKGFFvCncy9wNlq', 'provider', '09123456789', 'Naasag', 'Mambajao', 'Camiguin', 'Philippines', '2025-09-06 02:26:27'),
('USR10001', 'USC10001', 'Rook', 'rook@gmail.com', '$2y$10$u0g4A2fdtcbaz0RElNEA9e//7waxtSCWzrTT0aTkCcxepG8RaLadG', 'tourist', '09123456789', 'Rook', 'Mambajao', 'Camiguin', 'Philippines', '2025-09-06 05:15:16'),
('USR10002', 'USC10002', 'Joseph', 'jv@gmail.com', '$2y$10$gvuPct5eQnkhAgP470fOROnmTpBBUHjSGiDc6apca4KOGsFT7lycC', 'tourist', '09123456789', 'Bug-ong', 'Mambajao', 'Camiguin', 'Philippines', '2025-09-16 12:32:02'),
('USR10003', 'USC10003', 'James', 'james@gmail.com', '$2y$10$o3G6fjh53d17FryXvXWDkemuQDLhbkDdoBrrMnt0Ls..I7pBJtsbu', 'tourist', '09123456789', 'Yumbing', 'Mambajao', 'Camiguin', 'Philippines', '2025-09-17 10:19:43'),
('USR10004', 'USC10004', 'Joseph ', 'joseph@gmail.com', '$2y$10$NeA9NOgabNt.W2VRU85jfu3WcxelqzkG43zWvep8T.v2DRoBQr43S', 'tourist', '09123456789', 'Balbagon', 'Mambajao', 'Camiguin', 'Philippines', '2025-09-25 23:46:48'),
('USR10005', 'USC10005', 'Tourist Travellers', 'tourist@gmail.com', '$2y$10$UTUnulpNIy.7sVqeM8OntObaVcMXqaOsy1AoX.2orOmqK5JzmFS2W', 'tourist', '09123456789', 'Traveller', 'Traveller', 'Traveller', 'Traveller', '2025-12-16 07:59:16'),
('USR10006', 'USC10006', 'Tourist', 'kukuropatatas@gmail.com', '$2y$10$qvpE4tNTRllC0htjkq8Kb.jB9uLLYTnwwaA3gD1ipHiNC..9mdS3S', 'tourist', '0912345678', 'Tagdo', 'Mambajao', 'Camiguin', 'Philippines', '2026-03-30 06:58:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `rental_images`
--
ALTER TABLE `rental_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `tourist_id` (`tourist_id`);

--
-- Indexes for table `spot_pictures`
--
ALTER TABLE `spot_pictures`
  ADD PRIMARY KEY (`picture_id`),
  ADD KEY `spot_id` (`spot_id`);

--
-- Indexes for table `spot_reviews`
--
ALTER TABLE `spot_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_spot` (`spot_id`),
  ADD KEY `fk_booking` (`booking_id`);

--
-- Indexes for table `tourist_spot`
--
ALTER TABLE `tourist_spot`
  ADD PRIMARY KEY (`spot_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_code` (`user_code`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rental_images`
--
ALTER TABLE `rental_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `spot_reviews`
--
ALTER TABLE `spot_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rental_images`
--
ALTER TABLE `rental_images`
  ADD CONSTRAINT `rental_images_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`tourist_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_pictures`
--
ALTER TABLE `spot_pictures`
  ADD CONSTRAINT `spot_pictures_ibfk_1` FOREIGN KEY (`spot_id`) REFERENCES `tourist_spot` (`spot_id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_reviews`
--
ALTER TABLE `spot_reviews`
  ADD CONSTRAINT `fk_booking` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_spot` FOREIGN KEY (`spot_id`) REFERENCES `tourist_spot` (`spot_id`) ON DELETE CASCADE;

--
-- Constraints for table `tourist_spot`
--
ALTER TABLE `tourist_spot`
  ADD CONSTRAINT `tourist_spot_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
