/*=================
CHART INITIALIZATION
==================*/
// Pastikan data diambil dari PHP di luar blok Chart
const bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
const pemasukan = <?= json_encode($dataMasuk); ?>;
const pengeluaran = <?= json_encode($dataKeluar); ?>;

// 1. Line Chart
const lineChartElement = document.getElementById("lineChart");
new Chart(lineChartElement, {
    type: 'line',
    data: {
        labels: bulan,
        datasets: [
            {
                label: 'Pemasukan',
                data: pemasukan,
                borderWidth: 3,
                fill: true,
                tension: .4
            },
            {
                label: 'Pengeluaran',
                data: pengeluaran,
                borderWidth: 3,
                fill: true,
                tension: .4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true } }
    }
});

// 2. Bar Chart
const barChartElement = document.getElementById("barChart");
new Chart(barChartElement, {
    type: 'bar',
    data: {
        labels: bulan,
        datasets: [
            { label: 'Pemasukan', data: pemasukan },
            { label: 'Pengeluaran', data: pengeluaran }
        ]
    },
    options: { responsive: true }
});

// 3. Pie Chart
const pieElement = document.getElementById("pieChart");
new Chart(pieElement, {
    type: 'pie',
    data: {
        labels: ['Masuk', 'Keluar'],
        datasets: [{
            data: [80, 20] // Sesuaikan data ini jika ingin dinamis dari PHP
        }]
    }
});

/*=================
DIGITAL CLOCK
==================*/
function updateClock() {
    const now = new Date();
    const clockElement = document.getElementById("clock");
    if(clockElement) {
        clockElement.innerHTML = now.toLocaleTimeString('id-ID');
    }
}
setInterval(updateClock, 1000);
updateClock();

/*=================
SIDEBAR TOGGLE
==================*/
const btn = document.getElementById("toggleSidebar");
const sidebar = document.getElementById("sidebar");
const main = document.getElementById("mainContent");

if(btn) {
    btn.onclick = function() {
        sidebar.classList.toggle("close");
        main.classList.toggle("full");
    };
}

/*=================
DARK MODE
==================*/
const dark = document.getElementById("darkToggle");
if(dark) {
    dark.onclick = function() {
        document.body.classList.toggle("dark");
    };
}

/*========================
CALENDAR
=========================*/
const calendar = document.getElementById("calendar");
if(calendar) {
    const today = new Date();
    calendar.innerHTML = `
        <h2 style="font-size:60px">${today.getDate()}</h2>
        <p>${today.toLocaleDateString('id-ID', {
            weekday: 'long',
            month: 'long',
            year: 'numeric'
        })}</p>
    `;
}