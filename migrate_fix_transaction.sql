-- Jalankan file ini SATU KALI SAJA jika database "nikko_kas" kamu sudah pernah
-- dibuat sebelumnya (misalnya lewat create_database.php versi lama) dan tabel
-- `transactions` ternyata BELUM punya kolom `user_id`.
--
-- Cara cek: jalankan  DESCRIBE transactions;  di database nikko_kas.
-- Kalau tidak ada baris "user_id", jalankan script ini.

USE nikko_kas;

ALTER TABLE transactions
  ADD COLUMN user_id INT NOT NULL DEFAULT 1 AFTER id;

ALTER TABLE transactions
  ADD CONSTRAINT fk_transactions_user
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
