-- Database Schema & Demo Data for RTGS Analytics

SET FOREIGN_KEY_CHECKS = 0;

-- 1. ROLES
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Admin'),
(2, 'Analyst'),
(3, 'Auditor');

-- 2. USERS
-- Default admin user: admin / admin123
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `username`, `password_hash`, `role_id`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- 3. AUDIT SESSIONS
DROP TABLE IF EXISTS `audit_sessions`;
CREATE TABLE `audit_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT 'Nouvelle Session',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `audit_sessions` (`id`, `user_id`, `name`, `created_at`) VALUES
(1, 1, 'Initial Import', DATE_SUB(NOW(), INTERVAL 7 DAY));

-- 4. LOGS (User & ETL)
DROP TABLE IF EXISTS `user_logs`;
CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `etl_logs`;
CREATE TABLE `etl_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('SUCCESS','ERROR','WARNING') NOT NULL,
  `message` text DEFAULT NULL,
  `records_processed` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sql_imports`;
CREATE TABLE `sql_imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `file_content` longtext DEFAULT NULL,
  `executed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. RTGS TRANSACTIONS (Core Table)
DROP TABLE IF EXISTS `sprtgs_transactions`;
CREATE TABLE `sprtgs_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `chrono` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'DZD',
  `dco` date DEFAULT NULL,
  `trans_type` varchar(10) DEFAULT NULL,
  `sender` varchar(100) DEFAULT NULL,
  `beneficiary` varchar(100) DEFAULT NULL,
  `agency` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `dco` (`dco`),
  KEY `trans_type` (`trans_type`),
  CONSTRAINT `sprtgs_transactions_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `audit_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. MESSAGE LOGS (For Linking Status)
DROP TABLE IF EXISTS `rtgs_message_log`;
CREATE TABLE `rtgs_message_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `status` varchar(10) DEFAULT 'ACK',
  `raw_content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 7. DUMMY DATA FOR TRANSACTIONS
INSERT INTO `sprtgs_transactions` (session_id, reference, chrono, amount, dco, trans_type, sender, beneficiary, agency) VALUES
(1, 'REF001', 'CHRONO001', 5000000.00, DATE_SUB(NOW(), INTERVAL 1 DAY), 'MT103', 'BANQUE A', 'CLIENT X', 'AGENCE CENTRALE'),
(1, 'REF002', 'CHRONO002', 12500000.50, DATE_SUB(NOW(), INTERVAL 1 DAY), 'MT202', 'BANQUE B', 'BANQUE C', 'SIEGE'),
(1, 'REF003', 'CHRONO003', 750000.00, DATE_SUB(NOW(), INTERVAL 2 DAY), 'MT103', 'BANQUE A', 'CLIENT Y', 'AGENCE OUEST'),
(1, 'REF004', 'CHRONO004', 3000000.00, DATE_SUB(NOW(), INTERVAL 2 DAY), 'MT103', 'BANQUE C', 'CLIENT Z', 'AGENCE EST'),
(1, 'REF005', 'CHRONO005', 98000000.00, DATE_SUB(NOW(), INTERVAL 3 DAY), 'MT202', 'TRESO', 'BANQUE A', 'ALGER'),
(1, 'REF006', 'CHRONO006', 45000.00, DATE_SUB(NOW(), INTERVAL 3 DAY), 'MT103', 'BANQUE B', 'CLIENT X', 'AGENCE SUD'),
(1, 'REF007', 'CHRONO007', 1200000.00, DATE_SUB(NOW(), INTERVAL 4 DAY), 'MT103', 'BANQUE A', 'CLIENT W', 'AGENCE NORD'),
(1, 'REF008', 'CHRONO008', 6700000.00, DATE_SUB(NOW(), INTERVAL 5 DAY), 'MT202', 'BANQUE C', 'BANQUE B', 'SIEGE');

-- 8. DUMMY DATA FOR LOGS
INSERT INTO `rtgs_message_log` (session_id, reference, status, raw_content) VALUES
(1, 'REF001', 'ACK', '{1:F01...}{2:I103...}'),
(1, 'REF002', 'ACK', '{1:F01...}{2:I202...}'),
(1, 'REF003', 'NAK', '{1:F01...}{2:I103...}'),
(1, 'REF004', 'ACK', '{1:F01...}{2:I103...}'),
(1, 'REF005', 'ACK', '{1:F01...}{2:I202...}'),
(1, 'REF006', 'ACK', '{1:F01...}{2:I103...}'),
(1, 'REF007', 'ACK', '{1:F01...}{2:I103...}'),
(1, 'REF008', 'ACK', '{1:F01...}{2:I202...}');

SET FOREIGN_KEY_CHECKS = 1;
