<?php 
// Pastikan sesi dan auth sudah di-include sebelumnya
$isAdmin = ($user['role'] ?? 'user') === 'admin'; 

// Ambil data ringkasan dari database
require_once __DIR__ . '/db.php';

$totalIncome = 0.0;
$totalExpense = 0.0;
$monthlyIncome = 0.0;
$monthlyExpense = 0.0;
$monthlyCount = 0;

try {
    // Total keseluruhan (all-time)
    $stmt = $pdo->query("SELECT type, SUM(amount) AS total FROM transactions GROUP BY type");
    foreach ($stmt->fetchAll() as $row) {
        if ($row['type'] === 'income') $totalIncome = (float) $row['total'];
        if ($row['type'] === 'expense') $totalExpense = (float) $row['total'];
    }

    // Ringkasan bulan berjalan
    $stmtMonth = $pdo->prepare(
        "SELECT type, SUM(amount) AS total, COUNT(*) AS cnt
         FROM transactions
         WHERE DATE_FORMAT(transaction_date, '%Y-%m') = :ym
         GROUP BY type"
    );
    $stmtMonth->execute([':ym' => date('Y-m')]);
    foreach ($stmtMonth->fetchAll() as $row) {
        $monthlyCount += (int) $row['cnt'];
        if ($row['type'] === 'income') $monthlyIncome = (float) $row['total'];
        if ($row['type'] === 'expense') $monthlyExpense = (float) $row['total'];
    }
} catch (PDOException $e) {
    // Kalau query gagal, tetap tampilkan 0 daripada mematikan halaman
}

$netTotal = $totalIncome - $totalExpense;
$monthlyNet = $monthlyIncome - $monthlyExpense;

$recentTransactions = [];
try {
    $stmtRecent = $pdo->query(
        "SELECT t.id, t.type, t.amount, t.transaction_date, t.note,
                c.name AS category_name, w.name AS wallet_name
         FROM transactions t
         LEFT JOIN categories c ON c.id = t.category_id
         LEFT JOIN wallets w ON w.id = t.wallet_id
         ORDER BY t.id DESC
         LIMIT 10"
    );
    $recentTransactions = $stmtRecent->fetchAll();
} catch (PDOException $e) {
    // Biarkan daftar kosong kalau query gagal
}

