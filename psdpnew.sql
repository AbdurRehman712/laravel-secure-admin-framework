-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 30, 2025 at 09:03 AM
-- Server version: 8.4.3
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `psdpnew`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocations`
--

CREATE TABLE `allocations` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `financial_year_id` bigint UNSIGNED NOT NULL,
  `expense_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `allocation_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `release_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `surrender_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `allocations`
--

INSERT INTO `allocations` (`id`, `project_id`, `financial_year_id`, `expense_amount`, `allocation_amount`, `release_amount`, `surrender_amount`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 15, 132.67, 300.00, 132.67, 0.00, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32', NULL),
(2, 2, 15, 20.00, 50.00, 25.00, 0.00, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32', NULL),
(3, 3, 15, 242.65, 100.00, 246.13, 0.00, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32', NULL),
(4, 4, 15, 95.00, 175.00, 98.46, 0.00, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `approving_authorities`
--

CREATE TABLE `approving_authorities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `approving_authorities`
--

INSERT INTO `approving_authorities` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'DDWP', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(2, 'CDWP', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(3, 'ECNEC', '2025-07-28 02:03:30', '2025-07-28 02:03:30');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliverables`
--

CREATE TABLE `deliverables` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `financial_year_id` bigint UNSIGNED DEFAULT NULL,
  `quarter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deliverables`
--

INSERT INTO `deliverables` (`id`, `name`, `project_id`, `financial_year_id`, `quarter`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Project Implementation', 1, NULL, NULL, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(2, 'Project Implementation', 2, NULL, NULL, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(3, 'Project Implementation', 3, NULL, NULL, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(4, 'Project Implementation', 4, NULL, NULL, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `ministry_id` bigint UNSIGNED DEFAULT NULL,
  `division_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isAut` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `ministry_id`, `division_id`, `name`, `address`, `phone`, `isActive`, `isAut`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Aviation Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(2, 1, 1, 'Pakistan Civil Aviation Autority (PCAA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(3, 1, 1, 'Airports Security Force (ASF)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(4, 1, 1, 'Pakistan International Airlines Corporation Limited (PIACL)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(5, 1, 1, 'Pakistan Meterological Department (PMD)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(6, 1, 2, 'Cabinet Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(7, 1, 2, 'National Electric Power Regulatory Authority (NEPRA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(8, 1, 2, 'Pakistan Telecommunication Authority (PTA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(9, 1, 2, 'Oil & Gas Regulatory Authority (OGRA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(10, 1, 2, 'Public Procurement Regulatory Authority (PPRA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(11, 1, 2, 'Pakistan Cricket Board (PCB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(12, 1, 2, 'Special Technology Zones Authority (STZA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(13, 1, 2, 'Naya Pakistan Housing & Development Authority (NAPHDA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(14, 1, 2, 'Frequency Allocation Board (FAB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(15, 1, 2, 'Pakistan Tourism Development Corporation (PTDC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(16, 1, 2, 'Special Investment Facilitation Council (SIFC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(17, 1, 2, 'National Engineering Services Pakistan (NESPAK)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(18, 1, 2, 'NATIONAL TELECOMMUNICATION AND INFORMATION TECHNOLOGY SECURITY BOARD (NTISB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(19, 1, 2, 'National Archives of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(20, 1, 2, 'Printing Corporation of Pakistan (PCP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:29', '2024-10-30 03:52:29'),
(21, 1, 3, 'Establishment Division', 'Cabinet Secretariat, Islamabad', '051-9203573', NULL, 0, '2024-10-30 03:52:29', '2024-12-01 16:21:59'),
(22, 1, 3, 'Federal Public Service Commision (FPSC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(23, 1, 3, 'Secretariat Training Institute (STI)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(24, 1, 3, 'Staff Welfare Organization (SWO)', 'Aabpara, Islamabad', '0519244563', NULL, 0, '2024-10-30 03:52:30', '2024-10-30 06:14:28'),
(25, 1, 3, 'Civil Services Academy', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(26, 1, 3, 'Pakistan Public Administration Research Center (PPARC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(27, 1, 3, 'Akhtar Hameed Khan National Centre for Rural Development (AHKNCRD)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(28, 1, 3, 'Pakistan Academy for Rural Development (PARD)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(29, 1, 3, 'Federal Employees Benevolent & Group Insurance Funds (FEB & GIF)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(30, 1, 3, 'National School of Public Policy (NSPP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(31, 1, 4, 'National Security Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(32, 1, 5, 'Poverty Alleviation & Social Safety Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(33, 1, 5, 'Benazir Income Support Programme (BISP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(34, 1, 5, 'Pakistan Bait-ul-Mal (PBM)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(35, 1, 5, 'Pakistan Poverty Allevation Fund (PPAF)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(36, 1, 5, 'Trust for Voluntary Organizations (TVO)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(37, 1, 5, 'National Poverty Graduation Programme (NPGP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(38, 2, 6, 'Climate Change Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(39, 2, 6, 'Pakistan Environmental Protection Agency', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(40, 2, 6, 'Global Change Impacts Studies Centre', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(41, 2, 6, 'Zoological Survey of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(42, 2, 6, 'Islamabad Wildlife Management Board', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(43, 3, 7, 'Commerce Division', 'Block A, Pak Secretariat Islamabad', '0519217786', NULL, 0, '2024-10-30 03:52:30', '2024-11-15 01:32:14'),
(44, 3, 7, 'Trade Development Authority of Pakistan', 'TDAP, Karachi', '03453478736', NULL, 1, '2024-10-30 03:52:30', '2024-11-28 04:13:38'),
(45, 3, 7, 'Trading Corpporation of Pakistan', '4th & 5th Floor, Block B, Finance & Trade Center, Sharah-e-Faisal, Karachi', '021-99202996', NULL, 1, '2024-10-30 03:52:30', '2024-11-28 04:14:21'),
(46, 3, 7, 'State Life Insurance Corporation of Pakistan', 'State Life  Building No-09 , Dr Ziauddin Ahmed Road, Karachi', '03345558396', NULL, 1, '2024-10-30 03:52:30', '2024-11-28 04:15:18'),
(47, 3, 7, 'National Insurance Company Limited', 'NICL Building Abbasi Shaheed Road, Karachi', '03465378414', NULL, 0, '2024-10-30 03:52:30', '2024-11-15 01:44:40'),
(48, 3, 7, 'Trade Dispute Resolution Organization', 'State Life Buildiing No.5(Phase-1), 2nd Floor, China Chowk, Jinnah Avenue, Islamabad.', '051-9223033', NULL, 0, '2024-10-30 03:52:30', '2024-11-15 01:57:18'),
(49, 3, 7, 'Pakistan Expo Centre Private Limited Lahore', '1-A,Johar Town Expo Center Lahore', '042-352980057', NULL, 0, '2024-10-30 03:52:30', '2024-11-15 02:14:05'),
(50, 3, 7, 'National Tariff Commision', 'State Life Building No 5 Jinnah Avenue F-6/4 Islamabad', '051-9203039', NULL, 0, '2024-10-30 03:52:30', '2024-11-15 02:17:31'),
(51, 3, 7, 'Directorate General of Tarade Organizations', '2nd Floor, State Life Building China Chowk Blue Area Islamabad', '0300-5109801', NULL, 0, '2024-10-30 03:52:30', '2024-12-01 16:19:29'),
(52, 3, 7, 'Pakistan Institute of Trade and Development', 'Pitras Bukhari Road, H-8/4 Islamabad', '051-9269823', NULL, 0, '2024-10-30 03:52:30', '2024-12-01 16:20:04'),
(53, 3, 7, 'Pakistan Horticulture Development & Export Company', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(54, 3, 7, 'Intellectual Property Organization of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(55, 3, 7, 'Export Development Fund', '2nd Floor, FPCCI Capital House, G-8/1 Mauve Area, Islamabad', '051-9107430', NULL, 0, '2024-10-30 03:52:30', '2024-11-29 00:48:52'),
(56, 3, 7, 'Textile Commisioner\'s Organization', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(57, 3, 7, 'Pakistan Cotton Standards Institute', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(58, 4, 8, 'Communication Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(59, 4, 8, 'National Highways Authority', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(60, 4, 8, 'Pakistan Post', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(61, 4, 8, 'National Highways & Motorway Police', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(62, 4, 8, 'National Transport Research Centre', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(63, 4, 8, 'Construction Technology Training Institute', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(64, 4, 8, 'Postal Life Insurance Company Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(65, 5, 9, 'Defence Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(66, 5, 9, 'Joint Staff Headquarters', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(67, 5, 9, 'Military Lands and Cantonments Headquarters', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(68, 5, 9, 'Survey of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(69, 5, 9, 'Federal Government Educational Institutions', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(70, 5, 9, 'Maritime Security Agency', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(71, 5, 9, 'Pakistan Military Accounts Department', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(72, 5, 9, 'Pakistan Armed Services Board Department', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(73, 6, 10, 'Defence Production Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(74, 6, 10, 'Pakistan Aeronautical Complex (PAC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(75, 6, 10, 'Directorate General Munition Production (DGMP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(76, 6, 10, 'Defence Export Promotion Organization (DEPO)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(77, 6, 10, 'Pakistan Ordnance Factories (POFs)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(78, 6, 10, 'Heavy Industries Taxila (HIT)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(79, 6, 10, 'Directorate General Defence Purchase (DGDP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(80, 6, 10, 'National Radio Telecommunication Corporation (NRTC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(81, 6, 10, 'Karachi Shipyard & Engineering Works (KS&EW)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(82, 6, 10, 'Research & Development Establishment (RDE)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(83, 7, 11, 'Economic Affairs Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(84, 8, 12, 'Power Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(85, 8, 12, 'Private Power and Infrastructure Board (PPIB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(86, 8, 12, 'Central Power Purchasing Agency (CPPA), Islamabad', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(87, 8, 12, 'National Transmission and Despatch Company (NTDC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(88, 8, 12, 'Power Information Technology Company (PITC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(89, 8, 12, 'Power Planning & Monitoring Company (PPMC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(90, 8, 12, 'National Power Parks Management Company Private Limited (NPPMCL)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(91, 8, 12, 'Power Holding Limited (PHL)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(92, 8, 12, 'Islamabad Electric Supply Company (IESCO)', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(93, 8, 13, 'Petroleum Division', 'Petroleum Division, Petroleum House, G-5/2, Islamabad', '051-9224874', NULL, 0, '2024-10-30 03:52:30', '2024-12-01 16:15:52'),
(94, 8, 13, 'Department of Explosives', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(95, 8, 13, 'Sui Northern Gas Pipeline Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(96, 8, 13, 'Sui Southern Gas Company Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(97, 8, 13, 'Oil & Gas Development Company Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(98, 8, 13, 'Pakistan State Oil', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(99, 8, 13, 'Hydrocarbon Development Institute of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(100, 8, 13, 'Pakistan LNG Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(101, 8, 13, 'Pakistan Petroleum Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(102, 8, 13, 'Pak Arab Refinery Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(103, 8, 13, 'Geological Survey of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(104, 8, 13, 'Inter State Gas Systems', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(105, 8, 13, 'Pakistan Mineral Development Corporation', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(106, 8, 13, 'Government Holdings (Private) Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(107, 8, 13, 'Saindak Metals Limited', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(108, 9, 14, 'Federal Education and Professional Training Division', 'C Block, pak secretariat, Islamabad', '051-9205433', NULL, 0, '2024-10-30 03:52:30', '2024-12-01 16:13:59'),
(109, 9, 14, 'Higher Education Commission', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(110, 9, 14, 'National Vocational and Technical Training Commission', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(111, 9, 14, 'Pakistan Institute of Eduction', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(112, 9, 14, 'Federal Directorate of Education', 'Rohtas Rd,  G 9/4, Islamabad', '0331-6333028', NULL, 0, '2024-10-30 03:52:30', '2024-12-01 16:15:01'),
(113, 9, 14, 'Inter Boards Coordination Commission', NULL, NULL, NULL, 0, '2024-10-30 03:52:30', '2024-10-30 03:52:30'),
(114, 9, 14, 'Federal Board of Intermediate and Secondary Education', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(115, 9, 14, 'Pakistan Boys Scouts Association', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(116, 9, 14, 'Directorate General of Special Education', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(117, 9, 14, 'National Book Foundation', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(118, 9, 14, 'NFC Institute of Engineering & Fertilizer Research', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(119, 9, 14, 'Directorate General of Religious Eduction', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(120, 9, 14, 'Pakistan Institute of Fashion and Design', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(121, 9, 14, 'National Rahmatul-lil-Alameen Wa Khatamun Nabiyyin Authority', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(122, 9, 14, 'National Commission for Human Development', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(123, 9, 14, 'Basic Education Community Schools', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(124, 9, 14, 'Pakistan Girls Guide Association', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(125, 9, 14, 'Private Educational Institutions Regulatory Authority (PEIRA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(126, 9, 14, 'Pakistan National Commission for UNESCO', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(127, 9, 14, 'National Education Assessment System', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(128, 9, 14, 'National Education Foundation', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(129, 9, 14, 'National College of Arts', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(130, 9, 14, 'Federal College of Education', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(131, 9, 14, 'Government Polytechnic Institute for Women', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(132, 9, 14, 'National Textile University (NTU), Faisalabad', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(133, 9, 14, 'NFC Institute of Engineering and Technology', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(134, 9, 14, 'Pakistan Education Endowment Fund', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(135, 9, 14, 'National Skills University', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(136, 9, 15, 'National Heritage & Cultural Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(137, 9, 15, 'Department of Archaeology and Museums', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(138, 9, 15, 'National Library Of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(139, 9, 15, 'National Language Promotion Department', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(140, 9, 15, 'Quaid-e-Azam Academy', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(141, 10, 16, 'Finance Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(142, 10, 16, 'Auditor General of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(143, 10, 16, 'Accountant General of Pakistan Revenue', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(144, 10, 16, 'Controller General of Accounts', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(145, 10, 16, 'Securities and Exchange Commission of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(146, 10, 16, 'Competition Commission of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(147, 10, 16, 'Central Directorate of National Savings', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(148, 10, 16, 'Financial Accounting & Budgeting System (FABS)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(149, 10, 17, 'Federal Board of Revenue', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(150, 11, 18, 'Foreign Affairs Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(151, 12, 19, 'Housing & Works Division', 'C-Block, Pak Secretariat, Islamabad', '051-9210263', NULL, 0, '2024-10-30 03:52:31', '2024-12-01 16:12:21'),
(152, 12, 19, 'Pakistan Housing Authority Foundation (PHAF)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(153, 12, 19, 'National Construction Limited (NCL)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(154, 12, 19, 'Pakistan Public Works Department (PWD)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(155, 12, 19, 'Estate Office Management (EOM)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(156, 12, 19, 'Federal Government Employees Housing Authority (FGEHA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(157, 12, 19, 'Pakistan Environmental Planning & Architectural Consultants (PEPAC) Ltd.', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(158, 13, 20, 'Human Rights Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(159, 14, 21, 'Industries & Production Division', 'D-Block, Pak Secretariat, Islamabad', '051-9204501', NULL, 0, '2024-10-30 03:52:31', '2024-12-01 15:53:33'),
(160, 14, 21, 'Engineering Development Board (EDB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(161, 14, 21, 'National Productivity Organization (NPO)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(162, 14, 21, 'Pakistan Stone Development Company (PASDEC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(163, 14, 21, 'National Fertilizer Marketing Limited (NFML)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(164, 14, 21, 'Small and Medium Enterprises Development Authority (SMEDA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(165, 14, 21, 'National Fertilizer Corporation (NFC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(166, 14, 21, 'Pakistan Steel Mills (PSM)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(167, 14, 21, 'Pakistan Institute of Management (PIM)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(168, 14, 21, 'Technology Upgradation and Skill Development Company (TUSDEC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(169, 14, 21, 'Gujranwala Business Centre (GBC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(170, 14, 21, 'Pakistan Industrial Development Corporation (PIDC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(171, 14, 21, 'Pakistan Gems and Jewelry Development Company (PGJDC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(172, 14, 21, 'State Engineering Corporation (SEC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(173, 14, 21, 'Export Processing Zones Authority (EPZA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(174, 14, 21, 'Pakistan Hunting and Sporting Arms Development Company (PHSADC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(175, 14, 21, 'Pakistan Industrial Technical Assistance Centre (PITAC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(176, 14, 21, 'Pakistan Engineering Company (PECO)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(177, 14, 21, 'Karachi Tools, Dies & Moulds Centre (KTDMC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(178, 14, 21, 'Aik Hunar Aik Nagar (AHAN)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(179, 15, 22, 'Information & Broadcasting Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(180, 15, 22, 'Press Information Department (PID)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(181, 15, 22, 'Directorate of Electronic Media & Publications (DEMP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(182, 15, 22, 'Pakistan Television Corporation (PTVC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(183, 15, 22, 'Pakistan Broadcasting Corporation (PBC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(184, 15, 22, 'Associated Press of Pakistan Corporation (APPC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(185, 15, 22, 'Pakistan Electronic Media Regulatory Authority (PEMRA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(186, 15, 22, 'Press Council of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(187, 15, 22, 'Shalimar Recording & Broadcasting Company', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(188, 15, 22, 'Information Service Academy (ISA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(189, 15, 22, 'Internal Publicity Wing (IP Wing)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(190, 15, 22, 'External Publicity Wing (EP Wing)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(191, 15, 22, 'Centre of Digital Communication (CDC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(192, 15, 22, 'National Press Trust', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(193, 15, 22, 'Press Registrar', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(194, 15, 22, 'Pakistan Information Commission', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(195, 15, 22, 'Central Board of Film Censors', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(196, 15, 22, 'Implementation Tribunal for Newspaper Employees', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(197, 16, 23, 'Information Technology & Telecommunication Division', '7th floor, Kohsar Block, Pak Secretariat, Islamabad', '0347-6311389', NULL, 0, '2024-10-30 03:52:31', '2024-12-01 15:52:22'),
(198, 16, 23, 'National Information Technology Board (NITB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(199, 17, 24, 'Interior Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(200, 17, 24, 'NADRA', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31'),
(201, 17, 24, 'Capital Development Authority (CDA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(202, 17, 24, 'Metropolitan Cooperation Islamabad', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(203, 17, 24, 'National Police Academy', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(204, 17, 24, 'Directorate General of Immigration & Passport', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(205, 17, 24, 'Pakistan Coast Guard', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(206, 17, 24, 'Directorate General of Civil Defense Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(207, 17, 24, 'National Counter Terrorism Authority (NACTA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(208, 17, 24, 'National Police Foundation', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(209, 17, 24, 'Islamabad Police', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(210, 17, 24, 'National Academy for Prisons Administration', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(211, 17, 24, 'ICT Administration', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(212, 17, 24, 'Federal Investigation Agency (FIA)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(213, 17, 24, 'National Police Bureau', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(214, 18, 25, 'Inter Provincial & Coordination Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(215, 18, 25, 'Pakistan Sports Board', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(216, 18, 25, 'National Internship Program', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(217, 18, 25, 'Gun and Country Club', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(218, 18, 25, 'Pakistan Veterinary Medical Council', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(219, 18, 25, 'Department of Tourist Services', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(220, 18, 25, 'Federal Land Commission', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(221, 19, 26, 'Kashmir Affairs and Gilgit Baltistan Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(222, 20, 27, 'Law & Justice Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(223, 20, 27, 'Anti Dumping Appellate Tribunal, Islamabad', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(224, 20, 27, 'Commercial Courts', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(225, 20, 27, 'Banking Court', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(226, 20, 27, 'Accountability Courts', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(227, 20, 27, 'Appellate Tribunal Inland Revenue', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(228, 20, 27, 'Customs Appellate Tribunal', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(229, 20, 27, 'Drug Courts', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(230, 20, 27, 'Competition Appellate Tribunal', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(231, 20, 27, 'Environmental Protection Tribunal', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(232, 21, 28, 'Maritime Affairs Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(233, 21, 28, 'Government Shipping Office', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(234, 21, 28, 'Pakistan Marine Academy', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(235, 21, 28, 'Pakistan National Shipping Corporation', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(236, 21, 28, 'Port Qasim Authority', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(237, 21, 28, 'Karachi Port Trust', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(238, 21, 28, 'Gawadar Port Authority', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(239, 21, 28, 'DG Port & Shipping Wing Karachi', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(240, 21, 28, 'Mercantile Marine Department', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(241, 21, 28, 'Korangi Fisheries Harbour Authority', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(242, 21, 28, 'Marine Fishries Department', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(243, 22, 29, 'Narcotics Control Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(244, 22, 29, 'Anti Narcotics Force Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(245, 23, 30, 'National Food Security and Research Division', '4th floor, Block B, Pak Secretariat, Islamabad', '051-9201270', NULL, 0, '2024-10-30 03:52:32', '2024-12-01 15:48:54'),
(246, 23, 30, 'Agriculture Policy Institute (API)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(247, 23, 30, 'Federal Seed Certification and Registration Department (FSC&RD)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(248, 23, 30, 'Department of Plant Protection (DPP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(249, 23, 30, 'Animal Quarantine Department (AQD)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(250, 23, 30, 'Plant Breeder Rights Registry (PBRR)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(251, 23, 30, 'Livestock and Dairy Development Board (LDDB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(252, 23, 30, 'Pakistan Agricultural Research Council (PARC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(253, 23, 30, 'Pakistan Central Cotton Committee (PCCC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(254, 23, 30, 'Pakistan Agricultural Storage & Services Corporation (PASSCO)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(255, 23, 30, 'National Veterinary Lab (NVL)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(256, 23, 30, 'Federal Water Management Cell (FWMC)', '69-East (1st floor), Adeel Plaza, Blue Area, Islamabad', '051-9245107', NULL, 0, '2024-10-30 03:52:32', '2024-12-01 15:49:34'),
(257, 23, 30, 'Pakistan Oilseed Department Board (PODB)', '10-D West, Taimoor Chamber, 2nd floor, Blue Area, Islamabad', '0300-9766970', NULL, 0, '2024-10-30 03:52:32', '2024-12-01 15:50:14'),
(258, 23, 30, 'Fisheries Development Board (FDB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(259, 23, 30, 'National Fertilizer Development Centre (NFDC)', 'Street No.1,Sector H-8/1,Islamabad', '051-9250480', NULL, 0, '2024-10-30 03:52:32', '2024-12-01 15:50:51'),
(260, 23, 30, 'Pakistan Tobacco Board', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(261, 24, 31, 'National Health Services Regulations and Coordination Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(262, 24, 31, 'Federal Medical and Dental College', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(263, 24, 31, 'Federal Directorate of Immunization', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(264, 24, 31, 'Federal Government Polyclinic Hospital', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(265, 24, 31, 'District Health Office', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(266, 24, 31, 'Common Management Unit (CMU)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(267, 24, 31, 'Drug Regulatory Authority of Pakistan (DRAP)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(268, 24, 31, 'Shaheed Zulfiqar Ali Bhutto Medical University (SZABMU)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(269, 24, 31, 'Islamabad Healthcare Regulatory Authority', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(270, 24, 31, 'National Institute of Health (NIH)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(271, 24, 31, 'Pakistan Institute of Medical Sciences', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(272, 24, 31, 'Pakistan Medical and Dental Council (PMDC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(273, 24, 31, 'National Council for Tibb', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(274, 24, 31, 'Pakistan Nursing and Midwifery Council (PN&MC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(275, 24, 31, 'Pharmacy Council of Pakistan', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(276, 24, 31, 'Special Investment Facilitation Council', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(277, 24, 31, 'National Health Emergency Prepardness Network', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(278, 24, 31, 'National Trust for Population Welfare (NATPOW)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(279, 24, 31, 'National Council of Homeopathy', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(280, 24, 31, 'Health Services Academy', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(281, 24, 31, 'Pakistan College of Physicians and Surgeons', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(282, 24, 31, 'National Institute of Population Studies (NIPS)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(283, 25, 32, 'Overseas Pakistanis and Human Resource Development Division', '5th & 6th floor, B Block, Pak Secretariat, Islamabad', '051-9213552', NULL, 0, '2024-10-30 03:52:32', '2024-12-01 15:47:43'),
(284, 25, 32, 'Overseas Pakistanis Foundation (OPF)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(285, 25, 32, 'Overseas Employment Corporation (OEC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(286, 25, 32, 'Employees Old-Age Benefits Institution (EOBI)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(287, 25, 32, 'Workers Welfare Fund (WWF)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(288, 25, 32, 'National Industrial Relations Commissions Islamabad (NIRC)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(289, 25, 32, 'Bureau Of Emigration & Overseas Employment (BE&OE)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(290, 25, 32, 'Directorate Of Workers Education (DWE)', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(291, 26, 33, 'Parliamentary Affairs Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:32', '2024-10-30 03:52:32'),
(292, 27, 34, 'Planning Development & Special Initiatives Division', NULL, NULL, NULL, 0, '2024-10-30 03:52:33', '2024-10-30 03:52:33'),
(293, 27, 34, 'Pakistan Bureau of Statistics', NULL, NULL, NULL, 0, '2024-10-30 03:52:33', '2024-10-30 03:52:33'),
(294, 27, 34, 'PAKISTAN PLANNING AND MANAGEMENT INSTITUTE (PPMI)', NULL, NULL, NULL, 0, '2024-10-30 03:52:33', '2024-10-30 03:52:33'),
(295, 27, 34, 'Pakistan Institute of Development Economics (PIDE)', NULL, NULL, NULL, 0, '2024-10-30 03:52:33', '2024-10-30 03:52:33'),
(296, 28, 35, 'Privitization Division', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(297, 29, 36, 'Railways Division', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(298, 29, 36, 'Islamabad Carriage Factory', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(299, 29, 36, 'Pakistan Railways Locomotive Factory (PLF), Rislapur', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(300, 29, 36, 'Pakistan Railways', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(301, 29, 36, 'Pakistan Railways Workshop Lahore', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(302, 30, 37, 'Religious Affairs and Inter-Faith Harmony Division', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(303, 30, 37, 'Directorate of Hajj, Islamabad', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(304, 30, 37, 'Directorate of Hajj, Karachi', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(305, 30, 37, 'Directorate of Hajj, Lahore', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(306, 30, 37, 'Directorate of Hajj, Peshawar', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(307, 30, 37, 'Directorate of Hajj, Quetta', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(308, 30, 37, 'Directorate of Hajj, Multan', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(309, 30, 37, 'Directorate of Hajj, Sukkur', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(310, 31, 38, 'Science & Technology Division', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(311, 31, 38, 'Pakistan Council of Scientific and Industrial Research (PCSIR)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(312, 31, 38, 'National Institute of Electronics (NIE)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(313, 31, 38, 'National Institute of Oceanography (NIO)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(314, 31, 38, 'Pakistan Council for Renewable Energy Technologies (PCRET)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(315, 31, 38, 'Council for Work and Housing Research (CWHR)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(316, 31, 38, 'Pakistan Science Foundation (PSF)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(317, 31, 38, 'Pakistan Council for Science and Technology (PCST)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(318, 31, 38, 'Pakistan Standards And Quality Control Authority (PSQCA)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(319, 31, 38, 'Pakistan National Accreditation Council (PNAC)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(320, 31, 38, 'National Meteorology Institute of Pakistan (NMIP)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(321, 31, 38, 'Pakistan Engineering Council (PEC)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(322, 31, 38, 'Pakistan Halal Authority (PHA)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(323, 31, 38, 'STEDEC Technology Commercialization Corporation of Pakistan (Private) Limited', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(324, 31, 38, 'National University of Science and Technology (NUST)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(325, 31, 38, 'CUI-Comsats University Islamabad', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(326, 31, 38, 'National University of Technology (NUTECH)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(327, 32, 39, 'States and Frontier Regions Division', 'S-Block, Pak Secretariat Islamabad', '051-9209914', NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(328, 32, 39, 'Chief Commissionerate for Afghan Refugees', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(329, 33, 40, 'Water Resources Division', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(330, 33, 40, 'Indus River System Authority', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(331, 33, 40, 'Pakistan Council of Researcg in Water Resources', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(332, 33, 40, 'Office of the Chief Engineering Advisor/Chairman Federal Food Commission', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(333, 33, 40, 'Pakistan Commissioner For Indus Waters', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(334, 33, 40, 'Water and Power Development Authority (WAPDA)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(335, 34, 41, 'Prime Minister Office (Public)', 'Prime Minister Office Islamabad', '051-9008270', NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(336, 34, 41, 'Board of Investment (BOI)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(337, 34, 42, 'Senate of Pakistan', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(338, 34, 42, 'National Assembly of Pakistan', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(339, 34, 42, 'Supreme Court of Pakistan', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(340, 34, 42, 'Federal Shariat Court', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(341, 34, 42, 'Islamabad High Court', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(342, 34, 42, 'Law & Justice Commission of Pakistan', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(343, 34, 42, 'Lahore High Court, Lahore', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(344, 34, 42, 'High Court of Sindh Karachi', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(345, 34, 42, 'High Court of Balochistan', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(346, 34, 42, 'Peshawar High Court Peshawar', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(347, 34, 42, 'Federal Service Tribunal', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(348, 34, 42, 'Federal Judicial Academy', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(349, 34, 42, 'President Secretariat', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(350, 34, 42, 'Election Commission of Pakistan', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(351, 34, 42, 'National Accountability Bureau', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(352, 34, 42, 'Intelligence Bureau', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(353, 34, 42, 'Ombudsman Secretariat', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(354, 34, 42, 'Council of Common Interests', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(355, 34, 42, 'Council of Islamic Ideology', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(356, 34, 42, 'Zarai Taraqiati Bank Limited (ZTBL)', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(357, 34, 42, 'Federal Tax Ombudsman', NULL, NULL, NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(358, 1, 3, 'Mangement Services Wing', '5th Floor Shaheed Milat Secretariat Islamabad', '051-9103640', NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(359, 3, 7, 'Pakistan Reinsurance Company Ltd', 'PRC Towers, 32-A, Lalazar Drive, M.T.Khan Road, Karachi', '021-99202919', NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(360, 3, 7, 'Afghan Transit Trade', 'Islamabad', '0315-8069801', NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(361, 3, 7, 'Lahore Garment City Company', 'Plot No 143-146 & 151-155, Sundar Industrial Estate, Raiwind, Lahore', '042-35297391', NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(362, 3, 7, 'Faisalabad Garment City Company', 'Value Addition City, 1-1/2 KM, Sahianwala Road, Khurrianwala, Faisalabad', '041-8507208', NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(363, 35, 43, 'President Secretariat', 'President Secretariat (Public), Aiwan-e-Sadr, Islamabad', '051-9010189', NULL, 0, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(364, 16, 23, 'Pakistan Software Export Board (PSEB)', NULL, NULL, NULL, 0, '2024-10-30 03:52:31', '2024-10-30 03:52:31');

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `id` bigint UNSIGNED NOT NULL,
  `ministry_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`id`, `ministry_id`, `name`, `isActive`, `created_at`, `updated_at`) VALUES
