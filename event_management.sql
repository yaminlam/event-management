-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 24, 2025 at 07:50 AM
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
-- Database: `event_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendee_registrations`
--

CREATE TABLE `attendee_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `attendee_name` varchar(100) NOT NULL,
  `attendee_email` varchar(100) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendee_registrations`
--

INSERT INTO `attendee_registrations` (`id`, `event_id`, `attendee_name`, `attendee_email`, `registered_at`) VALUES
(1, 1, 'yamin', 'yaminlambappi009@gmail.com', '2025-01-22 08:41:18'),
(2, 2, 'yamin', 'yaminlambappi009@gmail.com', '2025-01-22 08:50:47'),
(3, 1, 'yaminmm', 'yaminlambappi009@gmail.com', '2025-01-22 17:04:15'),
(4, 1, 'yamin', 'yaminlambappi009@gmail.com', '2025-01-24 06:14:18'),
(5, 2, 'yamin', 'yaminlambappi009@gmail.com', '2025-01-24 06:18:21'),
(6, 1, 'yamin', 'yaminlambappi009@gmail.com', '2025-01-24 06:30:01');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `description`, `capacity`, `created_at`, `user_id`) VALUES
(1, 'Md Yamin Lam Bappi', 'asdf', 123, '2025-01-22 08:40:45', 1),
(2, 'Product update', 'sd3', 3, '2025-01-22 08:50:30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'yamin', 'yaminlambappi009@gmail.com', '$2y$10$hzZLJ6X6PcogYGeIwTGAlu7tJllqmpgTLRH/yTGKsKOf096GJ7lvS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendee_registrations`
--
ALTER TABLE `attendee_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `attendee_registrations`
--
ALTER TABLE `attendee_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendee_registrations`
--
ALTER TABLE `attendee_registrations`
  ADD CONSTRAINT `fk_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
