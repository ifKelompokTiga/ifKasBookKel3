<?php $isAdmin = ($user['role'] ?? 'user') === 'admin'; ?>
<div class="app">
  <section class="hero">
    <h1>Buku Kas</h1>
    <?php if ($isAdmin): ?>
      <p>Kelola pemasukan, pengeluaran, anggaran, dan dompet Anda dalam satu dashboard yang simpel namun powerful.</p>
    <?php else: ?>
      <p>Selamat datang di buku kas.</p>
    <?php endif; ?>
  </section>

  <section class="dashboard-grid">
    <div class="card">
      <h3>Total Pemasukan Bulan Ini</h3>
      <div id="incomeTotal" class="metric positive">Rp0</div>
    </div>
    <div class="card">
      <h3>Total Pengeluaran Bulan Ini</h3>
      <div id="expenseTotal" class="metric negative">Rp0</div>
    </div>
    <div class="card">
      <h3>Saldo Bersih</h3>
      <div id="netTotal" class="metric neutral">Rp0</div>
    </div>
    <div class="card">
      <h3>Transaksi Aktif</h3>
      <div id="transactionCount" class="metric">0</div>
    </div>
  </section>

  <div class="grid-2">
    <section class="card panel">
      <h2>Tambah Transaksi</h2>
      <form id="transactionForm">
        <div class="row">
          <label>Jenis
            <select id="type" required>
              <option value="income">Pemasukan</option>
              <option value="expense">Pengeluaran</option>
            </select>
          </label>
          <label>Nominal
            <input id="amount" type="number" min="0" step="1000" placeholder="Contoh: 150000" required />
          </label>
        </div>
        <div class="row">
          <label>Tanggal
            <input id="date" type="date" required />
          </label>
          <label>Wallet/Akun Kas
            <select id="wallet" required></select>
          </label>
        </div>
        <div class="row">
          <label>Kategori
            <select id="category" required></select>
          </label>
          <label>Catatan
            <input id="note" type="text" placeholder="Keterangan singkat" />
          </label>
        </div>
        <label>Bukti Transaksi (opsional)
          <input id="evidence" type="file" accept="image/*" />
        </label>
        <div class="preview" id="previewBox"></div>
        <button type="submit">Simpan Transaksi</button>
      </form>
    </section>

    <?php if ($isAdmin): ?>
    <section class="card panel">
      <h2>Kelola Kategori & Wallet</h2>
      <div class="two-col">
        <div>
          <h3>Kategori</h3>
          <form id="categoryForm">
            <input id="categoryName" type="text" placeholder="Contoh: Belanja" required />
            <button type="submit">Tambah Kategori</button>
          </form>
          <div id="categoryList" class="chip-row"></div>
        </div>
        <div>
          <h3>Wallet</h3>
          <form id="walletForm">
            <input id="walletName" type="text" placeholder="Contoh: GoPay" required />
            <button type="submit" class="secondary">Tambah Wallet</button>
          </form>
          <div id="walletList" class="chip-row"></div>
        </div>
      </div>
    </section>
    <?php endif; ?>
  </div>

  <?php if ($isAdmin): ?>
  <div class="two-col">
    <section class="card panel">
      <h2>Anggaran Bulanan</h2>
      <form id="budgetForm">
        <div class="row">
          <label>Kategori
            <select id="budgetCategory"></select>
          </label>
          <label>Target (Rp)
            <input id="budgetAmount" type="number" min="0" step="1000" required />
          </label>
        </div>
        <button type="submit">Simpan Anggaran</button>
      </form>
      <div id="budgetList" class="list"></div>
    </section>

    <section class="card panel">
      <h2>Transaksi Berulang</h2>
      <form id="recurringForm">
        <div class="row">
          <label>Jenis
            <select id="recurringType">
              <option value="expense">Pengeluaran</option>
              <option value="income">Pemasukan</option>
            </select>
          </label>
          <label>Nominal
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
          <input id="recurringNote" type="text" placeholder="Contoh: Bayar kos" />
        </label>
        <button type="submit">Tambah Transaksi Berulang</button>
      </form>
      <div id="recurringList" class="list"></div>
    </section>
  </div>
  <?php endif; ?>

  <section class="card panel">
    <div class="toolbar">
      <h2 style="margin: 0;">Riwayat Transaksi</h2>
      <div class="controls">
        <button id="exportCsv" class="secondary small">Export CSV</button>
      </div>
    </div>
    <form id="filterForm" class="row" style="margin-bottom: 14px;">
      <label>Search
        <input id="search" type="text" placeholder="Cari keterangan" />
      </label>
      <label>Jenis
        <select id="filterType">
          <option value="">Semua</option>
          <option value="income">Pemasukan</option>
          <option value="expense">Pengeluaran</option>
        </select>
      </label>
      <label>Kategori
        <select id="filterCategory"></select>
      </label>
      <label>Wallet
        <select id="filterWallet"></select>
      </label>
      <label>Mulai
        <input id="filterStart" type="date" />
      </label>
      <label>Selesai
        <input id="filterEnd" type="date" />
      </label>
    </form>
    <div id="transactionList" class="list"></div>
  </section>

  <?php if ($isAdmin): ?>
  <div class="two-col" style="margin-top: 24px;">
    <section class="card panel">
      <h2>Ringkasan Pengeluaran Bulan Ini</h2>
      <div class="chart-wrap">
        <canvas id="pieChart"></canvas>
      </div>
    </section>
    <section class="card panel">
      <h2>Tren 6 Bulan Terakhir</h2>
      <div class="chart-wrap">
        <canvas id="trendChart"></canvas>
      </div>
    </section>
  </div>
  <?php endif; ?>
</div>
