<?php
// Admin Panel Entry Point — Check admin role
require_once __DIR__ . '/../config/app.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php'); exit;
}

$db   = getDB();
$stmt = $db->prepare('SELECT id, name, email, role, is_active FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

if (!$admin || $admin['role'] !== 'admin' || !$admin['is_active']) {
    header('Location: ../app.php?err=forbidden'); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — BukuKas Universal</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛡️</text></svg>" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Poppins', sans-serif; background: #0F172A; color: #F1F5F9; display: flex; min-height: 100vh; }
    a { color: inherit; text-decoration: none; }
    button { cursor: pointer; font-family: inherit; }
    input, select { font-family: inherit; }

    /* SIDEBAR */
    .adm-sidebar {
      width: 240px; height: 100vh; position: fixed;
      background: #1E293B; border-right: 1px solid #334155;
      display: flex; flex-direction: column;
      z-index: 100; flex-shrink: 0;
    }
    .adm-logo { padding: 24px 20px; border-bottom: 1px solid #334155; display: flex; align-items: center; gap: 10px; }
    .adm-logo-icon { width: 36px; height: 36px; background: linear-gradient(135deg,#7C3AED,#8B5CF6); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .adm-logo-text { font-weight: 700; font-size: 14px; }
    .adm-logo-sub  { font-size: 10px; color: #64748B; }

    .adm-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
    .adm-nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px; border-radius: 8px; cursor: pointer;
      color: #94A3B8; font-size: 13px; font-weight: 500;
      transition: background 0.15s, color 0.15s; margin-bottom: 2px;
    }
    .adm-nav-item:hover { background: rgba(139,92,246,0.1); color: #C4B5FD; }
    .adm-nav-item.active { background: rgba(139,92,246,0.15); color: #A78BFA; font-weight: 600; }

    .adm-sidebar-footer {
      padding: 16px 12px; border-top: 1px solid #334155;
    }
    .adm-user { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; margin-bottom: 8px; }
    .adm-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#7C3AED,#8B5CF6); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; }
    .adm-user-name { font-size: 12px; font-weight: 600; }
    .adm-user-badge { font-size: 10px; color: #A78BFA; }
    .adm-btn-user-app { width: 100%; padding: 8px 12px; background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.3); border-radius: 8px; color: #A78BFA; font-size: 12px; font-weight: 600; margin-bottom: 6px; }
    .adm-btn-logout { width: 100%; padding: 8px 12px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; color: #FCA5A5; font-size: 12px; font-weight: 600; }

    /* MAIN */
    .adm-main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; }

    /* TOPBAR */
    .adm-topbar {
      position: sticky; top: 0; z-index: 50;
      background: #1E293B; border-bottom: 1px solid #334155;
      height: 60px; display: flex; align-items: center; justify-content: space-between;
      padding: 0 24px;
    }
    .adm-breadcrumb { font-size: 16px; font-weight: 600; }
    .adm-topbar-actions { display: flex; align-items: center; gap: 12px; }
    .adm-badge-admin { background: rgba(139,92,246,0.2); color: #A78BFA; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }

    /* CONTENT */
    .adm-content { flex: 1; padding: 28px; overflow-x: hidden; }

    /* SECTIONS */
    .adm-section { display: none; }
    .adm-section.active { display: block; animation: fadeUp 0.2s ease; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

    /* STAT CARDS */
    .adm-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    @media (max-width: 1200px) { .adm-stat-grid { grid-template-columns: repeat(2, 1fr); } }
    .adm-stat-card {
      background: #1E293B; border: 1px solid #334155; border-radius: 14px;
      padding: 20px; display: flex; flex-direction: column; gap: 8px;
    }
    .adm-stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .adm-stat-label { font-size: 12px; color: #64748B; font-weight: 500; }
    .adm-stat-value { font-size: 26px; font-weight: 700; font-variant-numeric: tabular-nums; line-height: 1.2; }

    /* CHARTS */
    .adm-chart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px; }
    @media (max-width: 1100px) { .adm-chart-grid { grid-template-columns: 1fr; } }
    .adm-card { background: #1E293B; border: 1px solid #334155; border-radius: 14px; padding: 20px; }
    .adm-card-title { font-size: 14px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; }
    .chart-wrap { position: relative; height: 220px; }

    /* TABLE */
    .adm-table-wrap { overflow-x: auto; margin-top: 12px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th { background: #0F172A; color: #64748B; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; padding: 10px 14px; text-align: left; border-bottom: 1px solid #334155; white-space: nowrap; }
    tbody tr { border-bottom: 1px solid rgba(51,65,85,0.5); transition: background 0.1s; }
    tbody tr:hover { background: rgba(139,92,246,0.05); }
    td { padding: 10px 14px; vertical-align: middle; }
    .td-right { text-align: right; font-variant-numeric: tabular-nums; }

    /* BADGES */
    .badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .badge-admin   { background: rgba(139,92,246,0.2); color: #A78BFA; }
    .badge-user    { background: rgba(59,130,246,0.2); color: #93C5FD; }
    .badge-active  { background: rgba(16,185,129,0.2); color: #6EE7B7; }
    .badge-inactive{ background: rgba(239,68,68,0.15); color: #FCA5A5; }
    .badge-income  { background: rgba(16,185,129,0.2); color: #6EE7B7; }
    .badge-expense { background: rgba(239,68,68,0.15); color: #FCA5A5; }
    .badge-transfer{ background: rgba(59,130,246,0.2); color: #93C5FD; }

    /* BUTTONS */
    .btn-xs { padding: 4px 10px; border-radius: 6px; border: none; font-size: 11px; font-weight: 600; cursor: pointer; transition: background 0.15s; }
    .btn-purple  { background: rgba(139,92,246,0.2); color: #A78BFA; }
    .btn-green   { background: rgba(16,185,129,0.2); color: #6EE7B7; }
    .btn-red     { background: rgba(239,68,68,0.15); color: #FCA5A5; }
    .btn-blue    { background: rgba(59,130,246,0.2); color: #93C5FD; }
    .btn-xs:hover { filter: brightness(1.2); }

    /* SEARCH / FILTER */
    .adm-filter { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
    .adm-filter input, .adm-filter select {
      height: 36px; padding: 0 12px; background: #0F172A; border: 1px solid #334155;
      border-radius: 8px; color: #F1F5F9; font-size: 12px; outline: none;
    }
    .adm-filter input:focus, .adm-filter select:focus { border-color: #7C3AED; }
    .adm-filter input { flex: 1; min-width: 200px; }

    /* ALERT */
    .adm-toast { position: fixed; top: 20px; right: 20px; background: #1E293B; border: 1px solid #334155; border-radius: 10px; padding: 12px 16px; font-size: 13px; display: none; align-items: center; gap: 8px; z-index: 9999; box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
    .adm-toast.show { display: flex; animation: slideIn 0.3s ease; }
    @keyframes slideIn { from { opacity:0; transform:translateX(30px); } to { opacity:1; transform:translateX(0); } }

    /* ACTIVITY */
    .activity-item { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px solid #334155; }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: #7C3AED; margin-top: 5px; flex-shrink: 0; }
    .activity-text { font-size: 12px; }
    .activity-time { font-size: 11px; color: #64748B; margin-top: 2px; }

    /* MODAL */
    .adm-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200; display: none; align-items: center; justify-content: center; }
    .adm-modal-overlay.open { display: flex; }
    .adm-modal { background: #1E293B; border: 1px solid #334155; border-radius: 16px; padding: 28px; max-width: 440px; width: 100%; margin: 16px; }
    .adm-modal h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
    .adm-modal p  { font-size: 13px; color: #94A3B8; margin-bottom: 24px; }
    .adm-modal-actions { display: flex; gap: 12px; justify-content: flex-end; }
    .adm-modal-btn { padding: 10px 20px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; }
    .adm-modal-btn-cancel { background: #334155; color: #94A3B8; }
    .adm-modal-btn-confirm { background: #7C3AED; color: #fff; }
    .adm-modal-btn-danger  { background: #EF4444; color: #fff; }

    /* LOADING */
    .adm-loading { text-align: center; padding: 40px; color: #64748B; font-size: 13px; }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .adm-sidebar { width: 0; overflow: hidden; }
      .adm-main    { margin-left: 0; }
      .adm-stat-grid { grid-template-columns: 1fr 1fr; }
      .adm-chart-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- Confirm Modal -->
<div class="adm-modal-overlay" id="confirmModal">
  <div class="adm-modal">
    <h3 id="confirmTitle">Konfirmasi</h3>
    <p id="confirmDesc">Apakah Anda yakin?</p>
    <div class="adm-modal-actions">
      <button class="adm-modal-btn adm-modal-btn-cancel" onclick="closeConfirm()">Batal</button>
      <button class="adm-modal-btn adm-modal-btn-danger" id="confirmOkBtn">Konfirmasi</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="adm-toast" id="admToast">
  <span id="admToastMsg"></span>
</div>

<!-- SIDEBAR -->
<aside class="adm-sidebar">
  <div class="adm-logo">
    <div class="adm-logo-icon">🛡️</div>
    <div>
      <div class="adm-logo-text">Admin Panel</div>
      <div class="adm-logo-sub">BukuKas Universal</div>
    </div>
  </div>

  <nav class="adm-nav">
    <div class="adm-nav-item active" data-section="dashboard">
      <span>📊</span> Dashboard
    </div>
    <div class="adm-nav-item" data-section="users">
      <span>👥</span> Manajemen User
    </div>
    <div class="adm-nav-item" data-section="activity">
      <span>📋</span> Log Aktivitas
    </div>
  </nav>

  <div class="adm-sidebar-footer">
    <div class="adm-user">
      <div class="adm-avatar"><?= strtoupper(substr($admin['name'], 0, 1)) ?></div>
      <div>
        <div class="adm-user-name"><?= htmlspecialchars($admin['name']) ?></div>
        <div class="adm-user-badge">🛡️ Administrator</div>
      </div>
    </div>
    <button class="adm-btn-user-app" onclick="window.location.href='../app.php'">↩ Ke Aplikasi</button>
    <button class="adm-btn-logout" onclick="handleLogout()">🚪 Keluar</button>
  </div>
</aside>

<!-- MAIN -->
<main class="adm-main">
  <!-- Topbar -->
  <header class="adm-topbar">
    <div class="adm-breadcrumb" id="admBreadcrumb">Dashboard</div>
    <div class="adm-topbar-actions">
      <span class="adm-badge-admin">🛡️ Admin</span>
      <button class="btn-xs btn-blue" onclick="loadStats()">↻ Refresh</button>
    </div>
  </header>

  <!-- Content -->
  <div class="adm-content">

    <!-- ====== SECTION: DASHBOARD ====== -->
    <div class="adm-section active" id="section-dashboard">
      <div class="adm-stat-grid" id="statGrid">
        <div class="adm-loading" style="grid-column:1/-1">Loading statistik...</div>
      </div>

      <div class="adm-chart-grid">
        <div class="adm-card">
          <div class="adm-card-title">
            Transaksi Harian (30 Hari)
            <span style="font-size:11px;color:#64748B;font-weight:400" id="chartDateRange"></span>
          </div>
          <div class="chart-wrap"><canvas id="lineChart"></canvas></div>
        </div>
        <div class="adm-card">
          <div class="adm-card-title">Top User Aktif</div>
          <div id="topUsersList"></div>
        </div>
      </div>

      <div class="adm-card">
        <div class="adm-card-title">Aktivitas Terbaru</div>
        <div id="recentActivityList"></div>
      </div>
    </div>

    <!-- ====== SECTION: USERS ====== -->
    <div class="adm-section" id="section-users">
      <div class="adm-filter">
        <input type="search" id="userSearch" placeholder="🔍 Cari nama atau email..." />
        <select id="userRoleFilter">
          <option value="">Semua Role</option>
          <option value="admin">Admin</option>
          <option value="user">User</option>
        </select>
        <button class="btn-xs btn-purple" onclick="loadUsers()">Cari</button>
      </div>

      <div class="adm-card" style="padding:0">
        <div class="adm-table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Transaksi</th>
                <th>Total Saldo</th>
                <th>Daftar</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <tr><td colspan="9" class="adm-loading">Loading...</td></tr>
            </tbody>
          </table>
        </div>
        <div id="usersPagination" style="padding:12px 16px;display:flex;justify-content:flex-end;gap:6px"></div>
      </div>
    </div>

    <!-- ====== SECTION: ACTIVITY ====== -->
    <div class="adm-section" id="section-activity">
      <div class="adm-card" style="padding:0">
        <div style="padding:16px 20px;font-size:14px;font-weight:600;border-bottom:1px solid #334155">
          Log Aktivitas Sistem
        </div>
        <div class="adm-table-wrap">
          <table>
            <thead>
              <tr>
                <th>Waktu</th>
                <th>User</th>
                <th>Aksi</th>
                <th>Detail</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody id="activityTableBody">
              <tr><td colspan="5" class="adm-loading">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /adm-content -->
</main>

<script>
const ADMIN_NAME = <?= json_encode($admin['name']) ?>;
let _lineChart = null;
let _confirmCallback = null;

// ---- Navigation ----
document.querySelectorAll('.adm-nav-item').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('.adm-nav-item').forEach(i => i.classList.remove('active'));
    document.querySelectorAll('.adm-section').forEach(s => s.classList.remove('active'));
    item.classList.add('active');
    const sec = item.dataset.section;
    document.getElementById('section-' + sec).classList.add('active');
    document.getElementById('admBreadcrumb').textContent = item.textContent.trim();
    if (sec === 'users')    loadUsers();
    if (sec === 'activity') loadActivity();
  });
});

// ---- Toast ----
function toast(msg, color = '#A78BFA') {
  const el = document.getElementById('admToast');
  document.getElementById('admToastMsg').textContent = msg;
  el.style.borderLeftColor = color;
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 3000);
}

// ---- Confirm Modal ----
function showConfirm(title, desc, onOk, btnClass = 'adm-modal-btn-danger') {
  document.getElementById('confirmTitle').textContent = title;
  document.getElementById('confirmDesc').textContent  = desc;
  const btn = document.getElementById('confirmOkBtn');
  btn.className = 'adm-modal-btn ' + btnClass;
  _confirmCallback = onOk;
  document.getElementById('confirmModal').classList.add('open');
}
function closeConfirm() { document.getElementById('confirmModal').classList.remove('open'); }
document.getElementById('confirmOkBtn').addEventListener('click', () => { closeConfirm(); if (_confirmCallback) _confirmCallback(); });
document.getElementById('confirmModal').addEventListener('click', e => { if (e.target === document.getElementById('confirmModal')) closeConfirm(); });

// ---- Format Rp ----
function fRp(n) { return new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', minimumFractionDigits:0 }).format(n||0); }
function fDate(s) { if (!s) return '-'; return new Date(s).toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }); }

// ---- Load Stats ----
async function loadStats() {
  try {
    const res  = await fetch('../api/admin/stats.php');
    const data = await res.json();
    if (!data.success) { toast('Gagal load stats: ' + data.message, '#FCA5A5'); return; }
    const d = data.data;

    document.getElementById('statGrid').innerHTML = `
      <div class="adm-stat-card">
        <div class="adm-stat-icon" style="background:rgba(139,92,246,0.15)">👥</div>
        <div class="adm-stat-label">Total User</div>
        <div class="adm-stat-value">${d.users}</div>
        <div style="font-size:11px;color:#64748B">${d.new_users_month} baru bulan ini</div>
      </div>
      <div class="adm-stat-card">
        <div class="adm-stat-icon" style="background:rgba(59,130,246,0.15)">💳</div>
        <div class="adm-stat-label">Total Transaksi</div>
        <div class="adm-stat-value">${d.tx_count.toLocaleString()}</div>
        <div style="font-size:11px;color:#64748B">${d.wallets} dompet aktif</div>
      </div>
      <div class="adm-stat-card">
        <div class="adm-stat-icon" style="background:rgba(16,185,129,0.15)">📈</div>
        <div class="adm-stat-label">Total Pemasukan</div>
        <div class="adm-stat-value" style="font-size:18px;color:#6EE7B7">${fRp(d.total_income)}</div>
      </div>
      <div class="adm-stat-card">
        <div class="adm-stat-icon" style="background:rgba(239,68,68,0.15)">📉</div>
        <div class="adm-stat-label">Total Pengeluaran</div>
        <div class="adm-stat-value" style="font-size:18px;color:#FCA5A5">${fRp(d.total_expense)}</div>
      </div>`;

    renderLineChart(d.daily_stats);
    renderTopUsers(d.top_users);
    renderRecentActivity(d.recent_activity);
  } catch(e) {
    document.getElementById('statGrid').innerHTML = '<div class="adm-loading">Gagal memuat data. Cek koneksi server.</div>';
  }
}

function renderLineChart(daily) {
  const labels   = daily.map(d => new Date(d.date).toLocaleDateString('id-ID', { day:'2-digit', month:'short' }));
  const incomes  = daily.map(d => parseFloat(d.income));
  const expenses = daily.map(d => parseFloat(d.expense));
  const ctx = document.getElementById('lineChart');
  if (_lineChart) { _lineChart.destroy(); }
  _lineChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label:'Pemasukan',   data:incomes,  borderColor:'#10B981', backgroundColor:'rgba(16,185,129,0.08)', tension:0.4, fill:true, pointRadius:3 },
        { label:'Pengeluaran', data:expenses, borderColor:'#EF4444', backgroundColor:'rgba(239,68,68,0.08)',  tension:0.4, fill:true, pointRadius:3 }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ position:'top', labels:{ color:'#94A3B8', font:{family:'Poppins',size:11}, usePointStyle:true } }, tooltip:{ callbacks:{ label: ctx => ' '+fRp(ctx.parsed.y) } } },
      scales: {
        x:{ grid:{display:false}, ticks:{color:'#64748B',font:{family:'Poppins',size:10},maxTicksLimit:10} },
        y:{ grid:{color:'rgba(255,255,255,0.04)'}, ticks:{color:'#64748B',callback:v=>'Rp'+Intl.NumberFormat('id-ID').format(v),font:{family:'Poppins',size:10}} }
      }
    }
  });
}

function renderTopUsers(users) {
  const el = document.getElementById('topUsersList');
  if (!users.length) { el.innerHTML = '<div class="adm-loading">Belum ada data</div>'; return; }
  el.innerHTML = users.slice(0,8).map(u => `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #334155">
      <div style="display:flex;align-items:center;gap:8px">
        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#7C3AED,#8B5CF6);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700">${u.name[0].toUpperCase()}</div>
        <div>
          <div style="font-size:12px;font-weight:600">${u.name}</div>
          <div style="font-size:10px;color:#64748B">${u.email}</div>
        </div>
      </div>
      <span class="badge ${u.role==='admin'?'badge-admin':'badge-user'}" style="font-size:11px">${u.tx_count} tx</span>
    </div>`).join('');
}

function renderRecentActivity(activities) {
  const el = document.getElementById('recentActivityList');
  if (!activities.length) { el.innerHTML = '<div class="adm-loading">Belum ada aktivitas</div>'; return; }
  const iconMap = { login:'🔐', logout:'🚪', register:'✨', wallet_create:'👛', wallet_delete:'🗑️', transaction_create:'💸', transaction_delete:'❌', admin_activate_user:'✅', admin_deactivate_user:'🚫', admin_set_role:'🎭', admin_delete_user:'💀' };
  el.innerHTML = activities.slice(0,10).map(a => `
    <div class="activity-item">
      <div class="activity-dot" style="background:${a.action.includes('delete')?'#EF4444':a.action.includes('login')?'#10B981':'#7C3AED'}"></div>
      <div>
        <div class="activity-text"><strong>${iconMap[a.action]||'📌'} ${a.user_name||'Sistem'}</strong> — ${a.action.replace(/_/g,' ')}</div>
        <div class="activity-time">${fDate(a.created_at)} · IP: ${a.ip_address||'-'}</div>
      </div>
    </div>`).join('');
}

// ---- Load Users ----
let _usersPage = 1;
async function loadUsers(page = 1) {
  _usersPage = page;
  const search = document.getElementById('userSearch').value;
  const role   = document.getElementById('userRoleFilter').value;
  const tbody  = document.getElementById('usersTableBody');
  tbody.innerHTML = '<tr><td colspan="9" class="adm-loading">Loading...</td></tr>';

  try {
    const params = new URLSearchParams({ search, role, page, per_page: 15 });
    const res  = await fetch('../api/admin/users.php?' + params);
    const data = await res.json();
    if (!data.success) { toast(data.message, '#FCA5A5'); return; }
    const { users, total } = data.data;

    if (!users.length) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:32px;color:#64748B">Tidak ada user ditemukan</td></tr>';
      return;
    }

    tbody.innerHTML = users.map((u, i) => `
      <tr>
        <td style="color:#64748B">${(page-1)*15+i+1}</td>
        <td>
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#7C3AED,#8B5CF6);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">${u.name[0].toUpperCase()}</div>
            <span style="font-weight:600;font-size:13px">${u.name}</span>
          </div>
        </td>
        <td style="color:#94A3B8;font-size:12px">${u.email}</td>
        <td><span class="badge badge-${u.role}">${u.role === 'admin' ? '🛡️ Admin' : '👤 User'}</span></td>
        <td><span class="badge badge-${u.is_active ? 'active' : 'inactive'}">${u.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
        <td class="td-right">${u.tx_count}</td>
        <td class="td-right" style="color:#6EE7B7">${fRp(u.total_balance)}</td>
        <td style="font-size:11px;color:#64748B">${new Date(u.created_at).toLocaleDateString('id-ID')}</td>
        <td>
          <div style="display:flex;gap:4px;flex-wrap:wrap">
            ${u.role !== 'admin'
              ? `<button class="btn-xs btn-purple" onclick="userAction(${u.id},'make_admin')">→ Admin</button>`
              : `<button class="btn-xs btn-blue" onclick="userAction(${u.id},'make_user')">→ User</button>`}
            ${u.is_active
              ? `<button class="btn-xs btn-red" onclick="userAction(${u.id},'deactivate')">Nonaktifkan</button>`
              : `<button class="btn-xs btn-green" onclick="userAction(${u.id},'activate')">Aktifkan</button>`}
            <button class="btn-xs btn-red" onclick="userAction(${u.id},'delete')">🗑️</button>
          </div>
        </td>
      </tr>`).join('');

    // Pagination
    const totalPages = Math.ceil(total / 15);
    const pg = document.getElementById('usersPagination');
    if (totalPages <= 1) { pg.innerHTML = ''; return; }
    let html = '';
    for (let i = 1; i <= totalPages; i++) {
      html += `<button onclick="loadUsers(${i})" style="width:28px;height:28px;border-radius:6px;border:1px solid ${i===page?'#7C3AED':'#334155'};background:${i===page?'#7C3AED':'transparent'};color:${i===page?'#fff':'#94A3B8'};font-size:11px;cursor:pointer">${i}</button>`;
    }
    pg.innerHTML = html;
  } catch(e) { toast('Koneksi gagal', '#FCA5A5'); }
}

async function userAction(userId, action) {
  const labels = { deactivate:'Nonaktifkan akun?', activate:'Aktifkan akun?', make_admin:'Jadikan Admin?', make_user:'Jadikan User biasa?', delete:'Hapus akun ini permanen?' };
  showConfirm(labels[action] || 'Konfirmasi', 'Aksi ini akan segera diterapkan.', async () => {
    try {
      const res  = await fetch('../api/admin/users.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ user_id: userId, action })
      });
      const data = await res.json();
      if (data.success) { toast(data.message, '#6EE7B7'); loadUsers(_usersPage); }
      else toast(data.message, '#FCA5A5');
    } catch(e) { toast('Koneksi gagal', '#FCA5A5'); }
  });
}

// ---- Load Activity ----
async function loadActivity() {
  try {
    const res  = await fetch('../api/admin/stats.php');
    const data = await res.json();
    if (!data.success) return;
    const activities = data.data.recent_activity;
    const iconMap = { login:'🔐', logout:'🚪', register:'✨', wallet_create:'👛', wallet_delete:'🗑️', transaction_create:'💸', transaction_delete:'❌', admin_activate_user:'✅', admin_deactivate_user:'🚫', admin_set_role:'🎭', admin_delete_user:'💀' };
    const tbody = document.getElementById('activityTableBody');
    if (!activities.length) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:32px;color:#64748B">Belum ada aktivitas</td></tr>';
      return;
    }
    tbody.innerHTML = activities.map(a => `
      <tr>
        <td style="font-size:11px;color:#64748B;white-space:nowrap">${fDate(a.created_at)}</td>
        <td style="font-size:12px">${a.user_name || '<em style="color:#64748B">Sistem</em>'}</td>
        <td style="font-size:12px">${iconMap[a.action]||'📌'} ${a.action.replace(/_/g,' ')}</td>
        <td style="font-size:11px;color:#94A3B8">${a.details || '-'}</td>
        <td style="font-size:11px;color:#64748B">${a.ip_address || '-'}</td>
      </tr>`).join('');
  } catch(e) {}
}

// ---- Logout ----
async function handleLogout() {
  showConfirm('Keluar?', 'Anda akan keluar dari panel admin.', async () => {
    await fetch('../api/auth/logout.php', { method:'POST' });
    window.location.href = '../login.php';
  }, 'adm-modal-btn-confirm');
}

// ---- Init ----
loadStats();
document.getElementById('userSearch').addEventListener('keydown', e => { if (e.key === 'Enter') loadUsers(); });
document.getElementById('userRoleFilter').addEventListener('change', loadUsers);
</script>
</body>
</html>
