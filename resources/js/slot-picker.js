// Halaman jadwal lapangan: deretan tanggal dan pemilih jam.
// Server tetap memeriksa semua aturan booking. Skrip ini hanya kemudahan tampilan.

function formatRupiah(value) {
    return 'Rp' + value.toLocaleString('id-ID');
}

// Gulirkan tanggal dan lapangan yang terpilih ke tengah deretan
function initStrips() {
    document.querySelectorAll('.date-chip.is-active, .chip.is-active').forEach((element) => {
        element.scrollIntoView({ inline: 'center', block: 'nearest' });
    });
}

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
    const note = document.getElementById('slot-note');

    const price = parseInt(grid.dataset.price, 10);
    const maxHours = parseInt(grid.dataset.max, 10);

    // nomor urut slot yang dipilih, selalu berurutan
    let selected = [];

    const indexOf = (button) => parseInt(button.dataset.index, 10);
    const byIndex = (index) => buttons.find((button) => indexOf(button) === index);

    function render() {
        buttons.forEach((button) => {
            const on = selected.includes(indexOf(button));

            button.classList.toggle('is-selected', on);
            button.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        if (selected.length === 0) {
            fieldStart.value = '';
            fieldEnd.value = '';
            submit.disabled = true;
            bar.hidden = true;

            return;
        }

        const first = byIndex(selected[0]);
        const last = byIndex(selected[selected.length - 1]);

        // format HH:00, sesuai yang diminta server
        fieldStart.value = first.dataset.start.slice(0, 5);
        fieldEnd.value = last.dataset.end.slice(0, 5);

        title.textContent = fieldStart.value + ' - ' + fieldEnd.value + ' \u00B7 ' + selected.length + ' jam';
        total.textContent = formatRupiah(selected.length * price);

        submit.disabled = false;
        bar.hidden = false;
    }

    function handleTap(index) {
        note.textContent = '';

        const min = selected[0];
        const max = selected[selected.length - 1];

        if (selected.includes(index)) {
            if (selected.length === 1) {
                selected = [];
            } else if (index === max) {
                selected.pop();
            } else if (index === min) {
                selected.shift();
            } else {
                selected = [index]; // ketuk di tengah: mulai pilihan baru
            }
        } else if (selected.length > 0 && (index === max + 1 || index === min - 1)) {
            if (selected.length < maxHours) {
                if (index === max + 1) {
                    selected.push(index);
                } else {
                    selected.unshift(index);
                }
            } else {
                selected = [index];
                note.textContent = 'Maksimal ' + maxHours + ' jam. Pilihan baru dimulai dari jam ini.';
            }
        } else {
            selected = [index]; // tidak berurutan: mulai pilihan baru
        }

        render();
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => handleTap(indexOf(button)));
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