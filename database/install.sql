-- Table: acc_account_groups
CREATE TABLE IF NOT EXISTS `acc_account_groups` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: acc_accounts
CREATE TABLE IF NOT EXISTS `acc_accounts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
  `code` VARCHAR(50) UNIQUE DEFAULT NULL,
  `account_group_id` BIGINT UNSIGNED DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`account_group_id`) REFERENCES `acc_account_groups`(`id`) ON DELETE SET NULL,
  INDEX `idx_account_group_id` (`account_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: acc_journal_entries
CREATE TABLE IF NOT EXISTS `acc_journal_entries` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `journal_number` VARCHAR(20) NOT NULL UNIQUE,
  `date` DATE NOT NULL,
  `reference` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: acc_journal_items
CREATE TABLE IF NOT EXISTS `acc_journal_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `journal_entry_id` BIGINT UNSIGNED DEFAULT NULL,
  `account_id` BIGINT UNSIGNED DEFAULT NULL,
  `type` ENUM('debit', 'credit') NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`journal_entry_id`) REFERENCES `acc_journal_entries`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `acc_accounts`(`id`) ON DELETE CASCADE,
  INDEX `idx_journal_entry_id` (`journal_entry_id`),
  INDEX `idx_account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `acc_bank_accounts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `account_id` BIGINT UNSIGNED NOT NULL,
  `bank_name` VARCHAR(255) NOT NULL,
  `account_number` VARCHAR(255) DEFAULT NULL,
  `holder_name` VARCHAR(255) DEFAULT NULL,
  `contact_number` VARCHAR(255) DEFAULT NULL,
  `bank_branch` VARCHAR(255) DEFAULT NULL,
  `opening_balance` DECIMAL(20, 2) DEFAULT 0.00,
  `current_balance` DECIMAL(20, 2) DEFAULT 0.00,
  `address` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`account_id`) REFERENCES `acc_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
