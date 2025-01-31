-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 31, 2025 at 09:12 AM
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
-- Database: `blog_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `content`, `created_at`) VALUES
(25, 'New Movie Release !', '\r\nThe Monkey 2025 ‧ Horror ‧ 1h 35m\r\n\r\n When twin brothers Bill and Hal find their father\'s old monkey toy in the attic, a series of deaths start. The siblings decide to throw the toy away and move on with their lives, growing apart over the years.', '2025-01-31 07:13:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `jwt_token` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `jwt_token`, `last_login`) VALUES
(10, 'malitha', '$2y$10$bxOqUMagnR85B9gObfNv0.9byvOHR/nWyZsyXNCb10oQnWL1c70l2', 'admin', '2025-01-30 09:39:37', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTAsInVzZXJuYW1lIjoibWFsaXRoYSIsInJvbGUiOiJhZG1pbiIsImlhdCI6MTczODMwNzg0MSwiZXhwIjoxNzM4MzExNDQxfQ.XGI_7hQh5KX-Vd6gso7sWgjvhTgqiGst1QztS2NHRu4', '2025-01-31 12:47:21'),
(11, 'buddika', '$2y$10$nEgUPeNmaJmleCqqTWcehesBgb.ThR3Dw.G/PhHevtzFqGjS4CU22', 'user', '2025-01-30 09:40:19', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTEsInVzZXJuYW1lIjoiYnVkZGlrYSIsInJvbGUiOiJ1c2VyIiwiaWF0IjoxNzM4MzA3ODQ4LCJleHAiOjE3MzgzMTE0NDh9.G-dv48ZoA1wYiGpcJ4FcQyFfFO43_LLX2VZfBZwK9sg', '2025-01-31 12:47:28'),
(12, 'sanduni', '$2y$10$zJHyWQQdqCObHcQZEOqDJOQ12VDc41lNuH7NHYENL8y4MaYIlEHCG', 'user', '2025-01-30 09:40:42', NULL, NULL),
(13, 'sanuka', '$2y$10$c9J8DnGECGbVXYE.9SybQens4zAopwhGgtoBitH1KexKlPxTYxygW', 'user', '2025-01-30 11:12:06', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MzgyMzU1NTEsImV4cCI6MTczODIzOTE1MSwiZGF0YSI6eyJpZCI6MTMsInVzZXJuYW1lIjoic2FudWthIn19.pLfqJUvyfCe8c4Zl3ehGHs-mwvDxj794czk8vGdzGGQ', NULL),
(14, 'qwqwqwq', '$2y$10$NQ9jer80Ka9DCM6/eDxUluXQG.T7ZUqwT4wz4QUcfvB1rkoPEYE1S', 'user', '2025-01-30 12:57:22', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MzgyNDE4NDIsImV4cCI6MTczODI0NTQ0MiwiZGF0YSI6eyJpZCI6IjE0IiwidXNlcm5hbWUiOiJxd3F3cXdxIiwicm9sZSI6InVzZXIifX0.W9FDjg1btrY6DTErRfR3SDQP2oZueq1cPPHvVhCrXCA', NULL),
(15, 'ecom_353', '$2y$10$mDohN0BWiK2fTA5ImRJCleZz6aj5lNug7K6uVmFptpsMON.ucTmb.', 'user', '2025-01-30 13:00:20', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MzgyNDIwMjAsImV4cCI6MTczODI0NTYyMCwiZGF0YSI6eyJpZCI6IjE1IiwidXNlcm5hbWUiOiJlY29tXzM1MyIsInJvbGUiOiJ1c2VyIn19.4lQLX4j1Cg8A1gPNHokbEuz8G1uyFLLYnC-3fe2Auns', NULL),
(16, 'asasasas', '$2y$10$s3u3kY90OFMYTQGYsERSTun7g6W0EOEGE.ATxEcQTBXcCEBQXvHkG', 'user', '2025-01-30 14:10:45', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MzgyNDYyNDUsImV4cCI6MTczODI0OTg0NSwiZGF0YSI6eyJpZCI6IjE2IiwidXNlcm5hbWUiOiJhc2FzYXNhcyIsInJvbGUiOiJ1c2VyIn19.1oN6Xv55Ox3VAsDBGp5_caW7nsgiwO2qWEP4cN0IhYQ', NULL),
(17, 'sdsddsds', '$2y$10$HOzDZhHA6ocqT8l7KuxSiusHnDtK6GqYpS7SRxV/RGdWJZ3ug3Uee', 'user', '2025-01-30 15:50:20', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MzgyNTIyMjEsImV4cCI6MTczODI1NTgyMSwiZGF0YSI6eyJpZCI6IjE3IiwidXNlcm5hbWUiOiJzZHNkZHNkcyIsInJvbGUiOiJ1c2VyIn19.kK5aNKZABGezZdTscprIOQTf3JKhEFxTEDtH83NUMMY', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
