-- =====================================================
-- BukuKas Universal — Database Schema
-- MySQL 8.4+ / MariaDB 10.6+
-- Run: mysql -u root -p < database/schema.sql
-- =====================================================

CREATE DATABASE IF NOT EXISTS bukukas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bukukas;

-- =====================================================
-- USERS
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(100)        NOT NULL,
  email        VARCHAR(150)        NOT NULL UNIQUE,
  password     VARCHAR(255)        NOT NULL,
  role         ENUM('admin','user') NOT NULL DEFAULT 'user',
  is_active    TINYINT(1)          NOT NULL DEFAULT 1,
  avatar       VARCHAR(255)        DEFAULT NULL,
  created_at   DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role  (role)
) ENGINE=InnoDB;

-- =====================================================
-- CATEGORIES
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED DEFAULT NULL COMMENT 'NULL = global/system category',
  name       VARCHAR(100)           NOT NULL,
  type       ENUM('income','expense') NOT NULL,
  icon       VARCHAR(10)            NOT NULL DEFAULT '📦',
  color      VARCHAR(20)            NOT NULL DEFAULT '#6B7280',
  created_at DATETIME               NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user   (user_id),
  INDEX idx_type   (type),
  CONSTRAINT fk_cat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- WALLETS
-- =====================================================
CREATE TABLE IF NOT EXISTS wallets (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED           NOT NULL,
  name            VARCHAR(100)           NOT NULL,
  type            ENUM('cash','bank','ewallet','savings','other') NOT NULL DEFAULT 'cash',
  balance         DECIMAL(18,2)          NOT NULL DEFAULT 0.00,
  initial_balance DECIMAL(18,2)          NOT NULL DEFAULT 0.00,
  gradient        VARCHAR(200)           NOT NULL DEFAULT 'linear-gradient(135deg,#16A34A,#22C55E)',
  description     VARCHAR(255)           DEFAULT NULL,
  is_active       TINYINT(1)             NOT NULL DEFAULT 1,
  created_at      DATETIME               NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user   (user_id),
  INDEX idx_active (is_active),
  CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TRANSACTIONS
-- =====================================================
CREATE TABLE IF NOT EXISTS transactions (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED             NOT NULL,
  wallet_id    INT UNSIGNED             NOT NULL,
  to_wallet_id INT UNSIGNED             DEFAULT NULL,
  category_id  INT UNSIGNED             DEFAULT NULL,
  type         ENUM('income','expense','transfer') NOT NULL,
  amount       DECIMAL(18,2)            NOT NULL CHECK (amount > 0),
  note         VARCHAR(255)             DEFAULT NULL,
  date         DATE                     NOT NULL,
  created_at   DATETIME                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME                 NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user     (user_id),
  INDEX idx_wallet   (wallet_id),
  INDEX idx_date     (date),
  INDEX idx_type     (type),
  CONSTRAINT fk_tx_user     FOREIGN KEY (user_id)      REFERENCES users(id)        ON DELETE CASCADE,
  CONSTRAINT fk_tx_wallet   FOREIGN KEY (wallet_id)    REFERENCES wallets(id)      ON DELETE CASCADE,
  CONSTRAINT fk_tx_to_wal   FOREIGN KEY (to_wallet_id) REFERENCES wallets(id)      ON DELETE SET NULL,
  CONSTRAINT fk_tx_category FOREIGN KEY (category_id)  REFERENCES categories(id)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- USER SETTINGS
-- =====================================================
CREATE TABLE IF NOT EXISTS user_settings (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id          INT UNSIGNED NOT NULL UNIQUE,
  theme            ENUM('light','dark') NOT NULL DEFAULT 'light',
  currency         VARCHAR(10)          NOT NULL DEFAULT 'IDR',
  low_balance_alert DECIMAL(18,2)       NOT NULL DEFAULT 100000.00,
  updated_at       DATETIME             NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- ACTIVITY LOG (Admin)
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_log (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED  DEFAULT NULL,
  action     VARCHAR(100)  NOT NULL,
  details    TEXT          DEFAULT NULL,
  ip_address VARCHAR(45)   DEFAULT NULL,
  created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user   (user_id),
  INDEX idx_action (action),
  INDEX idx_date   (created_at)
) ENGINE=InnoDB;
