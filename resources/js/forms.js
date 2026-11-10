// Perilaku formulir yang dipakai di banyak halaman.

// 1. Cegah kirim ganda: tambahkan atribut data-once pada <form>.
//    Tombol kirim dinonaktifkan setelah diketuk. Teks sementara bisa diatur dengan data-loading.
function resetForm(form) {
    delete form.dataset.submitting;

    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = false;

        if (button.dataset.label) {
            button.textContent = button.dataset.label;
        }
    });
}

document.addEventListener('submit', (event) => {
    const form = event.target;

    // event.defaultPrevented: misalnya pengguna menekan Batal pada kotak konfirmasi
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-once') || event.defaultPrevented) {
        return;
    }

    if (form.dataset.submitting === 'true') {
        event.preventDefault();

        return;
    }

    form.dataset.submitting = 'true';

    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.dataset.label = button.textContent;
        button.disabled = true;

        if (button.dataset.loading) {
            button.textContent = button.dataset.loading;
        }
    });
});

// Saat pengguna menekan tombol kembali, halaman bisa dipulihkan dari cache dengan tombol masih nonaktif
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        document.querySelectorAll('form[data-once]').forEach(resetForm);
    }
});

// 2. Tampilkan atau sembunyikan password: <button data-toggle-password="#id-input">
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-toggle-password]');

    if (!toggle) {
        return;
    }

    const input = document.querySelector(toggle.dataset.togglePassword);

    if (!input) {
        return;
    }

    const show = input.type === 'password';

    input.type = show ? 'text' : 'password';
    toggle.textContent = show ? 'Sembunyikan' : 'Tampilkan';
    toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
});

// 3. Salin teks: <button data-copy="teks yang disalin">
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy]');

    if (!button) {
        return;
    }

    const text = button.dataset.copy;
    let copied = false;

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            copied = true;
        }
    } catch (error) {
        copied = false;
    }

    // Cadangan untuk halaman tanpa HTTPS (misalnya diuji lewat alamat IP di ponsel)
    if (!copied) {
        const area = document.createElement('textarea');

        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();

        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }

        document.body.removeChild(area);
    }

    const original = button.dataset.original || button.textContent;

    button.dataset.original = original;
    button.textContent = copied ? 'Tersalin' : 'Gagal menyalin';

    setTimeout(() => {
        button.textContent = original;
    }, 1500);
});