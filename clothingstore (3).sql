-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 04, 2026 at 03:08 PM
-- Server version: 10.4.10-MariaDB
-- PHP Version: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clothingstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

DROP TABLE IF EXISTS `tbladmin`;
CREATE TABLE IF NOT EXISTS `tbladmin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`admin_id`, `email`, `password_hash`, `role`, `created_date`) VALUES
(1, 'admin1@pastimes.co.za', '16ab98f9a0273aa97963f6390327f648', 'Super Admin', '2026-05-04 13:17:33'),
(2, 'manager@pastimes.co.za', '062efa348b231eec41bdf91ebce445f5', 'Store Manager', '2026-05-04 13:19:00'),
(3, 'support@pastimes.co.za', '8839b48bc5994254f66a3c2ce8990446', 'Support Staff', '2026-05-04 13:19:43');

-- --------------------------------------------------------

--
-- Table structure for table `tblaorder`
--

DROP TABLE IF EXISTS `tblaorder`;
CREATE TABLE IF NOT EXISTS `tblaorder` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(11) NOT NULL,
  `clothes_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` date NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `buyer_id` (`buyer_id`),
  KEY `clothes_id` (`clothes_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tblaorder`
--

INSERT INTO `tblaorder` (`order_id`, `buyer_id`, `clothes_id`, `total_price`, `order_date`, `status`, `shipping_address`, `payment_method`) VALUES
(1, 7, 4, '700.00', '2026-05-04', 'Shipped', '48 stiemans street, johannesburg, Gauteng 2001', 'Bank Transfer'),
(2, 1, 9, '500.00', '2026-05-04', 'Processing', '41 stiemans street, johannesburg, Gauteng 2001', 'Bank Transfer'),
(3, 5, 8, '7000.00', '2026-05-04', 'Pending', '18 ocean view lane summersrand, Eastern Cape, Gqeberha 6001', 'Bank Transfer'),
(4, 3, 6, '350.00', '2026-05-04', 'Pending', '48 stiemans street, johannesburg, Gauteng 2001', 'Bank Transfer'),
(5, 2, 5, '150.00', '2026-05-04', 'Pending', '67 Madiba drive, Polokwane, Limpopo 0700', 'Bank Transfer'),
(6, 2, 7, '4500.00', '2026-05-04', 'Pending', '67 Madiba drive, Polokwane, Limpopo 0700', 'Bank Transfer');

-- --------------------------------------------------------

--
-- Table structure for table `tblclothes`
--

DROP TABLE IF EXISTS `tblclothes`;
CREATE TABLE IF NOT EXISTS `tblclothes` (
  `clothes_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('active','sold','pending') DEFAULT 'active',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`clothes_id`),
  KEY `seller_id` (`seller_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tblclothes`
--

INSERT INTO `tblclothes` (`clothes_id`, `item_name`, `description`, `size`, `color`, `category`, `price`, `seller_id`, `image_path`, `status`, `date_added`) VALUES
(2, 'Denim jacket', 'black denim in a better condition', 'XS', 'black', 'Jacket', '300.00', 1, 'images/1777901882_Black denim jacket.jpeg', 'active', '2026-05-04 13:38:02'),
(3, 'Denim  Jacket', 'denim in a good condition', 'L', 'blue', 'Jacket', '450.00', 1, 'images/1777901941_blue denim jacket.jpeg', 'active', '2026-05-04 13:39:01'),
(4, 'coat', 'faux fox coat in excellent condition', 'M', 'pink', 'Top', '700.00', 1, 'images/1777902037_faux fox pink coat.jpeg', 'sold', '2026-05-04 13:40:37'),
(5, 'jogger pants', 'ladies jogger pants still in good condition', 'XS', 'grey', 'Bottom', '150.00', 3, 'images/1777902558_jogger pants.jpeg', 'sold', '2026-05-04 13:49:18'),
(6, 'handbag', 'still in good condition', 'M', 'Tan', 'Accessory', '350.00', 7, 'images/1777903672_sunglasses pouch for handbags.jpeg', 'sold', '2026-05-04 14:07:52'),
(7, 'Britton squre lace up', 'Timberland still in good condition', '9', 'wheat', 'Shoes', '4500.00', 7, 'images/1777904497_Britton Square Lace-up shoes.jpeg', 'sold', '2026-05-04 14:21:37'),
(8, 'Laced Men shoes', 'Arbiter good for formal wear still in excellent condition', '10', 'Tan', 'Shoes', '7000.00', 7, 'images/1777904777_Arbiter Laced Mens Shoes.jpeg', 'sold', '2026-05-04 14:26:17'),
(9, 'Side bag', 'accesories bag still in great condition', 'XS', 'black', 'Accessory', '500.00', 7, 'images/1777904862_accesories bag.jpeg', 'sold', '2026-05-04 14:27:42'),
(10, 'floral summer dress', 'still in good condition', 'L', 'blue', 'Dress', '250.00', 7, 'images/1777904986_floral summer dress.jpeg', 'active', '2026-05-04 14:29:46'),
(11, 'Maxi skirt', 'silk material still in  very good condition', 'M', 'blue', 'Dress', '300.00', 7, 'images/1777905065_maxi skirt.jpeg', 'active', '2026-05-04 14:31:05'),
(12, 'puffer jacket', 'Alexander Wang  cropped puffer still in excellent condition', 'M', 'white', 'Jacket', '13000.00', 7, 'images/1777905398_WhatsApp Image 2026-04-30 at 1.52.50 AM.jpeg', 'active', '2026-05-04 14:36:38');

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

DROP TABLE IF EXISTS `tbluser`;
CREATE TABLE IF NOT EXISTS `tbluser` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`user_id`, `full_name`, `email`, `password_hash`, `address`, `phone`, `is_verified`, `registration_date`) VALUES
(1, 'Precious Moshiane Moloi', 'precioustumi38@gmail.com', 'cca22e0be434c32137eb4a8c13d81436', '100 jorrisen street,braamfontein', '0715109402', 1, '2026-05-04 13:21:12'),
(2, 'REBECCAH MAGANE', 'boitumelomagane95@gmail.com', 'a51259bcfc0205919421857ea1f76a76', '67 madiba drive  polokwane central Limpopo,0700', '0824517392', 1, '2026-05-04 13:28:31'),
(3, 'Thando Ranaka', 'thando13@gmail.com', '6b16e8236d7faa7fe604ac85c3186360', '5 kalahari avenue Upington Northen cape,8801', '0797665009', 1, '2026-05-04 13:29:54'),
(5, 'Wilson Smith', 'Wilson2@gmail.co.za', 'ad66bb2940bf7bc53ec0b5011232beaf', '18 Ocean view Lane Summersrand Gqenerha,6001 Eastern cape', '0719035561', 1, '2026-05-04 13:45:40'),
(6, 'Lerato moloi', 'lerato16@gmail.com', 'f677f8749c761e030ef5abad0a4d4c5d', '93 Juta street,braamfontein', '0829670934', 0, '2026-05-04 13:56:59'),
(7, 'Thando Thabethe', 'thando12@gmail.com', '6b16e8236d7faa7fe604ac85c3186360', '48 stiemans street braamfontein', '098948090', 1, '2026-05-04 14:03:41');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