(1, 1, 'Aviation Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(2, 1, 'Cabinet Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(3, 1, 'Establishment Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(4, 1, 'National Security Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(5, 1, 'Poverty Alleviation & Social Safety Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(6, 2, 'Climate Change Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(7, 3, 'Commerce Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(8, 4, 'Communication Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(9, 5, 'Defence Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(10, 6, 'Defence Production Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(11, 7, 'Economic Affairs Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(12, 8, 'Power Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(13, 8, 'Petroleum Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(14, 9, 'Federal Education and Professional Training Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(15, 9, 'National Heritage & Cultural Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(16, 10, 'Finance Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(17, 10, 'Revenue Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(18, 11, 'Foreign Affairs Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(19, 12, 'Housing & Works Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(20, 13, 'Human Rights Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(21, 14, 'Industries & Production Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(22, 15, 'Information & Broadcasting Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(23, 16, 'Information Technology & Telecommunication Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(24, 17, 'Interior Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(25, 18, 'Inter Provincial & Coordination Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(26, 19, 'Kashmir Affairs and Gilgit Baltistan Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(27, 20, 'Law & Justice Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(28, 21, 'Maritime Affairs Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(29, 22, 'Narcotics Control Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(30, 23, 'National Food Security and Research Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(31, 24, 'National Health Services Regulations and Coordination Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(32, 25, 'Overseas Pakistanis and Human Resource Development Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(33, 26, 'Parliamentary Affairs Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(34, 27, 'Planning Development & Special Initiatives Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(35, 28, 'Privitization Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(36, 29, 'Railways Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(37, 30, 'Religious Affairs and Inter-Faith Harmony Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(38, 31, 'Science & Technology Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(39, 32, 'States and Frontier Regions Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(40, 33, 'Water Resources Division', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(41, 34, 'Prime Minister Office', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(42, 34, 'Miscellaneous', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(43, 35, 'President Secretariat', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30');

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
-- Table structure for table `financial_years`
--

CREATE TABLE `financial_years` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_of_revisions` int NOT NULL DEFAULT '0',
  `last_revised_date` date DEFAULT NULL,
  `added_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_years`
--

INSERT INTO `financial_years` (`id`, `name`, `no_of_revisions`, `last_revised_date`, `added_by_user_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '2010-2011', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(2, '2011-2012', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(3, '2012-2013', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(4, '2013-2014', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(5, '2014-2015', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(6, '2015-2016', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(7, '2016-2017', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(8, '2017-2018', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(9, '2018-2019', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(10, '2019-2020', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(11, '2020-2021', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(12, '2021-2022', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(13, '2022-2023', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(14, '2023-2024', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(15, '2024-2025', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(16, '2025-2026', 0, NULL, NULL, 1, '2025-07-28 02:03:31', '2025-07-28 02:03:31');

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
(4, '2025_07_02_124440_create_permission_tables', 1),
(5, '2025_07_02_124455_create_approving_authorities_table', 1),
(6, '2025_07_02_124500_create_minestries_table', 1),
(7, '2025_07_02_124502_create_statuses_table', 1),
(8, '2025_07_02_124505_create_divisions_table', 1),
(9, '2025_07_02_124508_create_departments_table', 1),
(10, '2025_07_02_124514_create_financial_years_table', 1),
(11, '2025_07_02_124524_create_projects_table', 1),
(12, '2025_07_02_124530_create_allocations_table', 1),
(13, '2025_07_02_124538_create_quarters_table', 1),
(14, '2025_07_02_124544_create_deliverables_table', 1),
(15, '2025_07_02_124552_create_tasks_table', 1),
(16, '2025_07_03_053052_create_user_ministries_table', 1),
(17, '2025_07_03_111033_create_revisions_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `minestries`
--

CREATE TABLE `minestries` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `minestries`
--

INSERT INTO `minestries` (`id`, `name`, `isActive`, `created_at`, `updated_at`) VALUES
(1, 'Cabinet Secretariat', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(2, 'Ministry of Climate Change', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(3, 'Ministry of Commerce', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(4, 'Ministry of Communications', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(5, 'Ministry of Defence', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(6, 'Ministry of Defence Production', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(7, 'Ministry of Economic Affairs', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(8, 'Ministry of Energy', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(9, 'Ministry of Federal Education, Professional Training, National Heritage & Culture', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(10, 'Ministry of Finance & Revenue', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(11, 'Ministry of Foreign Affairs', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(12, 'Ministry of Housing and Works', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(13, 'Ministry of Human Rights', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(14, 'Ministry of Industries and Production', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(15, 'Ministry of Information and Broadcasting', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(16, 'Ministry of Information Technology and Telecommunication', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(17, 'Ministry of Interior', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(18, 'Ministry of Inter-Provincial Coordination', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(19, 'Ministry of Kashmir Affairs and Gilgit Baltistan', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(20, 'Ministry of Law and Justice', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(21, 'Ministry of Maritime Affairs', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(22, 'Ministry of Narcotics Control', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(23, 'Ministry of National Food Security and Research', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(24, 'Ministry of National Health Services, Regulations and Coordination', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(25, 'Ministry of Overseas Pakistanis and Human Resource Development', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(26, 'Ministry of Parliamentary Affairs', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(27, 'Ministry of Planning, Development & Special Initiatives', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(28, 'Ministry of Privatization', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(29, 'Ministry of Railways', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(30, 'Ministry of Religious Affairs and Inter Faith Harmony', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(31, 'Ministry of Science and Technology', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(32, 'Ministry of States and Frontier Regions', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(33, 'Ministry of Water Resources', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(34, 'Prime Minister Office', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(35, 'President Secretariat', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30');

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4);

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
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view-projects', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(2, 'create-projects', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(3, 'edit-projects', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(4, 'delete-projects', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(5, 'manage-users', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(6, 'manage-roles', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(7, 'view-reports', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(8, 'export-data', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(9, 'manage-system-settings', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ministry_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `psdp_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `psdp_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approving_authority_id` bigint UNSIGNED NOT NULL,
  `authority_approved_date` date DEFAULT NULL,
  `admin_approval_date` date DEFAULT NULL,
  `completion_date_pc1` date DEFAULT NULL,
  `likely_completion_date` date DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT NULL COMMENT 'Total project cost',
  `grand_amount` decimal(15,2) DEFAULT NULL COMMENT 'Grand project cost',
  `lc_amount` decimal(15,2) DEFAULT NULL COMMENT 'Local Currency amount',
  `fc_amount` decimal(15,2) DEFAULT NULL COMMENT 'Foreign Currency amount',
  `is_revised` tinyint(1) NOT NULL DEFAULT '0',
  `no_of_revision` int NOT NULL DEFAULT '0',
  `last_revised_date` date DEFAULT NULL,
  `added_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_at_date` date DEFAULT NULL,
  `deleted_at_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `ministry_id`, `department_id`, `psdp_no`, `psdp_id`, `approving_authority_id`, `authority_approved_date`, `admin_approval_date`, `completion_date_pc1`, `likely_completion_date`, `total_cost`, `grand_amount`, `lc_amount`, `fc_amount`, `is_revised`, `no_of_revision`, `last_revised_date`, `added_by_user_id`, `is_active`, `created_at`, `updated_at`, `updated_at_date`, `deleted_at_date`) VALUES
(1, 'Smart office Federal Ministries & Departments PSDP through MoITT (NITB)', 16, 198, '657', 'PSDP-657-2025', 1, '2020-09-16', '2020-09-16', '2024-12-31', '2026-06-30', 572.80, 572.80, 572.80, 0.00, 0, 0, '2024-12-31', 3, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32', NULL, NULL),
(2, 'One Patient One ID (NITB)', 16, 198, '666', 'PSDP-666-2025', 1, '2020-04-06', '2020-04-06', '2025-06-30', '2026-06-30', 200.25, 200.25, 200.25, 0.00, 0, 0, '2025-06-30', 3, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32', NULL, NULL),
(3, 'Certification of IT Professionals (PSEB)', 16, 364, '658', 'PSDP-658-2025', 1, '2019-12-18', '2019-12-18', '2024-06-30', '2025-06-30', 901.25, 901.25, 901.25, 0.00, 0, 0, '2024-06-30', 4, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32', NULL, NULL),
(4, 'Establishment of 25 STPs in Pakistan Phase-I (PSEB)', 16, 364, '661', 'PSDP-661-2025', 1, '2021-03-31', '2021-03-31', '2024-11-25', '2025-06-30', 473.03, 473.03, 473.03, 0.00, 0, 0, '2024-11-25', 4, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quarters`
--

CREATE TABLE `quarters` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `allocation_id` bigint UNSIGNED NOT NULL,
  `quarter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `release_amount` int NOT NULL DEFAULT '0',
  `expense_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quarters`
--

INSERT INTO `quarters` (`id`, `project_id`, `allocation_id`, `quarter`, `release_amount`, `expense_amount`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Q1', 33, 33.17, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(2, 1, 1, 'Q2', 33, 33.17, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(3, 1, 1, 'Q3', 33, 33.17, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(4, 1, 1, 'Q4', 33, 33.17, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(5, 2, 2, 'Q1', 6, 5.00, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(6, 2, 2, 'Q2', 6, 5.00, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(7, 2, 2, 'Q3', 6, 5.00, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(8, 2, 2, 'Q4', 6, 5.00, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(9, 3, 3, 'Q1', 62, 60.66, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(10, 3, 3, 'Q2', 62, 60.66, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(11, 3, 3, 'Q3', 62, 60.66, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(12, 3, 3, 'Q4', 62, 60.66, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(13, 4, 4, 'Q1', 25, 23.75, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(14, 4, 4, 'Q2', 25, 23.75, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(15, 4, 4, 'Q3', 25, 23.75, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(16, 4, 4, 'Q4', 25, 23.75, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `revisions`
--

CREATE TABLE `revisions` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `authority_approved_date` date DEFAULT NULL,
  `approving_authority_id` bigint UNSIGNED NOT NULL,
  `admin_approval_date` date DEFAULT NULL,
  `revised_date` date NOT NULL,
  `lc_amount` decimal(15,2) DEFAULT NULL COMMENT 'Local Currency amount',
  `fc_amount` decimal(15,2) DEFAULT NULL COMMENT 'Foreign Currency amount',
  `additional_cost` decimal(15,2) DEFAULT NULL COMMENT 'Additional cost for this revision',
  `added_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super admin', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(2, 'admin', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(3, 'PD', 'web', '2025-07-28 02:03:30', '2025-07-28 02:03:30');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(7, 2),
(8, 2),
(1, 3),
(2, 3),
(3, 3),
(7, 3);

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
('JcX7vuQQPPjbR8qZ4U16KQZq8RKK1nBQu8iKjTZC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoid1RaeXpTdTVNb3pmRndWNjA4Y1BuV2ViOGhhMzY3T0I0TmZOQTJCSSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNToiaHR0cDovL2xvY2FsaG9zdDo4MDAwL3BzZHAvcHJvamVjdHMiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1753859621),
('s730ZL0T2epTfB6cNdXa5N5ihEgsoc2cvZbMO8CQ', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRUVDcVZQc1dER21zZk1mTkpKZ014TVdRZ0VReTFwUDh5U2RQZHQwdSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcHNkcC9wZC1wcm9qZWN0cyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1753775365),
('ZIld1soOK6QBYOuVxtVYzfmseMXiOASQHUHdJRBt', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMDM3TkhQM0lMblMzZlZ4Q3kwQ0pmTnpsRU9pRTVvcWZCNm9KanBuNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9wc2RwL2Rhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1753853763);

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Completed', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(2, 'Delayed', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(3, 'Not Started', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(4, 'Behind', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(5, 'On-track', NULL, '2025-07-28 02:03:30', '2025-07-28 02:03:30'),
(6, 'In Progress', NULL, '2025-07-28 02:03:32', '2025-07-28 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `deliverable_id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `planned_complete` decimal(5,2) NOT NULL DEFAULT '0.00',
  `actual_complete` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status_id` bigint UNSIGNED NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `deliverable_id`, `code`, `description`, `start_date`, `end_date`, `planned_complete`, `actual_complete`, `status_id`, `remarks`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '1.1', 'Complete project implementation as per PC-1', '2020-09-16', '2024-12-31', 100.00, 44.00, 6, 'Auto-generated from NITB/PSEB data', 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(2, 2, 2, '1.1', 'Complete project implementation as per PC-1', '2020-04-06', '2025-06-30', 100.00, 40.00, 6, 'Auto-generated from NITB/PSEB data', 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(3, 3, 3, '1.1', 'Complete project implementation as per PC-1', '2019-12-18', '2024-06-30', 100.00, 100.00, 6, 'Auto-generated from NITB/PSEB data', 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(4, 4, 4, '1.1', 'Complete project implementation as per PC-1', '2021-03-31', '2024-11-25', 100.00, 54.00, 6, 'Auto-generated from NITB/PSEB data', 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@moitt.gov.pk', '2025-07-28 02:03:31', '$2y$12$D8DDwa8t72b/xrt32wlU.ehdL2Ac1FsvxGBZuqh0wiVVkjFTn2JEi', NULL, '2025-07-28 02:03:31', '2025-07-28 02:03:31'),
(2, 'Admin User', 'admin@moitt.gov.pk', '2025-07-28 02:03:32', '$2y$12$Koeqyaq1HBIGH0WcpIUaweBK3bKtHSA66WME8gTNfWkYjutqFTY66', NULL, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(3, 'PD NITB', 'pd.nitb@moitt.gov.pk', '2025-07-28 02:03:32', '$2y$12$3eQtNXyMZrIm2cxA5p7B8uCizPnMO4OvD0X3lAi0o6V5ZZo.0qPAi', NULL, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(4, 'PD PSEB', 'pd.pseb@moitt.gov.pk', '2025-07-28 02:03:32', '$2y$12$0Hkt7DnuzIFzwwR8HZwBUORM3nGfpwKP/c0czyBIDHCQR0p2QxFf6', NULL, '2025-07-28 02:03:32', '2025-07-28 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_ministries`
--

CREATE TABLE `user_ministries` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `ministry_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_ministries`
--

INSERT INTO `user_ministries` (`id`, `user_id`, `ministry_id`, `department_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 16, NULL, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(2, 3, 16, 198, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32'),
(3, 4, 16, 364, 1, '2025-07-28 02:03:32', '2025-07-28 02:03:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocations`
--
ALTER TABLE `allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `allocations_project_id_foreign` (`project_id`),
  ADD KEY `allocations_financial_year_id_foreign` (`financial_year_id`);

--
-- Indexes for table `approving_authorities`
--
ALTER TABLE `approving_authorities`
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
-- Indexes for table `deliverables`
--
ALTER TABLE `deliverables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deliverables_project_id_foreign` (`project_id`),
  ADD KEY `deliverables_financial_year_id_foreign` (`financial_year_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `departments_ministry_id_foreign` (`ministry_id`),
  ADD KEY `departments_division_id_foreign` (`division_id`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `divisions_ministry_id_foreign` (`ministry_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `financial_years`
--
ALTER TABLE `financial_years`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financial_years_added_by_user_id_foreign` (`added_by_user_id`);

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
-- Indexes for table `minestries`
--
ALTER TABLE `minestries`
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
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projects_ministry_id_foreign` (`ministry_id`),
  ADD KEY `projects_department_id_foreign` (`department_id`),
  ADD KEY `projects_approving_authority_id_foreign` (`approving_authority_id`),
  ADD KEY `projects_added_by_user_id_foreign` (`added_by_user_id`);

--
-- Indexes for table `quarters`
--
ALTER TABLE `quarters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quarters_project_id_foreign` (`project_id`),
  ADD KEY `quarters_allocation_id_foreign` (`allocation_id`);

--
-- Indexes for table `revisions`
--
ALTER TABLE `revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `revisions_project_id_foreign` (`project_id`),
  ADD KEY `revisions_approving_authority_id_foreign` (`approving_authority_id`),
  ADD KEY `revisions_added_by_user_id_foreign` (`added_by_user_id`);

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
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_project_id_foreign` (`project_id`),
  ADD KEY `tasks_deliverable_id_foreign` (`deliverable_id`),
  ADD KEY `tasks_status_id_foreign` (`status_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_ministries`
--
ALTER TABLE `user_ministries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_ministries_user_id_ministry_id_department_id_unique` (`user_id`,`ministry_id`,`department_id`),
  ADD KEY `user_ministries_ministry_id_foreign` (`ministry_id`),
  ADD KEY `user_ministries_department_id_foreign` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocations`
--
ALTER TABLE `allocations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `approving_authorities`
--
ALTER TABLE `approving_authorities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `deliverables`
--
ALTER TABLE `deliverables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=365;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_years`
--
ALTER TABLE `financial_years`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `minestries`
--
ALTER TABLE `minestries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quarters`
--
ALTER TABLE `quarters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `revisions`
--
ALTER TABLE `revisions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_ministries`
--
ALTER TABLE `user_ministries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allocations`
--
ALTER TABLE `allocations`
  ADD CONSTRAINT `allocations_financial_year_id_foreign` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allocations_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deliverables`
--
ALTER TABLE `deliverables`
  ADD CONSTRAINT `deliverables_financial_year_id_foreign` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deliverables_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `departments_ministry_id_foreign` FOREIGN KEY (`ministry_id`) REFERENCES `minestries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_ministry_id_foreign` FOREIGN KEY (`ministry_id`) REFERENCES `minestries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `financial_years`
--
ALTER TABLE `financial_years`
  ADD CONSTRAINT `financial_years_added_by_user_id_foreign` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_added_by_user_id_foreign` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_approving_authority_id_foreign` FOREIGN KEY (`approving_authority_id`) REFERENCES `approving_authorities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ministry_id_foreign` FOREIGN KEY (`ministry_id`) REFERENCES `minestries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quarters`
--
ALTER TABLE `quarters`
  ADD CONSTRAINT `quarters_allocation_id_foreign` FOREIGN KEY (`allocation_id`) REFERENCES `allocations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quarters_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `revisions`
--
ALTER TABLE `revisions`
  ADD CONSTRAINT `revisions_added_by_user_id_foreign` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `revisions_approving_authority_id_foreign` FOREIGN KEY (`approving_authority_id`) REFERENCES `approving_authorities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revisions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_deliverable_id_foreign` FOREIGN KEY (`deliverable_id`) REFERENCES `deliverables` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_ministries`
--
ALTER TABLE `user_ministries`
  ADD CONSTRAINT `user_ministries_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_ministries_ministry_id_foreign` FOREIGN KEY (`ministry_id`) REFERENCES `minestries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_ministries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
