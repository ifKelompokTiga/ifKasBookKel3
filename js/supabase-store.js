// =====================================================
// BukuKas Universal — Supabase Store Implementation
// =====================================================
// CATATAN: Karena Supabase bersifat Asynchronous, pastikan Anda mengubah fungsi 
// di app.js / UI untuk menggunakan async/await saat memanggil fungsi-fungsi ini.

import { supabase } from './supabase-config.js';

const SupabaseStore = (() => {

  // Caching lokal untuk UX yang lebih cepat
  let activeUser = null;
  let cachedWallets = [];
  let cachedCategories = [];
  let cachedTransactions = [];

  // ==========================================
  // AUTHENTICATION
  // ==========================================

  async function register(name, email, password) {
    // Daftar ke Auth Supabase
    const { data: authData, error: authErr } = await supabase.auth.signUp({
      email,
      password,
    });
    if (authErr) throw new Error(authErr.message);

    // Simpan profil ke tabel public
    if (authData.user) {
      const { error: profileErr } = await supabase
        .from('profiles')
        .insert([{
          id: authData.user.id,
          name: name,
          email: email,
          role: 'Individu',
          theme: 'light',
          currency: 'IDR'
        }]);
      if (profileErr) throw new Error(profileErr.message);
    }
    return authData.user;
  }

  async function login(email, password) {
    const { data, error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) throw new Error(error.message);
    activeUser = data.user;
    return data.user;
  }

  async function logout() {
    const { error } = await supabase.auth.signOut();
    if (error) throw new Error(error.message);
    activeUser = null;
  }

  async function checkAuthStatus() {
    const { data } = await supabase.auth.getSession();
    if (data.session) {
      activeUser = data.session.user;
      return true;
    }
    return false;
  }

  // ==========================================
  // PROFILE & SETTINGS
  // ==========================================
  
  async function getUserProfile() {
    if (!activeUser) return null;
    const { data, error } = await supabase
      .from('profiles')
      .select('*')
      .eq('id', activeUser.id)
      .single();
    if (error) throw new Error(error.message);
    return data;
  }

  async function updateUserProfile(profileData) {
    if (!activeUser) return;
    const { error } = await supabase
      .from('profiles')
      .update(profileData)
      .eq('id', activeUser.id);
    if (error) throw new Error(error.message);
  }

  // ==========================================
  // WALLETS
  // ==========================================
  
  async function getWallets() {
    const { data, error } = await supabase
      .from('wallets')
      .select('*')
      .order('created_at', { ascending: true });
    
    if (error) throw new Error(error.message);
    cachedWallets = data;
    return data;
  }

  async function addWallet(walletData) {
    walletData.user_id = activeUser.id; // Harus ditambahkan untuk validasi RLS
    const { data, error } = await supabase
      .from('wallets')
      .insert([walletData])
      .select();

    if (error) throw new Error(error.message);
    return data[0];
  }

  async function updateWallet(id, walletData) {
    const { error } = await supabase
      .from('wallets')
      .update(walletData)
      .eq('id', id);
    if (error) throw new Error(error.message);
  }

  async function deleteWallet(id) {
    const { error } = await supabase
      .from('wallets')
      .delete()
      .eq('id', id);
    if (error) throw new Error(error.message);
  }

  // ==========================================
  // TRANSACTIONS
  // ==========================================
  
  // Mengambil transaksi (Bisa juga disubscribe dengan Realtime jika diaktifkan)
  async function getTransactions() {
    const { data, error } = await supabase
      .from('transactions')
      .select('*')
      .order('date', { ascending: false });

    if (error) throw new Error(error.message);
    cachedTransactions = data;
    return data;
  }

  async function addTransaction(txData) {
    txData.user_id = activeUser.id;
    const { data, error } = await supabase
      .from('transactions')
      .insert([txData])
      .select();

    if (error) throw new Error(error.message);
    
    // Note: Saldo wallet sebaiknya diupdate melalui Supabase Database Trigger (Function) 
    // agar konsisten, tapi bisa juga dilakukan updateWallet secara manual di JS.
    
    return data[0].id;
  }

  async function deleteTransaction(id) {
    const { error } = await supabase
      .from('transactions')
      .delete()
      .eq('id', id);
    if (error) throw new Error(error.message);
  }

  return {
    register, login, logout, checkAuthStatus,
    getUserProfile, updateUserProfile,
    getWallets, addWallet, updateWallet, deleteWallet,
    getTransactions, addTransaction, deleteTransaction
  };

})();

export default SupabaseStore;
