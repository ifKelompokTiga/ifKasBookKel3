<?php
// Mulai sesi untuk mendapatkan data user yang sedang login
session_start();

// Panggil koneksi database
require __DIR__ . '/db.php';

// Pastikan request yang datang adalah POST dari form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Tangkap semua data dari form
    $amount      = $_POST['amount'] ?? 0;
    $type        = $_POST['type'] ?? 'expense';
    $category_id = $_POST['category'] ?? 3;
    $date        = $_POST['date'] ?? date('Y-m-d');
    $wallet_id   = $_POST['wallet'] ?? 1;
    $note        = $_POST['note'] ?? '';
    
    // 2. Ambil ID User yang sedang login (Kita gunakan 1 sebagai default/admin jika sesi belum sempurna)
    $user_id = $_SESSION['user']['id'] ?? 1; 

    // 3. Masukkan ke Database menggunakan PDO (Lebih aman dari serangan hacker)
    try {
        $sql = "INSERT INTO transactions (user_id, type, amount, transaction_date, wallet_id, category_id, note) 
                VALUES (:user_id, :type, :amount, :date, :wallet_id, :category_id, :note)";
                
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':user_id'     => $user_id,
            ':type'        => $type,
            ':amount'      => $amount,
            ':date'        => $date,
            ':wallet_id'   => $wallet_id,
            ':category_id' => $category_id,
            ':note'        => $note
        ]);

        // Beri respon sukses ke JavaScript
        echo "Sukses: Transaksi berhasil dicatat!";

    } catch (PDOException $e) {
        // Jika terjadi error pada database, beri tahu JavaScript
        http_response_code(500);
        echo "Gagal: " . $e->getMessage();
    }
} else {
    echo "Akses ditolak!";
}
?>