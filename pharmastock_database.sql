-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 03:35 PM
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
-- Database: `pharmastock`
--

-- --------------------------------------------------------

--
-- Table structure for table `bill`
--

CREATE TABLE `bill` (
  `bill_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_contact` varchar(15) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL CHECK (`total_amount` >= 0),
  `bill_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('Cash','Card','UPI','Other') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bill`
--

INSERT INTO `bill` (`bill_id`, `customer_name`, `customer_contact`, `total_amount`, `bill_date`, `payment_method`) VALUES
(5001, 'Arun Kumar', '9876543210', 350.25, '2025-03-16 05:00:00', 'Cash'),
(5002, 'Meera Sharma', '9823456789', 780.50, '2025-03-16 07:15:00', 'UPI'),
(5003, 'Ramesh Gupta', '9812345678', 1580.90, '2025-03-17 08:40:00', 'Card'),
(5004, 'Priya Menon', '9809876543', 520.25, '2025-03-17 03:50:00', 'Cash'),
(5005, 'Vikram Rao', '9787654321', 2450.00, '2025-03-18 12:20:00', 'UPI'),
(5006, 'Neha Verma', '9765432109', 950.30, '2025-03-18 06:10:00', 'Card'),
(5007, 'Sanjay Iyer', '9743210987', 2999.99, '2025-03-19 10:40:00', 'Cash'),
(5008, 'Kavita Nair', '9721098765', 410.45, '2025-03-19 08:05:00', 'UPI'),
(5009, 'Amit Patel', '9709876543', 850.00, '2025-03-20 12:50:00', 'Card'),
(5010, 'Deepak Mishra', '9687654321', 1520.75, '2025-03-20 10:25:00', 'Cash'),
(5011, 'Ayesha Khan', '9654321098', 230.60, '2025-03-21 05:15:00', 'UPI'),
(5012, 'Rahul Das', '9632109876', 1750.00, '2025-03-21 09:00:00', 'Card'),
(5013, 'Simran Kaur', '9610987654', 680.45, '2025-03-22 10:40:00', 'Cash'),
(5014, 'Aditya Narayan', '9598765432', 1900.75, '2025-03-22 06:55:00', 'UPI'),
(5015, 'Madhavi Reddy', '9587654321', 1350.25, '2025-03-23 12:10:00', 'Card'),
(5016, 'Dinesh Babu', '9576543210', 490.30, '2025-03-23 06:25:00', 'Cash'),
(5017, 'Farhan Shaikh', '9565432109', 800.99, '2025-03-24 08:50:00', 'UPI'),
(5018, 'Ritika Sharma', '9554321098', 220.45, '2025-03-24 07:40:00', 'Card'),
(5019, 'Ganesh Iyer', '9543210987', 1750.00, '2025-03-25 13:15:00', 'Cash'),
(5020, 'Pooja Agarwal', '9532109876', 920.60, '2025-03-25 07:05:00', 'UPI'),
(5021, 'jhon', '9879876543', 360.00, '2025-03-28 20:26:23', 'Cash'),
(5022, 'aliya', '9879876543', 235.25, '2025-03-29 01:02:22', 'Cash'),
(5023, 'aliya', '9879876543', 351.00, '2025-07-12 09:46:20', 'Cash');

-- --------------------------------------------------------

--
-- Table structure for table `billdetails`
--

CREATE TABLE `billdetails` (
  `bill_detail_id` int(11) NOT NULL,
  `bill_no` int(11) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `batch_no` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billdetails`
--

INSERT INTO `billdetails` (`bill_detail_id`, `bill_no`, `medicine_id`, `batch_no`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 5001, 1001, 'BATCH001', 2, 15.50, 31.00),
(2, 5001, 1005, 'BATCH005', 3, 22.75, 68.25),
(3, 5002, 1003, 'BATCH003', 1, 110.00, 110.00),
(4, 5002, 1007, 'BATCH007', 2, 335.25, 670.50),
(5, 5003, 1002, 'BATCH002', 5, 275.00, 1375.00),
(6, 5003, 1006, 'BATCH006', 2, 102.95, 205.90),
(7, 5004, 1004, 'BATCH004', 4, 120.50, 482.00),
(8, 5005, 1009, 'BATCH009', 7, 350.00, 2450.00),
(9, 5006, 1008, 'BATCH008', 3, 316.77, 950.31),
(10, 5007, 1010, 'BATCH010', 6, 499.99, 2999.94),
(11, 5008, 1001, 'BATCH001', 1, 15.50, 15.50),
(12, 5008, 1003, 'BATCH003', 2, 110.00, 220.00),
(13, 5008, 1006, 'BATCH006', 1, 102.95, 102.95),
(14, 5009, 1005, 'BATCH005', 4, 22.75, 91.00),
(15, 5010, 1002, 'BATCH002', 2, 275.00, 550.00),
(16, 5010, 1004, 'BATCH004', 1, 120.50, 120.50),
(17, 5010, 1007, 'BATCH007', 3, 335.25, 1005.75),
(18, 5011, 1009, 'BATCH009', 1, 350.00, 350.00),
(19, 5012, 1008, 'BATCH008', 1, 316.77, 316.77),
(20, 5013, 1010, 'BATCH010', 2, 499.99, 999.98),
(21, 5014, 1006, 'BATCH006', 4, 102.95, 411.80),
(22, 5015, 1003, 'BATCH003', 3, 110.00, 330.00),
(23, 5015, 1005, 'BATCH005', 3, 22.75, 68.25),
(24, 5015, 1007, 'BATCH007', 1, 335.25, 335.25),
(25, 5016, 1002, 'BATCH002', 2, 275.00, 550.00),
(26, 5017, 1004, 'BATCH004', 2, 120.50, 241.00),
(27, 5018, 1009, 'BATCH009', 1, 350.00, 350.00),
(28, 5018, 1010, 'BATCH010', 1, 499.99, 499.99),
(29, 5019, 1006, 'BATCH006', 3, 102.95, 308.85),
(30, 5020, 1008, 'BATCH008', 2, 316.77, 633.54),
(31, 5021, 1004, 'B1004', 3, 120.00, 360.00),
(32, 5022, 1001, 'B1001', 5, 25.75, 128.75),
(33, 5022, 1002, 'B1002', 3, 35.50, 106.50),
(34, 5023, 1003, '1003', 5, 48.90, 244.50),
(35, 5023, 1002, 'B1002', 3, 35.50, 106.50);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `bill_id`, `medicine_id`, `quantity_sold`, `total_price`, `sale_date`) VALUES
(6001, 5001, 1001, 2, 158.00, '2025-03-16 04:45:00'),
(6002, 5001, 1005, 1, 78.00, '2025-03-16 04:45:00'),
(6003, 5002, 1010, 3, 342.00, '2025-03-17 06:00:00'),
(6004, 5003, 1002, 1, 110.00, '2025-03-18 06:30:00'),
(6005, 5003, 1007, 4, 520.00, '2025-03-18 06:30:00'),
(6006, 5004, 1012, 2, 290.00, '2025-03-19 09:15:00'),
(6007, 5005, 1004, 1, 150.00, '2025-03-20 03:50:00'),
(6008, 5006, 1006, 3, 375.00, '2025-03-21 05:20:00'),
(6009, 5007, 1014, 5, 725.00, '2025-03-22 07:45:00'),
(6010, 5008, 1011, 2, 256.00, '2025-03-23 09:30:00'),
(6011, 5009, 1003, 2, 240.00, '2025-03-24 05:30:00'),
(6012, 5010, 1008, 1, 90.00, '2025-03-25 11:00:00'),
(6013, 5011, 1013, 4, 648.00, '2025-03-26 12:10:00'),
(6014, 5012, 1009, 1, 134.00, '2025-03-27 13:20:00'),
(6015, 5013, 1015, 3, 510.00, '2025-03-28 04:35:00'),
(6016, 5014, 1001, 2, 158.00, '2025-03-28 05:50:00'),
(6021, 5021, 1004, 3, 360.00, '2025-03-28 20:26:23'),
(6022, 5022, 1002, 3, 106.50, '2025-03-29 01:02:22'),
(6023, 5023, 1002, 3, 106.50, '2025-07-12 09:46:20');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `batch_no` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` >= 0),
  `price_per_unit` decimal(10,2) NOT NULL CHECK (`price_per_unit` >= 0),
  `manufacture_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `supplier_name` varchar(255) DEFAULT NULL,
  `supplier_contact` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`medicine_id`, `medicine_name`, `batch_no`, `quantity`, `price_per_unit`, `manufacture_date`, `expiry_date`, `added_date`, `supplier_name`, `supplier_contact`) VALUES
(1001, 'Paracetamol 650mg', 'B1001', 95, 25.75, '2024-01-01', '2026-01-01', '2025-02-28 18:30:00', 'XYZ Pharma', '9876543210'),
(1002, 'Ibuprofen 400mg', 'B1002', 74, 35.50, '2024-02-15', '2026-02-15', '2025-03-01 18:30:00', 'ABC Pharma', '9876543211'),
(1003, 'Amoxicillin 500mg', '1003', 45, 48.90, '2024-03-20', '2026-03-20', '2025-03-02 18:30:00', 'MedLife Ltd', '9876543212'),
(1004, 'Dextromethorphan Hydrobromide Syrup', 'B1004', 57, 120.00, '2024-04-10', '2025-12-10', '2025-03-03 18:30:00', 'HealthCare Ltd.', '9876543213'),
(1005, 'Levocetirizine 5mg', 'B1005', 90, 18.00, '2024-05-05', '2026-05-05', '2025-03-04 18:30:00', 'XYZ Pharma', '9876543210'),
(1006, 'Metformin Hydrochloride 1000mg', 'B1006', 75, 56.25, '2024-06-15', '2026-06-15', '2025-03-05 18:30:00', 'ABC Pharma', '9876543211'),
(1007, 'Azithromycin 500mg', 'B1007', 65, 78.90, '2024-07-25', '2026-07-25', '2025-03-06 18:30:00', 'MedLife Ltd.', '9876543212'),
(1008, 'Pantoprazole Sodium 40mg', 'B1008', 55, 38.50, '2024-08-10', '2026-08-10', '2025-03-07 18:30:00', 'HealthCare Ltd.', '9876543213'),
(1009, 'Losartan Potassium 50mg', 'B1009', 95, 24.80, '2024-09-05', '2026-09-05', '2025-03-08 18:30:00', 'XYZ Pharma', '9876543210'),
(1010, 'Thyroxine Sodium 100mcg', 'B1010', 110, 89.99, '2024-10-15', '2026-10-15', '2025-03-09 18:30:00', 'ABC Pharma', '9876543211'),
(1011, 'Tramadol Hydrochloride 50mg', 'B1011', 20, 55.20, '2022-06-10', '2024-03-01', '2025-03-10 18:30:00', 'MedLife Ltd.', '9876543212'),
(1012, 'Cefuroxime Axetil 500mg', 'B1012', 15, 120.00, '2021-08-20', '2023-12-20', '2025-03-11 18:30:00', 'HealthCare Ltd.', '9876543213'),
(1013, 'Dextromethorphan - Phenylephrine Syrup', 'B1013', 10, 250.00, '2022-11-15', '2024-01-10', '2025-03-12 18:30:00', 'XYZ Pharma', '9876543210'),
(1014, 'Multivitamin - Zinc - Iron Tablets', 'B1014', 5, 350.75, '2021-05-01', '2024-02-15', '2025-03-13 18:30:00', 'ABC Pharma', '9876543211'),
(1015, 'Ascorbic Acid Vitamin C 500mg', 'B1015', 45, 22.80, '2024-02-10', '2026-02-10', '2025-03-14 18:30:00', 'MedLife Ltd.', '9876543212'),
(1016, 'Zinc Sulfate 220mg', 'B1016', 35, 98.50, '2024-01-25', '2025-11-25', '2025-03-15 18:30:00', 'HealthCare Ltd.', '9876543213'),
(1017, 'Ferrous Sulfate Iron Syrup', 'B1017', 40, 180.00, '2024-03-10', '2025-10-10', '2025-03-16 18:30:00', 'XYZ Pharma', '9876543210'),
(1018, 'Calcium Carbonate - Vitamin D3', 'B1018', 30, 250.00, '2024-04-20', '2025-12-20', '2025-03-17 18:30:00', 'ABC Pharma', '9876543211'),
(1019, 'Folic Acid 5mg', 'B1019', 50, 19.90, '2024-05-15', '2025-09-15', '2025-03-18 18:30:00', 'MedLife Ltd.', '9876543212'),
(1020, 'Cholecalciferol -Vitamin D3 60000 IU', 'B1020', 48, 150.00, '2024-06-05', '2025-08-05', '2025-03-19 18:30:00', 'HealthCare Ltd.', '9876543213'),
(1021, 'Diclofenac Sodium Gel', 'DSG202403B', 80, 25.00, '2024-03-05', '2026-02-28', '2025-03-28 20:25:13', 'MediCare Pharma', '9988776655'),
(1022, 'dolo65', '1234', 90, 89.00, '2025-03-13', '2025-04-19', '2025-03-29 00:59:00', 'tra', '6234567898');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userid` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userid`, `name`, `email`, `password`) VALUES
('08', 'aliya', 'aliya@gmail.com', '$2y$10$C10/5CzsdlEbKPPPoDHh0OFiezXDtwpvdNDKYGQGKqLI0zu/1LFCC'),
('09', 'amna', 'amna@gmail.com', '$2y$10$jPORpMy7hYW0ucEyt.k0WeiBpfOj8izuSqQz/H9EmntEYNOdnkfDu'),
('26', 'ifra ara', 'iffu@gmail.com', '$2y$10$vbqlNBi.x/Xi3FTi335gk.WlJ.KTAwrQXL8RyMx6/A0mSNCXkE.5G'),
('admin101', 'guest', 'guest@gmail.com', '$2y$10$UsFSXic5uSsMxq4X9V88CuHEao8RFCmokWib5LHGnV6SvM9qIhUCO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bill`
--
ALTER TABLE `bill`
  ADD PRIMARY KEY (`bill_id`);

--
-- Indexes for table `billdetails`
--
ALTER TABLE `billdetails`
  ADD PRIMARY KEY (`bill_detail_id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `fk_billdetails_bill` (`bill_no`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`medicine_id`),
  ADD UNIQUE KEY `batch_no` (`batch_no`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bill`
--
ALTER TABLE `bill`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5024;

--
-- AUTO_INCREMENT for table `billdetails`
--
ALTER TABLE `billdetails`
  MODIFY `bill_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6024;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1023;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billdetails`
--
ALTER TABLE `billdetails`
  ADD CONSTRAINT `billdetails_ibfk_1` FOREIGN KEY (`bill_no`) REFERENCES `bill` (`bill_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `billdetails_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `stock` (`medicine_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_billdetails_bill` FOREIGN KEY (`bill_no`) REFERENCES `bill` (`bill_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bill` (`bill_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `stock` (`medicine_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
