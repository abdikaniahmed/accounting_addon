-- database/install.sql
-- install.sql

-- database/install.sql

CREATE TABLE IF NOT EXISTS `acc_accounts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
  `code` VARCHAR(50) UNIQUE,
  `is_active` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS `acc_journal_entries` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `journal_number` VARCHAR(20) NOT NULL UNIQUE,
  `date` DATE NOT NULL,
  `reference` VARCHAR(255),
  `description` TEXT,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS `acc_journal_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `journal_entry_id` BIGINT UNSIGNED,
  `account_id` BIGINT UNSIGNED,
  `type` ENUM('debit', 'credit') NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  FOREIGN KEY (`journal_entry_id`) REFERENCES `acc_journal_entries`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `acc_accounts`(`id`) ON DELETE CASCADE
);
