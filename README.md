# 💸 Digital Petty Cash System (Buku Kas Digital)
> **Solusi Digitalisasi Pencatatan Keuangan Personal yang Efisien, Akurat, dan Real-Time.**

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![Status](https://img.shields.io/badge/status-stable-green.svg)
![License](https://img.shields.io/badge/license-MIT-lightgrey.svg)

---

## 📌 Deskripsi Proyek
**Digital Petty Cash System** adalah aplikasi manajemen arus kas (cash flow) yang dirancang untuk menggantikan metode konvensional berbasis buku fisik. Proyek ini fokus pada tiga pilar utama: **Kecepatan Input**, **Akurasi Kalkulasi**, dan **Integritas Data**.

Didesain khusus untuk mahasiswa atau pengguna personal guna memantau distribusi pengeluaran harian tanpa kerumitan administrasi manual.

## 🚀 Fitur Utama (High-Impact)
- **⚡ Rapid Transaction Form:** Input jumlah, keterangan, dan tanggal dalam satu alur kerja yang intuitif.
- **📊 Smart Categorization:** Klasifikasi otomatis ke dalam kategori relevan (Makan, Hiburan, Tugas, Kiriman).
- **📉 Automated Balance Engine:** Algoritma yang secara otomatis menghitung `Total Pemasukan - Total Pengeluaran` setiap kali ada perubahan data.
- **📱 Responsive Dashboard:** Tampilan ringkasan finansial yang bersih dan mudah dibaca di berbagai ukuran layar.

## 🛠️ Stack Teknologi
* **Frontend:** [React.js / HTML5 & Tailwind CSS]
* **Database:** [LocalStorage / SQLite / Firebase]
* **Deployment:** [Vercel / Netlify / Localhost]

## 🏗️ Struktur Basis Data (ERD Preview)
Sistem menggunakan struktur data relasional sederhana untuk menjamin performa:
```sql
TABLE Transactions {
    id: INTEGER PRIMARY_KEY,
    date: DATE,
    description: TEXT,
    amount: DECIMAL,
    category: ENUM('Kiriman', 'Makan', 'Tugas', 'Hiburan'),
    type: ENUM('Income', 'Expense')
}
