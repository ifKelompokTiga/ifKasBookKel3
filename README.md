# Buku Kas Digital

Buku Kas Digital adalah aplikasi web pencatatan keuangan sederhana untuk memantau pemasukan dan pengeluaran harian. Aplikasi ini dirancang dengan antarmuka yang bersih, fungsional, dan estetis menggunakan tema khas "Natural Tones".

## ✨ Fitur Utama

- **Dashboard Ringkasan Real-time**: Menampilkan Saldo Saat Ini, Total Pemasukan, dan Total Pengeluaran.
- **Pencatatan Transaksi Cerdas**: Tambahkan data pemasukan atau pengeluaran lengkap dengan Jumlah, Keterangan, Tanggal, dan Kategori.
- **Pilihan Kategori Dinamis**: Menampilkan dropdown kategori yang otomatis menyesuaikan dengan jenis transaksi (misal: "Makan" untuk pengeluaran, "Gaji" untuk pemasukan).
- **Tabel Riwayat Transaksi**: Menampilkan daftar riwayat secara terurut dari terbaru ke terlama dengan gaya desain tabel editorial.
- **Penyimpanan Lokal (Local Storage)**: Seluruh data akan disimpan otomatis di dalam *browser local storage*. Data Anda tidak akan hilang meskipun halaman di-refresh atau ditutup.

## 🛠️ Tech Stack

- **Framework:** React 19
- **Bahasa:** TypeScript
- **Bundler:** Vite
- **Styling:** Tailwind CSS v4
- **Ikon:** Lucide React
- **Animasi:** Motion

## 🚀 Cara Menjalankan Secara Lokal

Ikuti langkah-langkah berikut untuk menjalankan project ini di komputer Anda:

1. **Clone repositori ini**
   ```bash
   git clone https://github.com/username-anda/buku-kas-digital.git
   cd buku-kas-digital
   ```

2. **Instal dependencies**
   Pastikan Anda sudah menginstal [Node.js](https://nodejs.org/). Kemudian jalankan:
   ```bash
   npm install
   ```

3. **Jalankan Development Server**
   ```bash
   npm run dev
   ```

4. **Buka Aplikasi**
   Buka peramban (browser) Anda dan kunjungi `http://localhost:3000` (atau port lain yang ditunjukkan pada terminal Anda).

## 📦 Build untuk Produksi

Bila Anda ingin mem-build aplikasi ini agar siap untuk di-deploy (misal di Vercel, Netlify, atau GitHub Pages), jalankan:

```bash
npm run build
```

Hasil build aplikasi (file statis) akan digenerate ke dalam direktori `dist/`.

## 📄 Lisensi

Project ini berada di bawah lisensi [MIT License](LICENSE). Anda bebas untuk menggunakan, memodifikasi, dan mendistribusikan secara personal maupun komersial.
