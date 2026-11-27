// Halaman jadwal lapangan: deretan tanggal dan pemilih jam mulai.
// Server tetap memeriksa semua aturan booking dan menghitung harga.
// Skrip ini hanya kemudahan tampilan.

function formatRupiah(value) {
    return 'Rp' + value.toLocaleString('id-ID');
}

// Gulirkan tanggal, lapangan, dan paket yang terpilih ke tengah deretan
function initStrips() {
    document.querySelectorAll('.date-chip.is-active, .chip.is-active').forEach((element) => {
        element.scrollIntoView({ inline: 'center', block: 'nearest' });
    });
}

// Satu ketukan memilih satu jam mulai. Jam selesai otomatis (jam mulai + paket).
function initSlotPicker() {
    const grid = document.getElementById('slot-grid');
    const form = document.getElementById('slot-form');
    const bar = document.getElementById('slot-bar');

    if (!grid || !form || !bar) {
        return;
    }

    const buttons = Array.from(grid.querySelectorAll('.slot-btn'));

    if (buttons.length === 0) {
        return;
    }

    const fieldStart = document.getElementById('f-start');
    const fieldEnd = document.getElementById('f-end');
    const title = document.getElementById('bar-title');
    const total = document.getElementById('bar-total');
    const submit = document.getElementById('btn-continue');

    // hanya untuk tampilan; harga yang berlaku dihitung server
    const price = parseInt(grid.dataset.price, 10);
    const hours = parseInt(grid.dataset.hours, 10);

    let selected = null;

    function render() {
        buttons.forEach((button) => {
            const on = button === selected;

            button.classList.toggle('is-selected', on);
            button.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        if (selected === null) {
            fieldStart.value = '';
            fieldEnd.value = '';
            submit.disabled = true;
            bar.hidden = true;

            return;
        }

        // format HH:MM, sesuai yang diminta server
        fieldStart.value = selected.dataset.start;
        fieldEnd.value = selected.dataset.end;

        title.textContent = fieldStart.value + ' - ' + fieldEnd.value + ' \u00B7 ' + hours + ' jam';
        total.textContent = formatRupiah(price);

        submit.disabled = false;
        bar.hidden = false;
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            // ketuk lagi jam yang sama untuk membatalkan pilihan
            selected = selected === button ? null : button;
            render();
        });
    });

    // Jangan kirim form kalau belum ada pilihan
    form.addEventListener('submit', (event) => {
        if (!fieldStart.value || !fieldEnd.value) {
            event.preventDefault();
        }
    });

    render();
}

function init() {
    initStrips();
    initSlotPicker();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}