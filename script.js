// ── STATE ──
let transactions = JSON.parse(localStorage.getItem('bkd_tx') || '[]');
let currentType   = 'income';
let currentFilter = 'all';

// ── DATA ──
const CATEGORIES = {
  income:  ['Gaji / Upah', 'Bonus', 'Penjualan', 'Transfer Masuk', 'Investasi', 'Lainnya'],
  expense: ['Makanan & Minuman', 'Transportasi', 'Kesehatan', 'Belanja', 'Tagihan', 'Pendidikan', 'Hiburan', 'Lainnya']
};

const ICONS = {
  income: {
    'Gaji / Upah':   '💼',
    'Bonus':         '🎁',
    'Penjualan':     '🛍️',
    'Transfer Masuk':'📲',
    'Investasi':     '📈',
    'Lainnya':       '💰'
  },
  expense: {
    'Makanan & Minuman': '🍜',
    'Transportasi':      '🚌',
    'Kesehatan':         '💊',
    'Belanja':           '🛒',
    'Tagihan':           '🧾',
    'Pendidikan':        '📚',
    'Hiburan':           '🎬',
    'Lainnya':           '💸'
  }
};

// ── INIT ──
function init() {
  const now = new Date();
  document.getElementById('dateLabel').textContent =
    now.toLocaleDateString('id-ID', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

  document.getElementById('tanggal').value = now.toISOString().split('T')[0];

  setType('income');
  renderList();
  updateSummary();
}

// ── TYPE TOGGLE ──
function setType(type) {
  currentType = type;

  document.getElementById('btnIncome').className =
    'type-btn' + (type === 'income'  ? ' active-income'  : '');
  document.getElementById('btnExpense').className =
    'type-btn' + (type === 'expense' ? ' active-expense' : '');

  const sel = document.getElementById('kategori');
  sel.innerHTML = '<option value="" disabled selected>Pilih kategori</option>';
  CATEGORIES[type].forEach(cat => {
    const opt = document.createElement('option');
    opt.value       = cat;
    opt.textContent = cat;
    sel.appendChild(opt);
  });
}

// ── TAMBAH TRANSAKSI ──
function tambahTransaksi() {
  const desc   = document.getElementById('deskripsi').value.trim();
  const amount = parseFloat(document.getElementById('jumlah').value);
  const cat    = document.getElementById('kategori').value;
  const date   = document.getElementById('tanggal').value;

  if (!desc)              return showToast('Isi deskripsi terlebih dahulu');
  if (!amount || amount <= 0) return showToast('Masukkan jumlah yang valid');
  if (!cat)               return showToast('Pilih kategori');
  if (!date)              return showToast('Pilih tanggal');

  const tx = {
    id: Date.now(),
    type: currentType,
    desc,
    amount,
    cat,
    date
  };

  transactions.unshift(tx);
  save();
  renderList();
  updateSummary();

  // Reset form fields
  document.getElementById('deskripsi').value = '';
  document.getElementById('jumlah').value    = '';
  document.getElementById('kategori').value  = '';

  showToast(currentType === 'income' ? 'Pemasukan ditambahkan ✓' : 'Pengeluaran dicatat ✓');
}

// ── HAPUS TRANSAKSI ──
function hapus(id) {
  transactions = transactions.filter(t => t.id !== id);
  save();
  renderList();
  updateSummary();
  showToast('Transaksi dihapus');
}

// ── FILTER ──
function setFilter(filter, btn) {
  currentFilter = filter;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderList();
}

// ── RENDER LIST ──
function renderList() {
  const search = document.getElementById('searchInput').value.toLowerCase();

  const filtered = transactions.filter(t => {
    const matchType   = currentFilter === 'all' || t.type === currentFilter;
    const matchSearch = !search ||
      t.desc.toLowerCase().includes(search) ||
      t.cat.toLowerCase().includes(search);
    return matchType && matchSearch;
  });

  const body = document.getElementById('listBody');

  if (filtered.length === 0) {
    body.innerHTML = `
      <div class="empty-state">
        <div class="empty-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
               style="color: var(--hint)">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
            <path d="M9 12h6M9 16h4"/>
          </svg>
        </div>
        <p>Belum ada transaksi</p>
      </div>`;
    return;
  }

  // Kelompokkan per tanggal
  const byDate = {};
  filtered.forEach(t => {
    if (!byDate[t.date]) byDate[t.date] = [];
    byDate[t.date].push(t);
  });

  let html = '';
  Object.keys(byDate)
    .sort((a, b) => b.localeCompare(a))
    .forEach(date => {
      const d     = new Date(date + 'T00:00:00');
      const label = d.toLocaleDateString('id-ID', {
        weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
      });

      html += `<div class="date-group-label">${label}</div>`;

      byDate[date].forEach(t => {
        const icon   = ICONS[t.type][t.cat] || (t.type === 'income' ? '💰' : '💸');
        const amtStr = 'Rp ' + t.amount.toLocaleString('id-ID');
        const sign   = t.type === 'income' ? '+' : '−';

        html += `
          <div class="tx-item">
            <div class="tx-icon ${t.type === 'income' ? 'income-icon' : 'expense-icon'}">${icon}</div>
            <div class="tx-info">
              <div class="tx-desc">${escHtml(t.desc)}</div>
              <div class="tx-meta">
                <span class="tx-cat-pill">${escHtml(t.cat)}</span>
              </div>
            </div>
            <div class="tx-amount ${t.type === 'income' ? 'in' : 'out'}">${sign}${amtStr}</div>
            <button class="tx-del" onclick="hapus(${t.id})" title="Hapus">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14H6L5 6"/>
                <path d="M10 11v6M14 11v6"/>
                <path d="M9 6V4h6v2"/>
              </svg>
            </button>
          </div>`;
      });
    });

  body.innerHTML = html;
}

// ── UPDATE RINGKASAN ──
function updateSummary() {
  const income  = transactions.filter(t => t.type === 'income') .reduce((s, t) => s + t.amount, 0);
  const expense = transactions.filter(t => t.type === 'expense').reduce((s, t) => s + t.amount, 0);
  const balance = income - expense;

  const fmt = n => 'Rp ' + Math.abs(n).toLocaleString('id-ID');

  const saldoEl = document.getElementById('saldo');
  saldoEl.textContent = (balance < 0 ? '−' : '') + fmt(balance);
  saldoEl.className   = 'card-value ' + (balance >= 0 ? 'balance-positive' : 'balance-negative');

  document.getElementById('totalIncome') .textContent = fmt(income);
  document.getElementById('totalExpense').textContent = fmt(expense);

  const ic = transactions.filter(t => t.type === 'income').length;
  const ec = transactions.filter(t => t.type === 'expense').length;
  document.getElementById('txCount')     .textContent = `${transactions.length} transaksi total`;
  document.getElementById('incomeCount') .textContent = `${ic} catatan`;
  document.getElementById('expenseCount').textContent = `${ec} catatan`;
}

// ── UTILS ──
function save() {
  localStorage.setItem('bkd_tx', JSON.stringify(transactions));
}

function escHtml(str) {
  return str
    .replace(/&/g,  '&amp;')
    .replace(/</g,  '&lt;')
    .replace(/>/g,  '&gt;')
    .replace(/"/g,  '&quot;');
}

let toastTimer;
function showToast(msg) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 2200);
}

// ── START ──
init();