function formatRupiah(float $amount): string
{
    return 'Rp' . number_format($amount, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Nikko Kas</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* Base Styles */
    :root {
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --secondary: #64748b;
      --success: #10b981;
      --danger: #ef4444;
      --bg-body: #f1f5f9;
      --card-bg: #ffffff;
      --text-main: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --gradient-brand: linear-gradient(135deg, #1e3a8a 0%, #3b0764 100%);
      --gradient-btn: linear-gradient(90deg, #2563eb 0%, #3730a3 100%);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); color: var(--text-main); line-height: 1.5; padding-bottom: 40px; }
    
    .app { max-width: 1200px; margin: 0 auto; padding: 20px; }

    /* Typography */
    h1, h2, h3 { font-weight: 700; color: var(--text-main); }
    .eyebrow { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary); font-weight: 600; display: block; margin-bottom: 4px; }
    .muted { color: var(--text-muted); font-size: 14px; }

    /* Cards */
    .card { background: var(--card-bg); border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,0.5); margin-bottom: 20px; }
    .card-heading, .section-head, .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }

    /* Hero Section (Senada dengan Login) */
    .hero-card {
      background: var(--gradient-brand);
      color: white;
      border-radius: 20px;
      padding: 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      box-shadow: 0 15px 35px rgba(30, 58, 138, 0.2);
      position: relative;
      overflow: hidden;
      flex-wrap: wrap;
      gap: 30px;
    }
    /* Dekorasi Latar Hero */
    .hero-card::before { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%; }
    .hero-copy h1 { color: white; font-size: 32px; margin-bottom: 10px; }
    .hero-copy .eyebrow { color: #93c5fd; }
    .hero-copy p { color: #cbd5e1; margin-bottom: 20px; font-size: 15px; }
    
    .hero-highlights { display: flex; gap: 10px; flex-wrap: wrap; }
    .hero-pill { background: rgba(255,255,255,0.15); backdrop-filter: blur(5px); padding: 6px 14px; border-radius: 50px; font-size: 13px; font-weight: 500; }

    /* Hero Summary Box (Glassmorphism) */
    .hero-summary { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.2); padding: 24px; border-radius: 16px; min-width: 300px; text-align: center; z-index: 1; }
    .hero-summary .label { color: #cbd5e1; font-size: 14px; font-weight: 500; display: block; margin-bottom: 5px; }
    .hero-balance { font-size: 36px; font-weight: 700; color: white; margin-bottom: 15px; }
    
    .hero-metrics { display: flex; gap: 15px; justify-content: center; }
    .metric-pill { display: flex; flex-direction: column; align-items: center; padding: 10px 15px; border-radius: 12px; flex: 1; }
    .metric-pill.positive { background: rgba(16, 185, 129, 0.2); color: #34d399; }
    .metric-pill.negative { background: rgba(239, 68, 68, 0.2); color: #f87171; }
    .metric-pill span { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
    .metric-pill strong { font-size: 16px; }

    /* Layout Grids */
    .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .quick-layout { display: grid; grid-template-columns: 1fr 1.5fr; gap: 24px; margin-bottom: 24px; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }

    /* Stats & Badges */
    .summary-stats { display: flex; gap: 20px; }
    .summary-stat { flex: 1; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid var(--border-color); }
    .summary-stat strong { display: block; font-size: 20px; margin-top: 5px; }
    .badge { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    
    /* Buttons */
    button { font-family: inherit; cursor: pointer; transition: all 0.2s ease; border: none; }
    .action-btn, button[type="submit"] { background: var(--gradient-btn); color: white; padding: 12px 20px; border-radius: 10px; font-weight: 600; font-size: 14px; width: 100%; box-shadow: 0 4px 12px rgba(37,99,235,0.2); }
    .action-btn:hover, button[type="submit"]:hover { opacity: 0.9; transform: translateY(-1px); }
    .secondary { background: #e2e8f0; color: #475569; }
    .secondary:hover { background: #cbd5e1; }
    .fab { background: var(--gradient-btn); color: white; width: 45px; height: 45px; border-radius: 50%; font-size: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(37,99,235,0.4); }

    /* Forms */
    form label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
    input, select { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1.5px solid var(--border-color); font-size: 14px; font-family: inherit; background-color: #f8fafc; margin-bottom: 15px; color: var(--text-main); }
    input:focus, select:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .row { display: flex; gap: 15px; flex-wrap: wrap; }
    .row > label { flex: 1; min-width: 150px; }

    /* Segmented Control */
    .segmented-control { display: flex; background: #f1f5f9; padding: 4px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border-color); }
    .segmented-button { flex: 1; padding: 10px; border-radius: 8px; font-weight: 600; font-size: 13px; color: var(--secondary); background: transparent; }
    .segmented-button.is-active { background: white; color: var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

    /* Helpers */
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0; }
    .chip-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .list { border-top: 1px solid var(--border-color); margin-top: 15px; padding-top: 15px; }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-card { flex-direction: column; text-align: center; padding: 30px 20px; }
      .hero-summary { width: 100%; min-width: auto; }
      .hero-highlights { justify-content: center; }
      .dashboard-grid, .quick-layout, .two-col { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="app">
  <section class="hero-card">
    <div class="hero-copy">
      <span class="eyebrow"><i class="fas fa-wallet"></i> Buku Kas Digital</span>
      <h1>Nikko Kas</h1>
      <?php if ($isAdmin): ?>
        <p>Catat transaksi, pantau saldo, dan kelola anggaran tanpa repot di satu tempat.</p>
      <?php else: ?>
        <p>Masukkan pemasukan dan pengeluaran dengan satu sentuhan.</p>
      <?php endif; ?>
      <div class="hero-highlights">
        <span class="hero-pill"><i class="fas fa-bolt text-yellow-400"></i> 3 detik untuk mencatat</span>
        <span class="hero-pill"><i class="fas fa-chart-line"></i> Laporan instan</span>
      </div>
    </div>
    
    <div class="hero-summary">
      <div class="summary-kpi">
        <span class="label">Total Saldo Bersih</span>
        <div id="netTotal" class="hero-balance"><?= formatRupiah($netTotal) ?></div>
        <div class="hero-metrics">
          <div class="metric-pill positive">
            <span><i class="fas fa-arrow-down"></i> Uang Masuk</span>
            <strong id="incomeTotal"><?= formatRupiah($totalIncome) ?></strong>
          </div>
          <div class="metric-pill negative">
            <span><i class="fas fa-arrow-up"></i> Uang Keluar</span>
            <strong id="expenseTotal"><?= formatRupiah($totalExpense) ?></strong>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="dashboard-grid">
    <div class="card summary-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow">Ringkasan</p>
          <h3><i class="fas fa-calendar-alt" style="color:var(--primary); margin-right:5px;"></i> Bulan Ini</h3>
        </div>
        <span class="badge soft">Live</span>
      </div>
      <div class="summary-stats">
        <div class="summary-stat">
          <span class="label muted">Jumlah Transaksi</span>
          <strong id="transactionCount"><?= $monthlyCount ?></strong>
        </div>
        <div class="summary-stat">
          <span class="label muted">Saldo Bulanan</span>
          <strong id="netTotalCompact" class="metric neutral"><?= formatRupiah($monthlyNet) ?></strong>
        </div>
      </div>
    </div>

   <div class="card">
      <div class="card-heading">
        <div>
          <p class="eyebrow">Aksi Cepat</p>
          <h3>Quick Add</h3>
        </div>
        <button id="quickAddFab" class="fab" type="button" aria-label="Tambah transaksi cepat"><i class="fas fa-plus"></i></button>
      </div>
      <p class="muted">Tekan tombol <strong>+</strong> untuk langsung fokus mengisi nominal dan mencatat transaksi dalam hitungan detik.</p>
    </div>
  </div>

  <div class="quick-layout">
    
    <section class="card panel quick-form-card">
      <div class="section-head">
        <div>
          <p class="eyebrow">Formulir</p>
          <h2><i class="fas fa-edit" style="color:var(--primary); margin-right:5px;"></i> Tambah Transaksi</h2>
        </div>
      </div>

<form id="transactionForm" class="quick-form">
        <div class="amount-shell" style="margin-bottom: 20px;">
          <label for="amount">Nominal (Rp)</label>
          <input id="amount" name="amount" type="number" min="0" step="1000" placeholder="Contoh: 150000" required style="font-size: 20px; font-weight: bold; padding: 15px;" />
        </div>

        <div class="segmented-control" role="tablist" aria-label="Jenis transaksi">
          <button type="button" class="segmented-button is-active" data-type="income">Pemasukan</button>
          <button type="button" class="segmented-button" data-type="expense">Pengeluaran</button>
        </div>
        <select id="type" name="type" class="sr-only">
          <option value="income" selected>Pemasukan</option>
          <option value="expense">Pengeluaran</option>
        </select>

        <div class="section-head compact" style="margin-bottom:10px;">
          <div>
            <p class="eyebrow">Kategori</p>
          </div>
        </div>
        
<div id="categoryGrid" class="quick-category-grid" style="margin-bottom: 15px;">
           <span class="badge cat-option" data-type="income" data-id="1">Gaji</span>
           <span class="badge cat-option" data-type="income" data-id="2">Penjualan</span>
           <span class="badge cat-option" data-type="expense" data-id="3">Belanja</span>
           <span class="badge cat-option" data-type="expense" data-id="4">Makan</span>
        </div>

        <input type="hidden" id="category" name="category" value="1" />
        <div class="row">
          <label>Tanggal
            <input id="date" name="date" type="date" required />
          </label>
          <label>Wallet / Sumber Dana
            <select id="wallet" name="wallet" required>
                <option value="">Pilih wallet</option>
                <option value="1">Kas Tunai</option>
                <option value="2">Rekening Bank</option>
            </select>
          </label>
        </div>

        <label>Catatan
          <input id="note" name="note" type="text" placeholder="Keterangan singkat (Makan siang, bensin, dll)" />
        </label>

        <label class="file-input" style="display:block; background:#f8fafc; padding:15px; border: 1.5px dashed var(--border-color); border-radius:10px; text-align:center; margin-bottom: 20px; cursor:pointer;">
          <i class="fas fa-cloud-upload-alt" style="color:var(--primary); font-size:24px; margin-bottom:10px; display:block;"></i>
          Bukti Transaksi (Opsional)
          <input id="evidence" name="evidence" type="file" accept="image/*" style="display:none;" />
        </label>

        <div class="preview" id="previewBox"></div>

        <div class="action-row" style="display:flex; align-items:center; gap:15px;">
          <button type="submit" class="action-btn"><i class="fas fa-save"></i> Simpan Transaksi</button>
          <span id="saveFeedback" class="feedback-pill" aria-live="polite"></span>
        </div>
      </form>
    </section>

    <section class="card panel">
      <div class="section-head">
        <div>
          <p class="eyebrow">Riwayat</p>
          <h2><i class="fas fa-history" style="color:var(--primary); margin-right:5px;"></i> Terkini</h2>
        </div>
      </div>
      <div id="recentTransactions" class="recent-list">
        <?php if (empty($recentTransactions)): ?>
          <p class="muted">Belum ada transaksi. Yuk catat transaksi pertamamu!</p>
        <?php else: ?>
          <?php foreach ($recentTransactions as $trx): ?>
            <?php $isIncome = $trx['type'] === 'income'; ?>
            <div class="recent-item" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--border-color);">
              <div>
                <strong style="display:block;"><?= htmlspecialchars($trx['category_name'] ?? 'Tanpa kategori') ?></strong>
                <span class="muted" style="font-size:12px;">
                  <?= htmlspecialchars(date('d M Y', strtotime($trx['transaction_date']))) ?>
                  <?= $trx['wallet_name'] ? ' · ' . htmlspecialchars($trx['wallet_name']) : '' ?>
                  <?= $trx['note'] ? ' · ' . htmlspecialchars($trx['note']) : '' ?>
                </span>
              </div>
              <strong style="color: <?= $isIncome ? 'var(--success)' : 'var(--danger)' ?>;">
                <?= $isIncome ? '+' : '-' ?><?= formatRupiah((float) $trx['amount']) ?>
              </strong>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php if ($isAdmin): ?>
  <div class="two-col">
    <section class="card panel">
      <div class="section-head">
        <div>
          <p class="eyebrow">Kelola Master Data</p>
          <h2><i class="fas fa-tags" style="color:var(--primary); margin-right:5px;"></i> Kategori & Wallet</h2>
        </div>
      </div>
      <div class="two-col compact">
        <div>
          <h3 style="font-size:16px; margin-bottom:10px;">Kategori</h3>
          <form id="categoryForm" style="display:flex; gap:10px;">
            <input id="categoryName" type="text" placeholder="Cth: Belanja" required style="margin-bottom:0;" />
            <button type="submit" style="width:auto; padding:12px;"><i class="fas fa-plus"></i></button>
          </form>
          <div id="categoryList" class="chip-row"></div>
        </div>
        <div>
          <h3 style="font-size:16px; margin-bottom:10px;">Wallet / Kas</h3>
          <form id="walletForm" style="display:flex; gap:10px;">
            <input id="walletName" type="text" placeholder="Cth: GoPay" required style="margin-bottom:0;" />
            <button type="submit" class="secondary" style="width:auto; padding:12px;"><i class="fas fa-plus"></i></button>
          </form>
          <div id="walletList" class="chip-row"></div>
        </div>
      </div>
    </section>

    <section class="card panel">
      <div class="section-head">
        <div>
          <p class="eyebrow">Perencanaan</p>
          <h2><i class="fas fa-bullseye" style="color:var(--primary); margin-right:5px;"></i> Anggaran Bulanan</h2>
        </div>
      </div>
      <form id="budgetForm">
        <div class="row">
          <label>Kategori
            <select id="budgetCategory"></select>
          </label>
          <label>Target Nominal (Rp)
            <input id="budgetAmount" type="number" min="0" step="1000" required />
          </label>
        </div>
        <button type="submit" style="margin-top:10px;"><i class="fas fa-save"></i> Simpan Anggaran</button>
      </form>
      <div id="budgetList" class="list"></div>
    </section>
  </div>

  <section class="card panel" style="margin-bottom: 24px;">
    <div class="section-head">
        <div>
          <p class="eyebrow">Otomatisasi</p>
          <h2><i class="fas fa-sync-alt" style="color:var(--primary); margin-right:5px;"></i> Transaksi Berulang</h2>
        </div>
    </div>
    <form id="recurringForm">
      <div class="row">
        <label>Jenis
          <select id="recurringType">
            <option value="expense">Pengeluaran</option>
            <option value="income">Pemasukan</option>
          </select>
        </label>
        <label>Nominal (Rp)
          <input id="recurringAmount" type="number" min="0" step="1000" required />
        </label>
      </div>
      <div class="row">
        <label>Kategori
          <select id="recurringCategory" required></select>
        </label>
        <label>Wallet
          <select id="recurringWallet" required></select>
        </label>
      </div>
      <div class="row">
        <label>Frekuensi
          <select id="recurringFrequency">
            <option value="monthly">Bulanan</option>
            <option value="weekly">Mingguan</option>
          </select>
        </label>
        <label>Jatuh Tempo Berikutnya
          <input id="recurringNextDate" type="date" required />
        </label>
      </div>
      <label>Catatan
        <input id="recurringNote" type="text" placeholder="Contoh: Bayar kos, Netflix" />
      </label>
      <button type="submit"><i class="fas fa-plus-circle"></i> Tambah Transaksi Berulang</button>
    </form>
    <div id="recurringList" class="list"></div>
  </section>
  <?php endif; ?>

  <section class="card panel">
    <div class="toolbar">
      <div>
         <p class="eyebrow">Database</p>
         <h2 style="margin: 0;"><i class="fas fa-table" style="color:var(--primary); margin-right:5px;"></i> Semua Transaksi</h2>
      </div>
      <div class="controls">
        <button id="exportCsv" class="secondary" style="padding: 10px 15px; font-size: 13px;"><i class="fas fa-file-csv"></i> Export CSV</button>
      </div>
    </div>
    
    <form id="filterForm" class="row" style="margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid var(--border-color);">
      <label>Search
        <input id="search" type="text" placeholder="Cari keterangan..." style="margin-bottom:0;" />
      </label>
      <label>Jenis
        <select id="filterType" style="margin-bottom:0;">
          <option value="">Semua</option>
          <option value="income">Pemasukan</option>
          <option value="expense">Pengeluaran</option>
        </select>
      </label>
      <label>Kategori
        <select id="filterCategory" style="margin-bottom:0;"></select>
      </label>
      <label>Wallet
        <select id="filterWallet" style="margin-bottom:0;"></select>
      </label>
      <label>Mulai
        <input id="filterStart" type="date" style="margin-bottom:0;" />
      </label>
      <label>Selesai
        <input id="filterEnd" type="date" style="margin-bottom:0;" />
      </label>
    </form>
    
    <div id="transactionList" class="list"></div>
  </section>

  <?php if ($isAdmin): ?>
  <div class="two-col" style="margin-top: 24px;">
    <section class="card panel">
      <div class="section-head">
        <div>
          <p class="eyebrow">Analisis Visual</p>
          <h2><i class="fas fa-chart-pie" style="color:var(--primary); margin-right:5px;"></i> Ringkasan Pengeluaran</h2>
        </div>
        <div class="segmented-control" style="margin-bottom: 0;" role="tablist">
          <button type="button" class="segmented-button is-active" data-period="daily">Harian</button>
          <button type="button" class="segmented-button" data-period="weekly">Mingguan</button>
          <button type="button" class="segmented-button" data-period="monthly">Bulan</button>
        </div>
      </div>
      <div class="chart-wrap" style="height: 300px; display:flex; align-items:center; justify-content:center;">
        <canvas id="pieChart"></canvas>
      </div>
    </section>

    <section class="card panel">
      <div class="section-head">
        <div>
          <p class="eyebrow">Statistik</p>
          <h2><i class="fas fa-chart-area" style="color:var(--primary); margin-right:5px;"></i> Tren 6 Periode Terakhir</h2>
        </div>
      </div>
      <div class="chart-wrap" style="height: 300px; display:flex; align-items:center; justify-content:center;">
        <canvas id="trendChart"></canvas>
      </div>
    </section>
  </div>
  <?php endif; ?>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {

    // 1. Fungsi Tombol "Quick Add" (+)
    const quickAddBtn = document.getElementById('quickAddFab');
    if (quickAddBtn) {
      quickAddBtn.addEventListener('click', () => {
        const amountInput = document.getElementById('amount');
        if (amountInput) {
          amountInput.focus(); // Arahkan kursor langsung ke kolom nominal
          window.scrollTo({ top: amountInput.offsetTop - 100, behavior: 'smooth' });
        }
      });
    }

    // 2. Fungsi Tab Pemasukan / Pengeluaran (Segmented Control)
    const typeButtons = document.querySelectorAll('.quick-form-card .segmented-button');
    const typeSelect = document.getElementById('type');
    
    typeButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        // Hapus class 'is-active' dari semua tombol
        typeButtons.forEach(b => b.classList.remove('is-active'));
        // Tambahkan ke tombol yang diklik
        this.classList.add('is-active');
        // Ubah nilai select yang tersembunyi (dikirim saat form disubmit)
        if (typeSelect) typeSelect.value = this.getAttribute('data-type');
      });
    });

    // 3. Fungsi Tab Periode Chart (Harian / Mingguan / Bulanan)
    const periodButtons = document.querySelectorAll('.period-tabs .segmented-button');
    periodButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        periodButtons.forEach(b => b.classList.remove('is-active'));
        this.classList.add('is-active');
        const period = this.getAttribute('data-period');
        console.log("Ganti chart ke periode:", period);
        // Di sini Anda bisa memanggil fungsi AJAX untuk memuat data chart baru
      });
    });

    // 4. Mencegah Form Refresh Halaman (Contoh Integrasi AJAX)
    // Berlaku untuk semua form di dashboard
    const forms = [
      'transactionForm', 'categoryForm', 'walletForm', 
      'budgetForm', 'recurringForm', 'filterForm'
    ];

    forms.forEach(formId => {
      const formElement = document.getElementById(formId);
      if (formElement) {
        formElement.addEventListener('submit', function(e) {
          e.preventDefault(); // Mencegah halaman refresh
          
          // --- CONTOH LOGIKA AJAX (Ganti dengan sistem Fetch/AJAX PHP Anda) ---
          console.log(`Form ${formId} sedang disubmit...`);
          
          // Memberi efek loading pada tombol
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
          submitBtn.disabled = true;

          // Simulasi proses ke server (hapus setTimeout ini saat integrasi ke PHP)
          setTimeout(() => {
            alert(`Berhasil menyimpan data dari ${formId}!`);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            this.reset(); // Kosongkan form setelah berhasil
          }, 800);
        });
      }
    });

    // 5. Tombol Export CSV
    const exportBtn = document.getElementById('exportCsv');
    if (exportBtn) {
      exportBtn.addEventListener('click', () => {
        // Di sini Anda arahkan ke file PHP yang meng-generate CSV, contoh:
        // window.location.href = 'export.php';
        alert("Fungsi Export CSV dipicu! (Hubungkan ke file PHP Anda)");
      });
    }

  });
</script>
</body>
</html>