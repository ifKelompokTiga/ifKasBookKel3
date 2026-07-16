CREATE DATABASE IF NOT EXISTS buku_kas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nikko_kas;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('income','expense') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    month VARCHAR(7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recurring_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('income','expense') NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    category_id INT NOT NULL,
    wallet_id INT NOT NULL,
    frequency ENUM('monthly','weekly') NOT NULL DEFAULT 'monthly',
    next_date DATE NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Kolom baru untuk melacak siapa pembuat transaksi
    type ENUM('income','expense') NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    transaction_date DATE NOT NULL,
    wallet_id INT NOT NULL,
    category_id INT NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    evidence_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (username, password, name, role) VALUES
('admin', '$2y$10$94rCkvfK4cJL5vW3wY7fQeFKwJr5Yv7A2V68W3h3P6fVQ5mQwz3eG', 'Administrator', 'admin'),
('user', '$2y$10$Ot8sBgIdWVg2pBhQXWg8g.5zVvC3yQW8c6p7I6oE7nq0gQxM2uM6', 'Pengguna', 'user');

INSERT INTO categories (name, type) VALUES
('Gaji', 'income'),
('Penjualan', 'income'),
('Belanja', 'expense'),
('Makan', 'expense'),
('Transport', 'expense');

INSERT INTO wallets (name) VALUES
('Cash'),
('Bank'),
('GoPay');
