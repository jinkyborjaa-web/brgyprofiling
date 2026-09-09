-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 05:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brgy_profiling`
--

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `Certificate_ID` int(11) NOT NULL,
  `Resident_ID` int(11) NOT NULL,
  `Certificate_Type` varchar(100) NOT NULL,
  `Purpose` varchar(255) DEFAULT NULL,
  `Date_Issued` date NOT NULL,
  `Issued_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`Certificate_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `family`
--

CREATE TABLE `family` (
  `Family_ID` int(11) NOT NULL,
  `Household_ID` varchar(50) NOT NULL,
  `Family_Name` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Head_of_Family` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Family_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officials`
--

CREATE TABLE `officials` (
  `Official_ID` int(11) NOT NULL,
  `Resident_ID` int(11) NOT NULL,
  `Position` varchar(100) NOT NULL,
  `Term_start` date NOT NULL,
  `Term_end` date NOT NULL,
  PRIMARY KEY (`Official_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `Resident_ID` int(11) NOT NULL,
  `Family_ID` int(11) DEFAULT NULL,
  `First_Name` varchar(255) NOT NULL,
  `Middle_Name` varchar(255) DEFAULT NULL,
  `Last_Name` varchar(255) NOT NULL,
  `Suffix` varchar(50) DEFAULT NULL,
  `Gender` varchar(20) NOT NULL,
  `Date_of_Birth` date NOT NULL,
  `Civil_Status` varchar(50) DEFAULT NULL,
  `Relationship_to_Head` varchar(100) DEFAULT NULL,
  `Occupation_Employment_Status` varchar(255) DEFAULT NULL,
  `Educational_Attainment` varchar(255) DEFAULT NULL,
  `Contact_Number` varchar(50) DEFAULT NULL,
  `Disability_Status` varchar(255) DEFAULT NULL,
  `Religion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Resident_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD KEY `Resident_ID` (`Resident_ID`),
  ADD KEY `Issued_by` (`Issued_by`);

--
-- Indexes for table `family`
--
ALTER TABLE `family`
  ADD UNIQUE KEY `Household_ID` (`Household_ID`),
  ADD KEY `fk_family_head` (`Head_of_Family`);

--
-- Indexes for table `officials`
--
ALTER TABLE `officials`
  ADD KEY `Resident_ID` (`Resident_ID`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD KEY `fk_residents_family` (`Family_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `Certificate_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officials`
--
ALTER TABLE `officials`
  MODIFY `Official_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `Resident_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`Resident_ID`) REFERENCES `residents` (`Resident_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`Issued_by`) REFERENCES `officials` (`Official_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `officials`
--
ALTER TABLE `officials`
  ADD CONSTRAINT `officials_ibfk_1` FOREIGN KEY (`Resident_ID`) REFERENCES `residents` (`Resident_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `fk_residents_family` FOREIGN KEY (`Family_ID`) REFERENCES `family` (`Family_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

