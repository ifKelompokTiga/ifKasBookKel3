// =====================================================
// BukuKas Universal — Store (LocalStorage)
// =====================================================

const Store = (() => {
  let activeUserId = localStorage.getItem('bk_active_user_id') || '';

  const getKeys = () => ({
    user:         'bk_user_' + activeUserId,
    wallets:      'bk_wallets_' + activeUserId,
    transactions: 'bk_transactions_' + activeUserId,
    categories:   'bk_categories_' + activeUserId,
    settings:     'bk_settings_' + activeUserId,
  });

  // --- Auth Helpers ---
  const loadAccounts = () => JSON.parse(localStorage.getItem('bk_accounts') || '[]');
  const saveAccounts = (accs) => localStorage.setItem('bk_accounts', JSON.stringify(accs));

  // --- Helpers ---
  const load  = k => JSON.parse(localStorage.getItem(getKeys()[k]) || 'null');
  const save  = (k, v) => localStorage.setItem(getKeys()[k], JSON.stringify(v));
  const genId = () => 'id_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);

  // ---- USER ----
  function getUser() {
    return load('user') || {
      id: activeUserId, name: 'Pengguna', email: '',
      role: 'Individu', avatar: '', joined: new Date().toISOString()
    };
  }
  function saveUser(user) { 
    save('user', user);
    // Also update account name and email if changed
    const accs = loadAccounts();
    const accIndex = accs.findIndex(a => a.id === activeUserId);
    if(accIndex >= 0) {
      accs[accIndex].name = user.name;
      accs[accIndex].email = user.email; // Update email for login
      saveAccounts(accs);
    }
  }

  // ---- AUTHENTICATION ----
  function register(name, email, password) {
    const accs = loadAccounts();
    if (accs.find(a => a.email === email)) {
      throw new Error('Email sudah terdaftar');
    }
    const newId = genId();
    accs.push({ id: newId, name, email, password });
    saveAccounts(accs);
    return true;
  }

  function login(email, password) {
    const accs = loadAccounts();
    const acc = accs.find(a => a.email === email && a.password === password);
    if (!acc) throw new Error('Email atau password salah');
    
    activeUserId = acc.id;
    localStorage.setItem('bk_active_user_id', activeUserId);
    
    // Create initial user profile if doesn't exist
    if (!load('user')) {
      save('user', {
        id: activeUserId, name: acc.name, email: acc.email,
        role: 'Individu', avatar: '', joined: new Date().toISOString()
      });
    }
    
    return true;
  }

  function logout() {
    activeUserId = '';
    localStorage.removeItem('bk_active_user_id');
  }

  function isLoggedIn() {
    return !!activeUserId;
  }

  // ---- SETTINGS ----
  function getSettings() {
    return load('settings') || {
      theme: 'light', currency: 'IDR', dateFormat: 'DD/MM/YYYY',
      language: 'id', lowBalanceAlert: 100000, fontSize: 'normal'
    };
  }
  function saveSettings(s) { save('settings', s); }

  // ---- CATEGORIES ----
  const DEFAULT_CATEGORIES = [
    { id: 'cat_makan',    name: 'Makanan & Minuman', type: 'expense', icon: '🍔', color: '#F59E0B' },
    { id: 'cat_transport',name: 'Transportasi',       type: 'expense', icon: '🚗', color: '#3B82F6' },
    { id: 'cat_belanja',  name: 'Belanja',            type: 'expense', icon: '🛒', color: '#8B5CF6' },
    { id: 'cat_tagihan',  name: 'Tagihan & Utilitas', type: 'expense', icon: '⚡', color: '#EF4444' },
    { id: 'cat_hiburan',  name: 'Hiburan',            type: 'expense', icon: '🎮', color: '#EC4899' },
    { id: 'cat_kesehatan',name: 'Kesehatan',          type: 'expense', icon: '❤️', color: '#10B981' },
    { id: 'cat_pendidikan',name:'Pendidikan',          type: 'expense', icon: '📚', color: '#6366F1' },
    { id: 'cat_lainnya_e',name: 'Lainnya',            type: 'expense', icon: '📦', color: '#6B7280' },
    { id: 'cat_gaji',     name: 'Gaji',               type: 'income',  icon: '💼', color: '#10B981' },
    { id: 'cat_freelance',name: 'Freelance',          type: 'income',  icon: '💻', color: '#16A34A' },
    { id: 'cat_hadiah',   name: 'Hadiah',             type: 'income',  icon: '🎁', color: '#F59E0B' },
    { id: 'cat_investasi',name: 'Investasi',          type: 'income',  icon: '📈', color: '#3B82F6' },
    { id: 'cat_lainnya_i',name: 'Lainnya',            type: 'income',  icon: '💰', color: '#6B7280' },
  ];

  function getCategories() {
    return load('categories') || DEFAULT_CATEGORIES;
  }
  function saveCategories(cats) { save('categories', cats); }
  function addCategory(cat) {
    const cats = getCategories();
    cat.id = genId();
    cats.push(cat);
    saveCategories(cats);
    return cat;
  }
  function updateCategory(id, data) {
    const cats = getCategories().map(c => c.id === id ? { ...c, ...data } : c);
    saveCategories(cats);
  }
  function deleteCategory(id) {
    saveCategories(getCategories().filter(c => c.id !== id));
  }
  function getCategoryById(id) {
    return getCategories().find(c => c.id === id) || { name: 'Lainnya', icon: '📦', color: '#6B7280' };
  }

  // ---- WALLETS ----
  const WALLET_GRADIENTS = [
    'linear-gradient(135deg,#16A34A,#22C55E)',
    'linear-gradient(135deg,#2563EB,#3B82F6)',
    'linear-gradient(135deg,#7C3AED,#8B5CF6)',
    'linear-gradient(135deg,#DC2626,#EF4444)',
    'linear-gradient(135deg,#D97706,#F59E0B)',
    'linear-gradient(135deg,#0891B2,#06B6D4)',
    'linear-gradient(135deg,#BE185D,#EC4899)',
    'linear-gradient(135deg,#374151,#6B7280)',
  ];

  function getWallets() { return load('wallets') || []; }
  function saveWallets(w) { save('wallets', w); }

  function addWallet(wallet) {
    const wallets = getWallets();
    const usedGrads = wallets.map(w => w.gradient);
    const availGrad = WALLET_GRADIENTS.find(g => !usedGrads.includes(g)) || WALLET_GRADIENTS[wallets.length % WALLET_GRADIENTS.length];
    wallet.id = genId();
    wallet.gradient = wallet.gradient || availGrad;
    wallet.createdAt = new Date().toISOString();
    wallets.push(wallet);
    saveWallets(wallets);
    return wallet;
  }

  function updateWallet(id, data) {
    const wallets = getWallets().map(w => w.id === id ? { ...w, ...data } : w);
    saveWallets(wallets);
  }

  function deleteWallet(id) {
    saveWallets(getWallets().filter(w => w.id !== id));
    saveTransactions(getTransactions().filter(t => t.walletId !== id && t.toWalletId !== id));
  }

  function getWalletById(id) {
    return getWallets().find(w => w.id === id);
  }

  function recalcWalletBalance(id) {
    const txs = getTransactions().filter(t => t.walletId === id || t.toWalletId === id);
    const wallet = getWalletById(id);
    if (!wallet) return;
    let balance = wallet.initialBalance || 0;
    for (const t of txs) {
      if (t.type === 'income'   && t.walletId === id)   balance += t.amount;
      if (t.type === 'expense'  && t.walletId === id)   balance -= t.amount;
      if (t.type === 'transfer' && t.walletId === id)   balance -= t.amount;
      if (t.type === 'transfer' && t.toWalletId === id) balance += t.amount;
    }
    updateWallet(id, { balance });
    return balance;
  }

  // ---- TRANSACTIONS ----
  function getTransactions() { return load('transactions') || []; }
  function saveTransactions(t) { save('transactions', t); }

  function addTransaction(tx) {
    const txs = getTransactions();
    tx.id = genId();
    tx.createdAt = new Date().toISOString();
    txs.unshift(tx);
    saveTransactions(txs);
    // update wallet balance
    _adjustBalance(tx, 1);
    return tx;
  }

  function updateTransaction(id, data) {
    const txs = getTransactions();
    const old = txs.find(t => t.id === id);
    if (!old) return;
    // reverse old effect
    _adjustBalance(old, -1);
    const updated = { ...old, ...data, id, createdAt: old.createdAt, updatedAt: new Date().toISOString() };
    const newTxs  = txs.map(t => t.id === id ? updated : t);
    saveTransactions(newTxs);
    _adjustBalance(updated, 1);
    return updated;
  }

  function deleteTransaction(id) {
    const tx = getTransactions().find(t => t.id === id);
    if (tx) _adjustBalance(tx, -1);
    saveTransactions(getTransactions().filter(t => t.id !== id));
  }

  function _adjustBalance(tx, sign) {
    const wallets = getWallets();
    const update  = (wid, delta) => {
      const w = wallets.find(x => x.id === wid);
      if (w) { w.balance = (w.balance || 0) + delta; }
    };
    if (tx.type === 'income')   update(tx.walletId, sign * tx.amount);
    if (tx.type === 'expense')  update(tx.walletId, -sign * tx.amount);
    if (tx.type === 'transfer') {
      update(tx.walletId,   -sign * tx.amount);
      update(tx.toWalletId,  sign * tx.amount);
    }
    saveWallets(wallets);
  }

  // ---- FILTERS / QUERY ----
  function queryTransactions({ walletId, type, categoryId, dateFrom, dateTo, search } = {}) {
    let txs = getTransactions();
    if (walletId)    txs = txs.filter(t => t.walletId === walletId || t.toWalletId === walletId);
    if (type && type !== 'all') txs = txs.filter(t => t.type === type);
    if (categoryId)  txs = txs.filter(t => t.categoryId === categoryId);
    if (dateFrom)    txs = txs.filter(t => new Date(t.date) >= new Date(dateFrom));
    if (dateTo)      txs = txs.filter(t => new Date(t.date) <= new Date(dateTo));
    if (search)      txs = txs.filter(t => (t.note||'').toLowerCase().includes(search.toLowerCase()) ||
                                           (t.description||'').toLowerCase().includes(search.toLowerCase()));
    return txs;
  }

  // ---- SUMMARY ----
  function getMonthlySummary(year, month) {
    const txs = getTransactions().filter(t => {
      const d = new Date(t.date);
      return d.getFullYear() === year && d.getMonth() === month;
    });
    const income  = txs.filter(t => t.type === 'income' ).reduce((s, t) => s + t.amount, 0);
    const expense = txs.filter(t => t.type === 'expense').reduce((s, t) => s + t.amount, 0);
    return { income, expense, net: income - expense, count: txs.length };
  }

  function getTotalBalance() {
    return getWallets().reduce((s, w) => s + (w.balance || 0), 0);
  }

  // ---- SEED DATA ----
  function seedDemoData() {
    const w1 = addWallet({ name: 'Kas Utama',  type: 'cash',    initialBalance: 2500000, balance: 2500000, description: 'Uang tunai harian' });
    const w2 = addWallet({ name: 'Bank BCA',   type: 'bank',    initialBalance: 8750000, balance: 8750000, description: 'Rekening tabungan' });
    const w3 = addWallet({ name: 'GoPay',      type: 'ewallet', initialBalance:  350000, balance:  350000, description: 'Dompet digital' });

    const now   = new Date();
    const mon   = now.getMonth();
    const yr    = now.getFullYear();
    const dates = n => {
      const d = new Date(yr, mon, n);
      return d.toISOString().slice(0, 10);
    };

    const demoTxs = [
      { walletId: w2.id, type: 'income',  categoryId: 'cat_gaji',      amount: 8000000, note: 'Gaji bulan ini', date: dates(1) },
      { walletId: w1.id, type: 'expense', categoryId: 'cat_makan',     amount:   45000, note: 'Makan siang warteg', date: dates(2) },
      { walletId: w1.id, type: 'expense', categoryId: 'cat_transport', amount:   30000, note: 'Ojek online', date: dates(3) },
      { walletId: w2.id, type: 'expense', categoryId: 'cat_tagihan',   amount:  250000, note: 'Bayar listrik PLN', date: dates(4) },
      { walletId: w3.id, type: 'expense', categoryId: 'cat_belanja',   amount:  125000, note: 'Belanja online shopee', date: dates(5) },
      { walletId: w1.id, type: 'income',  categoryId: 'cat_freelance', amount:  750000, note: 'Project desain logo', date: dates(6) },
      { walletId: w1.id, type: 'expense', categoryId: 'cat_kesehatan', amount:   85000, note: 'Beli obat apotek', date: dates(8) },
      { walletId: w2.id, type: 'expense', categoryId: 'cat_hiburan',   amount:   50000, note: 'Netflix subscription', date: dates(9) },
      { walletId: w1.id, type: 'expense', categoryId: 'cat_makan',     amount:   65000, note: 'Dinner restoran', date: dates(10) },
      { walletId: w3.id, type: 'expense', categoryId: 'cat_transport', amount:   22000, note: 'Grab car', date: dates(11) },
      { walletId: w2.id, type: 'income',  categoryId: 'cat_investasi', amount:  320000, note: 'Dividen saham', date: dates(12) },
      { walletId: w1.id, type: 'transfer',toWalletId: w3.id, categoryId: null, amount: 200000, note: 'Top up GoPay', date: dates(13) },
      { walletId: w1.id, type: 'expense', categoryId: 'cat_pendidikan',amount:  150000, note: 'Beli buku online', date: dates(14) },
      { walletId: w1.id, type: 'expense', categoryId: 'cat_makan',     amount:   35000, note: 'Sarapan bubur ayam', date: dates(15) },
      { walletId: w2.id, type: 'expense', categoryId: 'cat_belanja',   amount:  450000, note: 'Baju baru', date: dates(15) },
      { walletId: w3.id, type: 'income',  categoryId: 'cat_hadiah',    amount:  100000, note: 'Hadiah ulang tahun', date: dates(16) },
    ];

    // Add transactions one by one to properly update balances
    demoTxs.forEach(t => addTransaction(t));
  }

  // ---- BACKUP / RESTORE ----
  function exportBackup() {
    return JSON.stringify({
      version: 1, exportedAt: new Date().toISOString(),
      user: getUser(), settings: getSettings(),
      wallets: getWallets(), transactions: getTransactions(), categories: getCategories()
    }, null, 2);
  }

  function importBackup(jsonStr) {
    const data = JSON.parse(jsonStr);
    if (!data.version) throw new Error('Format backup tidak valid');
    if (data.user)         save('user', data.user);
    if (data.settings)     save('settings', data.settings);
    if (data.wallets)      save('wallets', data.wallets);
    if (data.transactions) save('transactions', data.transactions);
    if (data.categories)   save('categories', data.categories);
  }

  function clearAllData() {
    Object.values(getKeys()).forEach(k => localStorage.removeItem(k));
  }

  // ---- INIT ----
  function init() {
    // Intentionally left empty to start with 0 balance and no demo data
  }

  return {
    genId,
    // Auth
    register, login, logout, isLoggedIn,
    // User
    getUser, saveUser,
    // Settings
    getSettings, saveSettings,
    // Categories
    getCategories, addCategory, updateCategory, deleteCategory, getCategoryById,
    // Wallets
    getWallets, addWallet, updateWallet, deleteWallet, getWalletById, getTotalBalance,
    // Transactions
    getTransactions, addTransaction, updateTransaction, deleteTransaction,
    queryTransactions, getMonthlySummary,
    // Backup
    exportBackup, importBackup, clearAllData,
    // Gradients
    WALLET_GRADIENTS,
    // Init
    init
  };
})();
