# Booking GOR Bulutangkis

Aplikasi web untuk memesan lapangan bulutangkis per jam. Pelanggan bisa melihat jam yang masih kosong dan memesan sendiri lewat website. Admin mengelola semua jadwal di satu tempat, termasuk pesanan yang masuk lewat WhatsApp.

Dibuat sebagai proyek portofolio dengan Laravel, berdasarkan cara kerja GOR bulutangkis nyata: pemesanan lewat WhatsApp dan bayar langsung di tempat.

## Masalah yang diselesaikan

Di GOR yang dijadikan acuan, pelanggan harus bertanya ke admin lewat WhatsApp untuk tahu jam yang kosong. Jadwal dicatat manual, sehingga mudah salah catat dan bisa terjadi pesanan ganda. Aplikasi ini memindahkan proses itu ke website tanpa menghilangkan kebiasaan lama (booking lewat WhatsApp dan bayar di GOR).

## Fitur

**Pelanggan**
- Melihat daftar lapangan dan jadwal tanpa perlu login
- Daftar dan masuk, lalu memesan lapangan per jam (1 sampai 4 jam)
- Halaman detail booking dengan tombol WhatsApp ke admin
- Halaman Booking Saya, dan pembatalan sendiri paling lambat 3 jam sebelum jam main
- Tampilan nyaman di ponsel, dengan menu bawah

**Admin**
- Dashboard ringkasan
- Kelola lapangan: tambah, ubah, aktifkan atau nonaktifkan
- Kelola booking: filter, lalu konfirmasi, batalkan, tandai lunas, selesai, atau tidak datang
- Booking manual untuk pesanan yang masuk lewat WhatsApp atau pelanggan yang datang langsung
- Laporan pendapatan per periode dengan ekspor Excel dan PDF

**Sistem**
- Cek bentrok jadwal, dengan penguncian baris lapangan di dalam transaksi database, sehingga dua orang yang menekan tombol bersamaan tidak bisa mendapat jam yang sama
- Booking yang belum dikonfirmasi admin dalam 2 jam dibatalkan otomatis oleh penjadwal (`bookings:expire`, berjalan tiap menit)

## Aturan booking

| Aturan | Nilai |
|---|---|
| Durasi | 1 sampai 4 jam, jam bulat (misalnya 19:00 sampai 21:00) |
| Pemesanan di muka | Maksimal 14 hari ke depan |
| Booking aktif per pelanggan | Maksimal 3 |
| Batas konfirmasi admin | 2 jam (tidak melewati jam mulai) |
| Batas pembatalan oleh pelanggan | Lebih dari 3 jam sebelum jam mulai |
| Zona waktu | WITA (`Asia/Makassar`) |

## Teknologi

- Laravel 13 (PHP 8.3 atau lebih baru)
- MySQL
- Bootstrap 5 lewat Laravel UI, dibangun dengan Vite
- `maatwebsite/excel` untuk ekspor Excel dan `barryvdh/laravel-dompdf` untuk ekspor PDF

## Cara menjalankan di komputer sendiri

Yang dibutuhkan: PHP 8.3+, Composer, Node.js dan npm, serta MySQL.

```bash
git clone https://github.com/lailahyt/booking-gor.git
cd booking-gor

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Buat database kosong di MySQL (misalnya `booking_gor`), lalu ubah bagian database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_gor
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan dengan migrasi, data contoh, dan build tampilan:

```bash
php artisan migrate
php artisan db:seed --class=BookingDemoSeeder
npm run build
php artisan serve
```

Buka `http://localhost:8000`.

Agar pembatalan otomatis berjalan, jalankan penjadwal di terminal terpisah:

```bash
php artisan schedule:work
```

### Akun contoh

Data contoh dari `BookingDemoSeeder` berisi 4 lapangan (Rp30.000 per jam, buka 08:00 sampai 23:00) dan dua akun:

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@gor.test` | `password` |
| Pelanggan | `pelanggan@gor.test` | `password` |

Akun ini hanya untuk percobaan di komputer sendiri. Ganti password sebelum aplikasi dipakai sungguhan.

## Struktur singkat

```
app/Services/BookingService.php      Aturan booking, cek bentrok, pembatalan
app/Services/RevenueReportService.php Hitungan laporan pendapatan
app/Console/Commands/                 Perintah bookings:expire
app/Http/Controllers/Admin/           Dashboard, lapangan, booking, laporan
resources/views/                      Tampilan Blade (pelanggan dan admin)
routes/web.php                        Rute aplikasi
```

## Status

Fitur utama sudah selesai dan bisa dipakai dengan sistem bayar di GOR. Pembayaran online (Midtrans sandbox) direncanakan sebagai tahap terpisah.

## Penulis

Laila Hayati — [GitHub](https://github.com/lailahyt)
