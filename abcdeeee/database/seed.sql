-- =====================================================
-- BukuKas Universal — Seed Data
-- Run AFTER schema.sql
-- =====================================================
USE bukukas;

-- =====================================================
-- DEFAULT GLOBAL CATEGORIES (user_id = NULL)
-- =====================================================
INSERT INTO categories (user_id, name, type, icon, color) VALUES
  (NULL, 'Makanan & Minuman', 'expense', '🍔', '#F59E0B'),
  (NULL, 'Transportasi',       'expense', '🚗', '#3B82F6'),
  (NULL, 'Belanja',            'expense', '🛒', '#8B5CF6'),
  (NULL, 'Tagihan & Utilitas', 'expense', '⚡', '#EF4444'),
  (NULL, 'Hiburan',            'expense', '🎮', '#EC4899'),
  (NULL, 'Kesehatan',          'expense', '❤️', '#10B981'),
  (NULL, 'Pendidikan',         'expense', '📚', '#6366F1'),
  (NULL, 'Lainnya',            'expense', '📦', '#6B7280'),
  (NULL, 'Gaji',               'income',  '💼', '#10B981'),
  (NULL, 'Freelance',          'income',  '💻', '#16A34A'),
  (NULL, 'Hadiah',             'income',  '🎁', '#F59E0B'),
  (NULL, 'Investasi',          'income',  '📈', '#3B82F6'),
  (NULL, 'Lainnya',            'income',  '💰', '#6B7280');

-- =====================================================
-- NOTE: First user who registers becomes admin automatically.
-- No hardcoded admin seed here — handled in register.php.
-- =====================================================

SELECT 'Seed data inserted successfully.' AS status;
