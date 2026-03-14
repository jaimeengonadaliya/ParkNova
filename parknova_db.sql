-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2026 at 02:56 PM
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
-- Database: `parknova_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parking_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `vehicle_number` varchar(50) NOT NULL,
  `vehicle_type` enum('Car','Bike') NOT NULL DEFAULT 'Car',
  `booking_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `parking_id`, `slot_id`, `vehicle_number`, `vehicle_type`, `booking_date`, `start_time`, `end_time`, `amount`, `status`, `created_at`) VALUES
(1, 45, 2, 16, 'GJ05MS4455', 'Car', '2026-03-11', '09:00:00', '10:00:00', NULL, 'pending', '2026-03-11 16:06:09'),
(3, 45, 2, 13, 'GJ05MS8341', 'Car', '2026-03-13', '20:34:00', '23:05:00', 720.00, 'pending', '2026-03-12 14:49:27'),
(4, 45, 2, 14, '12345', 'Car', '2026-03-12', '20:40:58', '20:41:12', 40.00, 'completed', '2026-03-12 15:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `parking_locations`
--

CREATE TABLE `parking_locations` (
  `parking_id` int(11) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `parking_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `parking_type` enum('Mall','Public','Private') DEFAULT 'Public',
  `total_slots` int(11) NOT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `image` varchar(255) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_locations`
--

INSERT INTO `parking_locations` (`parking_id`, `manager_id`, `parking_name`, `address`, `city`, `parking_type`, `total_slots`, `price_per_hour`, `status`, `image`, `created_date`, `latitude`, `longitude`) VALUES
(1, 34, 'Sabarmati Riverside Parking', 'Near Ashram Road', 'Ahmedabad', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:14', '23.0300', '72.5800'),
(2, 35, 'Diamond City Plaza Parking', 'Varachha Main Road', 'Surat', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:14', '21.1702', '72.8311'),
(3, 36, 'Sayaji Garden Parking', 'Kala Ghoda Circle', 'Vadodara', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:14', '22.3072', '73.1812'),
(4, 37, 'Crystal Mall Parking', 'Kalavad Road', 'Rajkot', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:14', '22.3039', '70.8022'),
(5, 38, 'Gift City Smart Parking', 'Gift City Tower 1', 'Gandhinagar', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:14', '23.2156', '72.6369'),
(6, 39, 'Marine Drive Premium Parking', 'Netaji Subhash Chandra Bose Road', 'Mumbai', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:14', '18.9431', '72.8230'),
(7, 40, 'Connaught Place Parking', 'Outer Circle, CP', 'Delhi', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:15', '28.6315', '77.2167'),
(8, 41, 'MG Road Metro Parking', 'MG Road, Shivajinagar', 'Bangalore', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:15', '12.9716', '77.5946'),
(9, 42, 'HITEC City Tech Parking', 'Cyber Towers Phase 1', 'Hyderabad', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:15', '17.4504', '78.3811'),
(10, 43, 'Koregaon Park Elite Parking', 'Lane 7, Koregaon Park', 'Pune', 'Public', 12, 40.00, 'active', NULL, '2026-03-11 15:06:15', '18.5362', '73.8940');

-- --------------------------------------------------------

--
-- Table structure for table `parking_slots`
--

CREATE TABLE `parking_slots` (
  `slot_id` int(11) NOT NULL,
  `parking_id` int(11) NOT NULL,
  `slot_number` varchar(20) NOT NULL,
  `slot_type` enum('2W','4W','EV') DEFAULT '4W',
  `status` enum('available','booked','occupied') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_slots`
--

INSERT INTO `parking_slots` (`slot_id`, `parking_id`, `slot_number`, `slot_type`, `status`) VALUES
(1, 1, 'A1', '4W', 'available'),
(2, 1, 'A2', '4W', 'available'),
(3, 1, 'A3', '4W', 'available'),
(4, 1, 'A4', '4W', 'available'),
(5, 1, 'B1', '4W', 'available'),
(6, 1, 'B2', '4W', 'available'),
(7, 1, 'B3', '4W', 'available'),
(8, 1, 'B4', '4W', 'available'),
(9, 1, 'C1', '4W', 'available'),
(10, 1, 'C2', '4W', 'available'),
(11, 1, 'C3', '4W', 'available'),
(12, 1, 'C4', '4W', 'available'),
(13, 2, 'A1', '4W', 'occupied'),
(14, 2, 'A2', '4W', 'available'),
(15, 2, 'A3', '4W', 'available'),
(16, 2, 'A4', '4W', 'booked'),
(17, 2, 'B1', '4W', 'available'),
(18, 2, 'B2', '4W', 'available'),
(19, 2, 'B3', '4W', 'available'),
(20, 2, 'B4', '4W', 'available'),
(21, 2, 'C1', '4W', 'available'),
(22, 2, 'C2', '4W', 'available'),
(23, 2, 'C3', '4W', 'available'),
(24, 2, 'C4', '4W', 'available'),
(25, 3, 'A1', '4W', 'available'),
(26, 3, 'A2', '4W', 'available'),
(27, 3, 'A3', '4W', 'available'),
(28, 3, 'A4', '4W', 'available'),
(29, 3, 'B1', '4W', 'available'),
(30, 3, 'B2', '4W', 'available'),
(31, 3, 'B3', '4W', 'available'),
(32, 3, 'B4', '4W', 'available'),
(33, 3, 'C1', '4W', 'available'),
(34, 3, 'C2', '4W', 'available'),
(35, 3, 'C3', '4W', 'available'),
(36, 3, 'C4', '4W', 'available'),
(37, 4, 'A1', '4W', 'available'),
(38, 4, 'A2', '4W', 'available'),
(39, 4, 'A3', '4W', 'available'),
(40, 4, 'A4', '4W', 'available'),
(41, 4, 'B1', '4W', 'available'),
(42, 4, 'B2', '4W', 'available'),
(43, 4, 'B3', '4W', 'available'),
(44, 4, 'B4', '4W', 'available'),
(45, 4, 'C1', '4W', 'available'),
(46, 4, 'C2', '4W', 'available'),
(47, 4, 'C3', '4W', 'available'),
(48, 4, 'C4', '4W', 'available'),
(49, 5, 'A1', '4W', 'available'),
(50, 5, 'A2', '4W', 'available'),
(51, 5, 'A3', '4W', 'available'),
(52, 5, 'A4', '4W', 'available'),
(53, 5, 'B1', '4W', 'available'),
(54, 5, 'B2', '4W', 'available'),
(55, 5, 'B3', '4W', 'available'),
(56, 5, 'B4', '4W', 'available'),
(57, 5, 'C1', '4W', 'available'),
(58, 5, 'C2', '4W', 'available'),
(59, 5, 'C3', '4W', 'available'),
(60, 5, 'C4', '4W', 'available'),
(61, 6, 'A1', '4W', 'available'),
(62, 6, 'A2', '4W', 'available'),
(63, 6, 'A3', '4W', 'available'),
(64, 6, 'A4', '4W', 'available'),
(65, 6, 'B1', '4W', 'available'),
(66, 6, 'B2', '4W', 'available'),
(67, 6, 'B3', '4W', 'available'),
(68, 6, 'B4', '4W', 'available'),
(69, 6, 'C1', '4W', 'available'),
(70, 6, 'C2', '4W', 'available'),
(71, 6, 'C3', '4W', 'available'),
(72, 6, 'C4', '4W', 'available'),
(73, 7, 'A1', '4W', 'available'),
(74, 7, 'A2', '4W', 'available'),
(75, 7, 'A3', '4W', 'available'),
(76, 7, 'A4', '4W', 'available'),
(77, 7, 'B1', '4W', 'available'),
(78, 7, 'B2', '4W', 'available'),
(79, 7, 'B3', '4W', 'available'),
(80, 7, 'B4', '4W', 'available'),
(81, 7, 'C1', '4W', 'available'),
(82, 7, 'C2', '4W', 'available'),
(83, 7, 'C3', '4W', 'available'),
(84, 7, 'C4', '4W', 'available'),
(85, 8, 'A1', '4W', 'available'),
(86, 8, 'A2', '4W', 'available'),
(87, 8, 'A3', '4W', 'available'),
(88, 8, 'A4', '4W', 'available'),
(89, 8, 'B1', '4W', 'available'),
(90, 8, 'B2', '4W', 'available'),
(91, 8, 'B3', '4W', 'available'),
(92, 8, 'B4', '4W', 'available'),
(93, 8, 'C1', '4W', 'available'),
(94, 8, 'C2', '4W', 'available'),
(95, 8, 'C3', '4W', 'available'),
(96, 8, 'C4', '4W', 'available'),
(97, 9, 'A1', '4W', 'available'),
(98, 9, 'A2', '4W', 'available'),
(99, 9, 'A3', '4W', 'available'),
(100, 9, 'A4', '4W', 'available'),
(101, 9, 'B1', '4W', 'available'),
(102, 9, 'B2', '4W', 'available'),
(103, 9, 'B3', '4W', 'available'),
(104, 9, 'B4', '4W', 'available'),
(105, 9, 'C1', '4W', 'available'),
(106, 9, 'C2', '4W', 'available'),
(107, 9, 'C3', '4W', 'available'),
(108, 9, 'C4', '4W', 'available'),
(109, 10, 'A1', '4W', 'available'),
(110, 10, 'A2', '4W', 'available'),
(111, 10, 'A3', '4W', 'available'),
(112, 10, 'A4', '4W', 'available'),
(113, 10, 'B1', '4W', 'available'),
(114, 10, 'B2', '4W', 'available'),
(115, 10, 'B3', '4W', 'available'),
(116, 10, 'B4', '4W', 'available'),
(117, 10, 'C1', '4W', 'available'),
(118, 10, 'C2', '4W', 'available'),
(119, 10, 'C3', '4W', 'available'),
(120, 10, 'C4', '4W', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_method` enum('Online','Cash') NOT NULL,
  `payment_status` enum('Pending','Success','Failed') NOT NULL DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `payment_method`, `payment_status`, `payment_date`) VALUES
(1, 3, 'Online', 'Success', '2026-03-12 14:49:27'),
(2, 4, 'Online', 'Success', '2026-03-12 15:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vehicle_number` varchar(50) DEFAULT NULL,
  `vehicle_type` enum('Car','Bike','EV') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role`, `status`, `name`, `email`, `mobile`, `password`, `created_at`, `vehicle_number`, `vehicle_type`) VALUES
(1, 'admin', 'active', 'Administrator', 'admin@parknova_db.com', '9876543210', '$2y$10$VGlJSljSuojW/qBcY3E8AuA2m0sC8cDfCtwG5KwIrWdt3PwGSMjCC', '2026-03-11 13:07:35', NULL, NULL),
(2, 'super_admin', 'active', 'Super Admin', 'super@parknova.com', '9999999999', '$2y$10$X41hXXnfNpjDwbFAaBitIOxZFDfqyS4PSbbDKxB47udFyCetJn2my', '2026-03-11 13:31:47', NULL, NULL),
(33, 'super_admin', 'active', 'System Admin', 'admin@parknova.com', NULL, '$2y$10$STcfGOSpCxRacuhA/ElvD.5zeBvFJyeW2oe..8E7hz/hKJv1lEnSW', '2026-03-11 15:06:14', NULL, NULL),
(34, 'manager', 'active', 'Manager Ahmedabad', 'manager_ahmedabad@parknova.com', NULL, '$2y$10$kNfLdlPQKzk0DTSpgSHSIOeVe440aGBtfu1JwRn.HS7nWUKCueT6.', '2026-03-11 15:06:14', NULL, NULL),
(35, 'manager', 'active', 'Manager Surat', 'manager_surat@parknova.com', NULL, '$2y$10$ExcOvEvlMgjkrXzTEE75X.MEwkdzn9NAVJkk00qdav.1Ub8GyCEFK', '2026-03-11 15:06:14', NULL, NULL),
(36, 'manager', 'active', 'Manager Vadodara', 'manager_vadodara@parknova.com', NULL, '$2y$10$lMAsm7.SbWp5rLa5twLSR.SQhli20U/f3RomTd7dEPXCcgrXKq.q2', '2026-03-11 15:06:14', NULL, NULL),
(37, 'manager', 'active', 'Manager Rajkot', 'manager_rajkot@parknova.com', NULL, '$2y$10$SoMpvk7v9nXrOcimkQOVDeNBw8nNCr473xcqEHuSHpNsqEnKOTvRi', '2026-03-11 15:06:14', NULL, NULL),
(38, 'manager', 'active', 'Manager Gandhinagar', 'manager_gandhinagar@parknova.com', NULL, '$2y$10$BPzLTYowt2ertPVirGpeU.xGhePPotkFy4mpKrByTWIpxU.pdQW8i', '2026-03-11 15:06:14', NULL, NULL),
(39, 'manager', 'active', 'Manager Mumbai', 'manager_mumbai@parknova.com', NULL, '$2y$10$ErksG7NrkCeEPRo3suFjc.SaER4/DIkQyRBzS2hhwCq0nkoN00Lx.', '2026-03-11 15:06:14', NULL, NULL),
(40, 'manager', 'active', 'Manager Delhi', 'manager_delhi@parknova.com', NULL, '$2y$10$etMuhkEIbXzTZbAGTQhuDueB55a1k4S5EeazJbe1yTNZRff.nf6xq', '2026-03-11 15:06:15', NULL, NULL),
(41, 'manager', 'active', 'Manager Bangalore', 'manager_bangalore@parknova.com', NULL, '$2y$10$1IrKY5UBJ4n.5Iu6A5iFAuFot/raOcDzI8G9jAOkjPc4mpuGgdEk6', '2026-03-11 15:06:15', NULL, NULL),
(42, 'manager', 'active', 'Manager Hyderabad', 'manager_hyderabad@parknova.com', NULL, '$2y$10$HtrnuKHCdA6sK5hGFhdIeO64vtetnBOjAAMeY911UYWjC8Sat2yCG', '2026-03-11 15:06:15', NULL, NULL),
(43, 'manager', 'active', 'Manager Pune', 'manager_pune@parknova.com', NULL, '$2y$10$K88WGMG3AnLpEvMFEVhjAOjtURZKSVMBzqg0x4QsDMjENFEVWPtQO', '2026-03-11 15:06:15', NULL, NULL),
(44, 'user', 'active', 'Test User', 'testuser@example.com', '9876543210', '$2y$10$07ec6wSMhqxwpjw5U2xCse3dUL3Ro0nA8EOHT2Ha6I/UEORW5UE8m', '2026-03-11 15:08:26', NULL, NULL),
(45, 'user', 'active', 'Jaimeen Gondaliya', 'jaminigondaliya2503@gmail.com', '9913690245', '$2y$10$IJElDN3tARjtduGXd/lQAels5YFvrC0ZthcZC3Ee./gS2H8S5Cg3S', '2026-03-11 16:02:30', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parking_id` (`parking_id`),
  ADD KEY `slot_id` (`slot_id`);

--
-- Indexes for table `parking_locations`
--
ALTER TABLE `parking_locations`
  ADD PRIMARY KEY (`parking_id`);

--
-- Indexes for table `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD PRIMARY KEY (`slot_id`),
  ADD KEY `parking_id` (`parking_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parking_locations`
--
ALTER TABLE `parking_locations`
  MODIFY `parking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `parking_slots`
--
ALTER TABLE `parking_slots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`parking_id`) REFERENCES `parking_locations` (`parking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`slot_id`) REFERENCES `parking_slots` (`slot_id`) ON DELETE CASCADE;

--
-- Constraints for table `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD CONSTRAINT `parking_slots_ibfk_1` FOREIGN KEY (`parking_id`) REFERENCES `parking_locations` (`parking_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
