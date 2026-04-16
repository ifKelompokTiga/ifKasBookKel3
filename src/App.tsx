import React, { useState, useMemo, useEffect } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { 
  Wallet, 
  TrendingUp, 
  TrendingDown, 
  Plus, 
  Trash2, 
  Calendar, 
  Tag, 
  FileText, 
  DollarSign
} from 'lucide-react';

// === Types ===
type TransactionType = 'income' | 'expense';

interface Transaction {
  id: string;
  amount: number;
  description: string;
  date: string;
  category: string;
  type: TransactionType;
}

// === Constants ===
// "Kiriman" is categorised as Income default. "Makan", "Hiburan", "Tugas" as Expenses.
const CATEGORIES = {
  income: ['Kiriman', 'Gaji', 'Bonus', 'Pendapatan Lain'],
  expense: ['Makan', 'Hiburan', 'Tugas', 'Transportasi', 'Pengeluaran Lain'],
};

export default function App() {
  // === State ===
  // Load data from localStorage on mount (optional but good for UX)
  const [transactions, setTransactions] = useState<Transaction[]>(() => {
    const saved = localStorage.getItem('bukuKasData');
    if (saved) {
      try {
        return JSON.parse(saved);
      } catch (e) {
        return [];
      }
    }
    return [];
  });

  const [amount, setAmount] = useState<string>('');
  const [description, setDescription] = useState<string>('');
  const [date, setDate] = useState<string>(new Date().toISOString().split('T')[0]);
  const [type, setType] = useState<TransactionType>('expense');
  const [category, setCategory] = useState<string>(CATEGORIES.expense[0]);
  const [error, setError] = useState<string>('');

  // === Effects ===
  // Save to localStorage whenever transactions change
  useEffect(() => {
    localStorage.setItem('bukuKasData', JSON.stringify(transactions));
  }, [transactions]);

  // === Computed Values ===
  const { totalIncome, totalExpense, balance } = useMemo(() => {
    return transactions.reduce(
      (acc, curr) => {
        if (curr.type === 'income') {
          acc.totalIncome += curr.amount;
          acc.balance += curr.amount;
        } else {
          acc.totalExpense += curr.amount;
          acc.balance -= curr.amount;
        }
        return acc;
      },
      { totalIncome: 0, totalExpense: 0, balance: 0 }
    );
  }, [transactions]);

  // Sort transactions by date descending
  const sortedTransactions = useMemo(() => {
    return [...transactions].sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  }, [transactions]);

  // === Handlers ===
  const handleTypeChange = (newType: TransactionType) => {
    setType(newType);
    setCategory(CATEGORIES[newType][0]);
  };

  const handleAddTransaction = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    // Input Validation
    const parsedAmount = parseFloat(amount);
    if (!amount || isNaN(parsedAmount) || parsedAmount <= 0) {
      setError('Masukkan jumlah yang valid (lebih dari 0).');
      return;
    }
    if (!description.trim()) {
      setError('Keterangan tidak boleh kosong.');
      return;
    }
    if (!date) {
      setError('Tanggal tidak boleh kosong.');
      return;
    }
    if (!category) {
      setError('Kategori harus dipilih.');
      return;
    }

    const newTransaction: Transaction = {
      id: crypto.randomUUID(),
      amount: parsedAmount,
      description: description.trim(),
      date,
      category,
      type,
    };

    setTransactions((prev) => [newTransaction, ...prev]);
    
    // Reset form except date and type
    setAmount('');
    setDescription('');
  };

  const handleDelete = (id: string) => {
    setTransactions((prev) => prev.filter(t => t.id !== id));
  };

  // Helper formatting function
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(value);
  };

  const formatDate = (dateString: string) => {
    return new Intl.DateTimeFormat('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    }).format(new Date(dateString));
  };

  return (
    <div className="min-h-screen bg-[var(--color-natural-bg)] text-[var(--color-natural-dark)] font-sans p-4 sm:p-6 lg:p-8">
      <div className="max-w-5xl mx-auto space-y-6">
        
        {/* Header */}
        <header className="flex items-center gap-4 mb-4">
          <div className="bg-[var(--color-natural-pill)] p-3 rounded-xl text-[var(--color-natural-dark)] shadow-sm">
            <Wallet size={28} />
          </div>
          <div>
            <h1 className="text-[28px] font-bold font-serif text-[var(--color-natural-dark)]">
              Buku Kas Digital
            </h1>
            <p className="text-[var(--color-natural-muted)] text-sm font-medium tracking-wide">
              {new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date())}
            </p>
          </div>
        </header>

        {/* Dashboard / Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-[var(--color-natural-stat)] p-5 rounded-[16px] border border-[var(--color-natural-border)] shadow-sm flex items-center justify-between"
          >
            <div>
              <p className="text-[11px] font-semibold text-[var(--color-natural-muted)] uppercase tracking-[0.05em] mb-1">Total Saldo</p>
              <h2 className={`text-[22px] font-bold mt-1 ${balance < 0 ? 'text-[var(--color-natural-expense)]' : 'text-[var(--color-natural-dark)]'}`}>
                {formatCurrency(balance)}
              </h2>
            </div>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="bg-[var(--color-natural-stat)] p-5 rounded-[16px] border border-[var(--color-natural-border)] shadow-sm flex items-center justify-between"
          >
            <div>
              <p className="text-[11px] font-semibold text-[var(--color-natural-muted)] uppercase tracking-[0.05em] mb-1">Total Pemasukan</p>
              <h2 className="text-[22px] font-bold text-[var(--color-natural-income)] mt-1">
                +{formatCurrency(totalIncome)}
              </h2>
            </div>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
            className="bg-[var(--color-natural-stat)] p-5 rounded-[16px] border border-[var(--color-natural-border)] shadow-sm flex items-center justify-between"
          >
            <div>
              <p className="text-[11px] font-semibold text-[var(--color-natural-muted)] uppercase tracking-[0.05em] mb-1">Total Pengeluaran</p>
              <h2 className="text-[22px] font-bold text-[var(--color-natural-expense)] mt-1">
                -{formatCurrency(totalExpense)}
              </h2>
            </div>
          </motion.div>
        </div>

        {/* Main Content: Form & List */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          {/* Form Col */}
          <div className="lg:col-span-1">
            <div className="bg-[var(--color-natural-card)] p-6 rounded-[20px] shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-[var(--color-natural-border)]">
              <h3 className="text-[18px] font-serif font-bold text-[var(--color-natural-dark)] mb-5">Tambah Catatan</h3>
              
              <form onSubmit={handleAddTransaction} className="space-y-4">
                {/* Date */}
                <div className="space-y-1.5">
                  <label className="text-[12px] uppercase tracking-[0.05em] font-semibold text-[var(--color-natural-muted)] block mb-1">
                    Tanggal
                  </label>
                  <input
                    type="date"
                    value={date}
                    onChange={(e) => setDate(e.target.value)}
                    className="w-full px-4 py-3 rounded-xl border border-[var(--color-natural-border)] bg-[var(--color-natural-input)] text-[14px] text-[var(--color-natural-dark)] focus:border-[var(--color-natural-accent)] focus:ring-1 focus:ring-[var(--color-natural-accent)] transition-all outline-none appearance-none"
                  />
                </div>

                {/* Description */}
                <div className="space-y-1.5">
                  <label className="text-[12px] uppercase tracking-[0.05em] font-semibold text-[var(--color-natural-muted)] block mb-1">
                    Keterangan
                  </label>
                  <input
                    type="text"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    placeholder="Contoh: Makan siang nasi padang"
                    className="w-full px-4 py-3 rounded-xl border border-[var(--color-natural-border)] bg-[var(--color-natural-input)] text-[14px] text-[var(--color-natural-dark)] focus:border-[var(--color-natural-accent)] focus:ring-1 focus:ring-[var(--color-natural-accent)] transition-all outline-none"
                  />
                </div>

                {/* Category */}
                <div className="space-y-1.5">
                  <label className="text-[12px] uppercase tracking-[0.05em] font-semibold text-[var(--color-natural-muted)] block mb-1">
                    Kategori
                  </label>
                  <select
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                    className="w-full px-4 py-3 rounded-xl border border-[var(--color-natural-border)] bg-[var(--color-natural-input)] text-[14px] text-[var(--color-natural-dark)] focus:border-[var(--color-natural-accent)] focus:ring-1 focus:ring-[var(--color-natural-accent)] transition-all outline-none appearance-none"
                  >
                    {CATEGORIES[type].map((cat) => (
                      <option key={cat} value={cat}>
                        {cat}
                      </option>
                    ))}
                  </select>
                </div>

                {/* Amount */}
                <div className="space-y-1.5">
                  <label className="text-[12px] uppercase tracking-[0.05em] font-semibold text-[var(--color-natural-muted)] block mb-1">
                    Jumlah (Rp)
                  </label>
                  <input
                    type="number"
                    value={amount}
                    onChange={(e) => setAmount(e.target.value)}
                    placeholder="0"
                    className="w-full px-4 py-3 rounded-xl border border-[var(--color-natural-border)] bg-[var(--color-natural-input)] text-[14px] text-[var(--color-natural-dark)] focus:border-[var(--color-natural-accent)] focus:ring-1 focus:ring-[var(--color-natural-accent)] transition-all outline-none"
                  />
                </div>

                {/* Type Selection built as radio buttons to match design */}
                <div className="flex gap-4 pt-2 pb-1">
                  <label className="flex items-center cursor-pointer text-[var(--color-natural-dark)] text-sm">
                    <input
                      type="radio"
                      name="type"
                      checked={type === 'expense'}
                      onChange={() => handleTypeChange('expense')}
                      className="w-4 h-4 mr-2 accent-[var(--color-natural-expense)]"
                    />
                    Pengeluaran
                  </label>
                  <label className="flex items-center cursor-pointer text-[var(--color-natural-dark)] text-sm">
                    <input
                      type="radio"
                      name="type"
                      checked={type === 'income'}
                      onChange={() => handleTypeChange('income')}
                      className="w-4 h-4 mr-2 accent-[var(--color-natural-income)]"
                    />
                    Pemasukan
                  </label>
                </div>

                {/* Error Message */}
                {error && (
                  <motion.div 
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    className="text-[var(--color-natural-expense)] text-[13px] font-medium bg-[#Fdf3f0] p-3 rounded-xl border border-[#F4EBE8]"
                  >
                    {error}
                  </motion.div>
                )}

                {/* Submit Button */}
                <button
                  type="submit"
                  className="w-full mt-2 bg-[var(--color-natural-accent)] text-white font-semibold py-3.5 px-4 rounded-xl transition-all hover:brightness-105 active:scale-[0.98]"
                >
                  Simpan Transaksi
                </button>
              </form>
            </div>
          </div>

          {/* List Col */}
          <div className="lg:col-span-2">
            <div className="bg-[var(--color-natural-card)] p-6 rounded-[20px] shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-[var(--color-natural-border)] h-full min-h-[500px]">
              <h3 className="text-[18px] font-serif font-bold text-[var(--color-natural-dark)] mb-4">Riwayat Transaksi</h3>
              
              {sortedTransactions.length === 0 ? (
                <div className="flex flex-col items-center justify-center h-[300px] text-[var(--color-natural-muted)] space-y-4">
                  <div className="bg-[var(--color-natural-stat)] p-4 rounded-full">
                    <FileText size={40} className="text-[var(--color-natural-muted)] opacity-50" />
                  </div>
                  <p>Belum ada transaksi. Silakan tambah baru.</p>
                </div>
              ) : (
                <div className="space-y-0 overflow-y-auto pr-2 custom-scrollbar max-h-[380px]">
                  {/* Table Header matching design */}
                  <div className="grid grid-cols-[100px_1fr_120px_140px] px-4 py-3 text-[11px] uppercase tracking-[0.05em] text-[var(--color-natural-muted)] border-b border-[var(--color-natural-border)]">
                    <div>Tanggal</div>
                    <div>Keterangan</div>
                    <div>Kategori</div>
                    <div className="text-right">Jumlah</div>
                  </div>

                  <AnimatePresence initial={false}>
                    {sortedTransactions.map((tx) => (
                      <motion.div
                        key={tx.id}
                        initial={{ opacity: 0, x: -10 }}
                        animate={{ opacity: 1, x: 0 }}
                        exit={{ opacity: 0, height: 0, overflow: 'hidden' }}
                        transition={{ duration: 0.2 }}
                        className="grid grid-cols-[100px_1fr_120px_140px] items-center px-4 py-4 border-b border-[#F5F5F5] text-[14px] hover:bg-[var(--color-natural-stat)] transition-all group"
                      >
                        <div className="text-[var(--color-natural-dark)]">
                          {formatDate(tx.date).replace(/ 20\d\d$/, '')}
                        </div>
                        
                        <div className="text-[var(--color-natural-dark)] font-medium truncate pr-4">
                          {tx.description}
                        </div>
                        
                        <div>
                          <span className="px-3 py-1 bg-[var(--color-natural-pill)] rounded-full text-[11px] font-semibold text-[var(--color-natural-dark)] inline-block">
                            {tx.category}
                          </span>
                        </div>
                        
                        <div className="flex items-center justify-end gap-2 text-right">
                          <p className={`font-semibold whitespace-nowrap align-right ${
                            tx.type === 'income' ? 'text-[var(--color-natural-income)]' : 'text-[var(--color-natural-expense)]'
                          }`}>
                            {tx.type === 'income' ? '+' : '-'}{formatCurrency(tx.amount)}
                          </p>
                          <button
                            onClick={() => handleDelete(tx.id)}
                            className="p-1.5 text-[var(--color-natural-muted)] hover:text-[var(--color-natural-orange)] rounded-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 absolute right-4"
                            aria-label="Hapus transaksi"
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      </motion.div>
                    ))}
                  </AnimatePresence>
                </div>
              )}
            </div>
          </div>
        </div>

      </div>
    </div>
  );
}

