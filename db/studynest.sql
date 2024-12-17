-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2024 at 12:35 PM
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
-- Database: `studynest`
--

-- --------------------------------------------------------

--
-- Table structure for table `activitylog`
--

CREATE TABLE `activitylog` (
  `logId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `answerId` int(11) NOT NULL,
  `questionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `answerText` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `commentId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `parentId` int(11) DEFAULT NULL,
  `entityTypeId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `commentText` text NOT NULL,
  `isRead` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `courseId` int(11) NOT NULL,
  `courseName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`courseId`, `courseName`) VALUES
(1, 'Calculus'),
(2, 'Statistics'),
(3, 'Linear Algebra'),
(4, 'Database Management System'),
(5, 'Principles of Economics'),
(6, 'Python Programming');

-- --------------------------------------------------------

--
-- Table structure for table `entitytypes`
--

CREATE TABLE `entitytypes` (
  `entityTypeId` int(11) NOT NULL,
  `entityTypeName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entitytypes`
--

INSERT INTO `entitytypes` (`entityTypeId`, `entityTypeName`) VALUES
(11, 'note'),
(12, 'course'),
(13, 'topic'),
(14, 'question'),
(15, 'comment'),
(16, 'note');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `likeId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `majors`
--

CREATE TABLE `majors` (
  `majorId` int(11) NOT NULL,
  `majorName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `majors`
--

INSERT INTO `majors` (`majorId`, `majorName`) VALUES
(1, 'CS'),
(2, 'MIS'),
(3, 'BA');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `newsId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `newsTitle` varchar(255) DEFAULT NULL,
  `newsContent` text NOT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fileAttachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `noteId` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `topicId` int(11) NOT NULL,
  `noteText` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`noteId`, `userId`, `topicId`, `noteText`, `created_at`, `updated_at`, `image_path`) VALUES
(13, 7, 71, 'Note about technology', '2024-12-14 00:01:16', '2024-12-14 00:01:16', NULL),
(14, 7, 72, 'Note about health', '2024-12-14 00:01:16', '2024-12-14 00:01:16', NULL),
(15, 1, 72, '<p>Limits is good</p>\r\n', '2024-12-14 00:03:29', '2024-12-14 00:03:29', NULL),
(16, 1, 74, '<p>Life is all about probability.</p>\r\n', '2024-12-14 00:25:42', '2024-12-14 00:25:42', NULL),
(17, 1, 74, '<p>Life is all about probability.</p>\r\n', '2024-12-14 00:29:49', '2024-12-14 00:29:49', NULL),
(20, 1, 76, '', '2024-12-14 17:25:20', '2024-12-14 17:25:20', '675dbf8067958_note.png'),
(21, 1, 77, '<p><strong>Demand</strong> refers to the quantity of a good or service that consumers are willing and able to purchase at various price levels during a specific period. The relationship between price and quantity demanded is typically inverse, meaning as price decreases, demand increases, and vice versa (Law of Demand).</p>\r\n\r\n<ul>\r\n	<li><strong>Factors affecting demand</strong>:\r\n\r\n	<ul>\r\n		<li>Price of the good.</li>\r\n		<li>Consumer income.</li>\r\n		<li>Preferences and tastes.</li>\r\n		<li>Prices of related goods (substitutes and complements).</li>\r\n		<li>Future expectations.</li>\r\n	</ul>\r\n	</li>\r\n</ul>\r\n', '2024-12-14 17:32:18', '2024-12-14 17:32:18', NULL),
(22, 1, 77, '<p><strong>Demand</strong> refers to the quantity of a good or service that consumers are willing and able to purchase at various price levels during a specific period. The relationship between price and quantity demanded is typically inverse, meaning as price decreases, demand increases, and vice versa (Law of Demand).</p>\r\n\r\n<ul>\r\n	<li><strong>Factors affecting demand</strong>:\r\n\r\n	<ul>\r\n		<li>Price of the good.</li>\r\n		<li>Consumer income.</li>\r\n		<li>Preferences and tastes.</li>\r\n		<li>Prices of related goods (substitutes and complements).</li>\r\n		<li>Future expectations.</li>\r\n	</ul>\r\n	</li>\r\n</ul>\r\n', '2024-12-14 17:34:40', '2024-12-14 17:34:40', '675dc1b0b7c1b_sd.jpeg'),
(23, 1, 80, '<p>There are different types of loops; we have the for loop, while loop, and the do-while loop, too.&nbsp;</p>\r\n', '2024-12-15 15:58:31', '2024-12-15 15:58:31', NULL),
(24, 1, 80, '<p>There are different types of loops; we have the for loop, while loop, and the do-while loop, too.&nbsp;</p>\r\n', '2024-12-15 15:59:34', '2024-12-15 15:59:34', NULL),
(25, 7, 81, '<p>Economics teachs individual about trade off, which can apply in everything we do</p>', '2024-12-15 17:27:54', '2024-12-15 17:27:54', NULL),
(26, 7, 82, '<p>this is code</p>', '2024-12-15 18:06:29', '2024-12-15 18:06:29', NULL),
(27, 7, 83, '<p>Do all the same time</p>', '2024-12-15 18:25:36', '2024-12-15 18:25:36', NULL),
(28, 7, 94, '<p>Derivatives</p>', '2024-12-15 18:54:53', '2024-12-15 18:54:53', 'uploads/notes/note_675f25fd75c27.png'),
(29, 7, 94, '<p>Derivatives</p>', '2024-12-15 18:54:55', '2024-12-15 18:54:55', 'uploads/notes/note_675f25ff70c8e.png'),
(30, 7, 72, '<p>Limit is easy.</p>', '2024-12-15 19:01:36', '2024-12-15 19:01:36', 'uploads/notes/675f2790f1d49.png'),
(31, 7, 72, '<p>Take it now.</p>', '2024-12-15 19:04:49', '2024-12-15 19:04:49', 'uploads/notes/675f28510c396.png');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notificationId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `message` text NOT NULL,
  `isRead` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `questionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `topicId` int(11) NOT NULL,
  `questionText` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `roleId` int(11) NOT NULL,
  `roleName` enum('student','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`roleId`, `roleName`) VALUES
(1, 'student'),
(2, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `topicId` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `topicName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`topicId`, `courseId`, `topicName`) VALUES
(71, 3, 'Matrix'),
(72, 1, 'Limits'),
(73, 4, 'Constraint'),
(74, 2, 'Probability'),
(75, 1, 'integration'),
(76, 6, 'Control flow'),
(77, 5, 'Demand and Supply'),
(78, 3, 'Inverses'),
(79, 1, 'Differentiation'),
(80, 6, 'Loops'),
(81, 5, 'Trade off'),
(82, 6, 'Code'),
(83, 1, 'Differential Equation'),
(94, 1, 'Derivatives'),
(95, 2, 'Population ');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userId` int(11) NOT NULL,
  `firstName` varchar(50) DEFAULT NULL,
  `lastName` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `roleId` int(11) NOT NULL,
  `majorId` int(11) DEFAULT NULL,
  `yearGroup` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `firstName`, `lastName`, `email`, `password`, `roleId`, `majorId`, `yearGroup`, `created_at`, `updated_at`) VALUES
(8, 'Amina', 'Mohammed', 'amina@gmail.com', '$2y$10$NQOFwwZfu.VQxDmOBLNqK.AC4XFawE8gsYeltzMTp7GuZSArqxEuW', 2, 3, 2015, '2024-12-14 20:09:18', '2024-12-16 11:14:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activitylog`
--
ALTER TABLE `activitylog`
  ADD PRIMARY KEY (`logId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `entityTypeId` (`entityTypeId`);

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`answerId`),
  ADD KEY `questionId` (`questionId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`commentId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `entityTypeId` (`entityTypeId`),
  ADD KEY `parentId` (`parentId`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`courseId`);

--
-- Indexes for table `entitytypes`
--
ALTER TABLE `entitytypes`
  ADD PRIMARY KEY (`entityTypeId`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`likeId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `entityTypeId` (`entityTypeId`);

--
-- Indexes for table `majors`
--
ALTER TABLE `majors`
  ADD PRIMARY KEY (`majorId`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`newsId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`noteId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `topicId` (`topicId`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notificationId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`questionId`),
  ADD KEY `topicId` (`topicId`),
  ADD KEY `questions_ibfk_1` (`userId`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`roleId`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`topicId`),
  ADD KEY `courseId` (`courseId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `majorId` (`majorId`),
  ADD KEY `users_ibfk_1` (`roleId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activitylog`
--
ALTER TABLE `activitylog`
  MODIFY `logId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `answerId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `commentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `courseId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `entitytypes`
--
ALTER TABLE `entitytypes`
  MODIFY `entityTypeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `likeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `majors`
--
ALTER TABLE `majors`
  MODIFY `majorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `newsId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `noteId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notificationId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `questionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `roleId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `topicId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activitylog`
--
ALTER TABLE `activitylog`
  ADD CONSTRAINT `activitylog_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  ADD CONSTRAINT `activitylog_ibfk_2` FOREIGN KEY (`entityTypeId`) REFERENCES `entitytypes` (`entityTypeId`) ON DELETE CASCADE;

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`questionId`) REFERENCES `questions` (`questionId`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`entityTypeId`) REFERENCES `entitytypes` (`entityTypeId`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parentId`) REFERENCES `comments` (`commentId`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`entityTypeId`) REFERENCES `entitytypes` (`entityTypeId`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`topicId`) REFERENCES `topics` (`topicId`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`topicId`) REFERENCES `topics` (`topicId`) ON DELETE CASCADE;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`courseId`) REFERENCES `courses` (`courseId`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `roles` (`roleId`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`majorId`) REFERENCES `majors` (`majorId`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
