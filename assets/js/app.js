document.addEventListener('DOMContentLoaded', function() {

    // 0. Isi otomatis tanggal hari ini di form transaksi
    const dateInput = document.getElementById('date');
    if (dateInput && !dateInput.value) {
      dateInput.value = new Date().toISOString().slice(0, 10);
    }

    // 1. Fungsi Tombol "Quick Add" (+)
    const quickAddBtn = document.getElementById('quickAddFab');
    if (quickAddBtn) {
      quickAddBtn.addEventListener('click', () => {
        const amountInput = document.getElementById('amount');
        if (amountInput) {
          amountInput.focus(); 
          window.scrollTo({ top: amountInput.offsetTop - 100, behavior: 'smooth' });
        }
      });
    }

    // 2. Fungsi Tab Pemasukan / Pengeluaran (Segmented Control)
    const typeButtons = document.querySelectorAll('.quick-form-card .segmented-button');
    const typeSelect = document.getElementById('type');
    
    typeButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        typeButtons.forEach(b => b.classList.remove('is-active'));
        this.classList.add('is-active');
        const selectedType = this.getAttribute('data-type');
        if (typeSelect) typeSelect.value = selectedType;
        refreshCategoryOptions(selectedType); // <-- baris baru
      });
    });
    // 2b. Filter & pilih kategori sesuai jenis transaksi (income/expense)
    const categoryInput = document.getElementById('category');
    const catButtons = document.querySelectorAll('.cat-option');

    function refreshCategoryOptions(activeType) {
      let firstVisibleId = null;
      catButtons.forEach(btn => {
        const match = btn.getAttribute('data-type') === activeType;
        btn.style.display = match ? 'inline-block' : 'none';
        btn.classList.remove('is-active');
        if (match && firstVisibleId === null) {
          firstVisibleId = btn.getAttribute('data-id');
          btn.classList.add('is-active');
        }
      });
      if (categoryInput && firstVisibleId !== null) {
        categoryInput.value = firstVisibleId;
      }
    }

    catButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        catButtons.forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        if (categoryInput) categoryInput.value = btn.getAttribute('data-id');
      });
    });

    // Jalankan saat halaman pertama dibuka (default: income, sesuai tab aktif)
    refreshCategoryOptions('income');

    // 3. Fungsi Tab Periode Chart 
    const periodButtons = document.querySelectorAll('.period-tabs .segmented-button');
    periodButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        periodButtons.forEach(b => b.classList.remove('is-active'));
        this.classList.add('is-active');
      });
    });

    // 4. MENGIRIM DATA FORM KE DATABASE (AJAX)
    const forms = [
      'transactionForm', 'categoryForm', 'walletForm', 
      'budgetForm', 'recurringForm', 'filterForm'
    ];

    forms.forEach(formId => {
      const formElement = document.getElementById(formId);
      if (formElement) {
        formElement.addEventListener('submit', function(e) {
          e.preventDefault(); // Mencegah halaman refresh
          
          // Mengambil semua data dari form yang sedang diisi
          const formData = new FormData(this);
          
          // Memberi efek loading pada tombol
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
          submitBtn.disabled = true;

          // Mengirim data ke file PHP secara diam-diam (tanpa refresh)
          fetch('includes/transaksi.php', {
              method: 'POST',
              body: formData
          })
          .then(async response => {
              const data = await response.text();
              if (!response.ok || data.startsWith('Gagal')) {
                  throw new Error(data);
              }
              return data;
          })
          .then(data => {
              alert("Data berhasil diproses!");
              this.reset(); // Kosongkan form setelah berhasil
              window.location.reload(); // Refresh agar saldo & ringkasan terupdate
          })
          .catch(error => {
              console.error('Error:', error);
              alert("Terjadi kesalahan saat menyimpan data: " + error.message);
          })
          .finally(() => {
              // Kembalikan tombol ke keadaan semula
              submitBtn.innerHTML = originalText;
              submitBtn.disabled = false;
          });
        });
      }
    });

    // 5. Tombol Export CSV
    const exportBtn = document.getElementById('exportCsv');
    if (exportBtn) {
      exportBtn.addEventListener('click', () => {
        alert("Fungsi Export CSV dipicu! (Hubungkan ke file PHP Anda)");
      });
    }

});