-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 02, 2021 at 01:57 PM
-- Server version: 10.1.44-MariaDB
-- PHP Version: 7.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_category`
--

CREATE TABLE `{prefix}_category` (
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) DEFAULT 0,
  `topic` varchar(150) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `{prefix}_category`
--

INSERT INTO `{prefix}_category` (`type`, `category_id`, `topic`) VALUES
('category_id', 1, 'จอมอนิเตอร์'),
('category_id', 2, 'Notebook'),
('category_id', 3, 'อุปกรณ์คอมพิวเตอร์');

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_customer`
--

CREATE TABLE `{prefix}_customer` (
  `id` int(11) NOT NULL,
  `company` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `branch` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `idcard` varchar(13) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tax_id` varchar(13) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `provinceID` smallint(3) UNSIGNED NOT NULL,
  `province` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_no` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `{prefix}_customer`
--

INSERT INTO `{prefix}_customer` (`id`, `company`, `branch`, `name`, `idcard`, `tax_id`, `phone`, `fax`, `email`, `address`, `provinceID`, `province`, `zipcode`, `country`, `website`, `bank`, `bank_name`, `bank_no`, `discount`) VALUES
(1, 'ทดสอบ คู่ค้า', '', '', '', '', '03412345678', '', '', '123/45 อ.เมือง', 103, 'กาญจนบุรี', '71000', 'TH', '', '', '', '', 10),
(2, 'ทดสอบ ลูกค้า', '', '', '', '', '03412456', '', '', '', 102, 'กรุงเทพมหานคร', '10000', 'TH', '', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_inventory`
--

CREATE TABLE `{prefix}_inventory` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `topic` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cost` float DEFAULT 0,
  `vat` float DEFAULT 0,
  `stock` float DEFAULT 0,
  `count_stock` tinyint(1) DEFAULT 1,
  `inuse` tinyint(1) NOT NULL DEFAULT 1,
  `create_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `{prefix}_inventory`
--

INSERT INTO `{prefix}_inventory` (`id`, `topic`, `create_date`, `cost`, `vat`, `count_stock`, `category_id`, `inuse`, `stock`) VALUES
(1, 'จอมอนิเตอร์ ACER S220HQLEBD', '2018-08-28', 3500, 2, 1, 1, 1, 5),
(2, 'ASUS A550JX', '2018-08-28', 25000, 0, 1, 2, 1, 2),
(3, 'Crucial 4GB DDR3L&amp;1600 SODIMM', '2018-08-28', 500, 0, 1, 3, 1, 10);

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_inventory_items`
--

CREATE TABLE `{prefix}_inventory_items` (
  `product_no` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `topic` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `price` float DEFAULT 0,
  `cut_stock` float DEFAULT 1,
  `unit` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instock` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `{prefix}_inventory_items`
--

INSERT INTO `{prefix}_inventory_items` (`product_no`, `inventory_id`, `topic`, `price`, `cut_stock`, `unit`, `instock`) VALUES
('S220HQLEBD', 1, '', 4150, 1, 'เครื่อง', 1),
('A550JX', 2, '', 29500, 1, 'เครื่อง', 1),
('IF111/036/1', 3, '', 790, 1, 'ชิ้น', 1);

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_inventory_meta`
--

CREATE TABLE `{prefix}_inventory_meta` (
  `inventory_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `value` TEXT NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_language`
--

CREATE TABLE `{prefix}_language` (
  `id` int(11) NOT NULL,
  `key` text COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `owner` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `js` tinyint(1) NOT NULL,
  `th` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `en` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_number`
--

CREATE TABLE `{prefix}_number` (
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `auto_increment` int(11) NOT NULL,
  `last_update` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_orders`
--

CREATE TABLE `{prefix}_orders` (
  `id` int(11) NOT NULL,
  `order_no` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `member_id` int(11) UNSIGNED NOT NULL,
  `discount` float NOT NULL,
  `vat` float NOT NULL,
  `tax` float NOT NULL,
  `total` float NOT NULL,
  `status` enum('IN','OUT','PO','QUO','RET') COLLATE utf8_unicode_ci,
  `paid` float NOT NULL,
  `discount_percent` float NOT NULL,
  `tax_status` float NOT NULL,
  `vat_status` tinyint(1) NOT NULL,
  `order` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_stock`
--

CREATE TABLE `{prefix}_stock` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `inventory_id` int(11),
  `product_no` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('IN','OUT','PO','QUO','RET') COLLATE utf8_unicode_ci,
  `create_date` datetime NOT NULL,
  `topic` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` float DEFAULT 0,
  `cut_stock` float DEFAULT NULL,
  `unit` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `used` float NOT NULL,
  `price` float NOT NULL,
  `vat` float NOT NULL,
  `discount` float NOT NULL,
  `total` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `{prefix}_stock`
--

INSERT INTO `{prefix}_stock` (`id`, `order_id`, `member_id`, `inventory_id`, `product_no`, `status`, `create_date`, `topic`, `quantity`, `cut_stock`, `unit`, `used`, `price`, `vat`, `discount`, `total`) VALUES
(1, 0, 1, 1, 'S220HQLEBD', 'IN', '2021-02-13 00:00:00', '1108-365D จอมอนิเตอร์ ACER S220HQLEBD', 5, 1, 'เครื่อง', 0, 3500, 0, 0, 17500),
(2, 0, 1, 2, 'A550JX', 'IN', '2021-02-13 00:00:00', 'A550JX ASUS A550JX', 2, 1, 'เครื่อง', 0, 25000, 0, 0, 50000),
(3, 0, 1, 3, 'IF111/036/1', 'IN', '2021-02-13 00:00:00', 'IF111/036/1 Crucial 4GB DDR3L&amp;1600 SODIMM', 10, 1, 'ชิ้น', 0, 500, 0, 0, 5000);

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_user`
--

CREATE TABLE `{prefix}_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `permission` text COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `sex` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_card` varchar(13) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `provinceID` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `province` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci DEFAULT 'TH',
  `visited` int(11) DEFAULT 0,
  `lastvisited` int(11) DEFAULT 0,
  `session_id` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `social` tinyint(1) DEFAULT 0,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `{prefix}_category`
--
ALTER TABLE `{prefix}_category`
  ADD KEY `type` (`type`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `{prefix}_customer`
--
ALTER TABLE `{prefix}_customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `{prefix}_inventory`
--
ALTER TABLE `{prefix}_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `count_stock` (`count_stock`);

--
-- Indexes for table `{prefix}_inventory_items`
--
ALTER TABLE `{prefix}_inventory_items`
  ADD PRIMARY KEY (`product_no`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `{prefix}_inventory_meta`
--
ALTER TABLE `{prefix}_inventory_meta`
  ADD KEY `inventory_id` (`inventory_id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `{prefix}_language`
--
ALTER TABLE `{prefix}_language`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `{prefix}_number`
--
ALTER TABLE `{prefix}_number`
  ADD PRIMARY KEY (`type`);

--
-- Indexes for table `{prefix}_orders`
--
ALTER TABLE `{prefix}_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `{prefix}_stock`
--
ALTER TABLE `{prefix}_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`order_id`),
  ADD KEY `status` (`status`),
  ADD KEY `product_no` (`product_no`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `{prefix}_user`
--
ALTER TABLE `{prefix}_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`),
  ADD KEY `token` (`token`),
  ADD KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for table `{prefix}_customer`
--
ALTER TABLE `{prefix}_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `{prefix}_inventory`
--
ALTER TABLE `{prefix}_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `{prefix}_language`
--
ALTER TABLE `{prefix}_language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `{prefix}_orders`
--
ALTER TABLE `{prefix}_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `{prefix}_stock`
--
ALTER TABLE `{prefix}_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `{prefix}_user`
--
ALTER TABLE `{prefix}_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
