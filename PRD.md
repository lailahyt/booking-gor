# PRD: Aplikasi Booking Lapangan Bulutangkis

| | |
|---|---|
| **Nama proyek** | Booking GOR Bulutangkis |
| **Penulis** | Laila Hayati |
| **Versi** | 1.0 |
| **Tanggal** | 6 November 2026 |
| **Status** | Draf, siap dikerjakan |
| **Teknologi** | Laravel 12, MySQL, Bootstrap 5 (Laravel UI), Midtrans Sandbox (tahap 2) |

Angka seperti batas durasi, batas waktu konfirmasi, dan batas pembatalan di dokumen ini adalah usulan awal. Angka-angka itu bisa diubah sesuai kebiasaan GOR yang dijadikan acuan.

---

## 1. Problem Statement

Di GOR bulutangkis yang dijadikan acuan, pelanggan memesan lapangan lewat WhatsApp (nomor admin diambil dari Google Maps), lalu membayar langsung di GOR. Cara ini mudah dipakai, tapi punya beberapa kelemahan:

- Pelanggan tidak bisa melihat jam yang masih kosong. Mereka harus bertanya dulu ke admin, lalu menunggu balasan.
- Jadwal dicatat manual oleh admin, sehingga rawan salah catat dan rawan pesanan ganda untuk jam yang sama.
- Admin harus membalas pesan yang sama berulang kali (jam kosong, harga, konfirmasi).
- Tidak ada rekap otomatis untuk jumlah booking dan pendapatan.
- Tidak ada catatan pelanggan yang memesan tapi tidak datang.

Aplikasi ini dibuat untuk memindahkan proses tersebut ke website, tanpa menghilangkan kebiasaan yang sudah ada (booking lewat WhatsApp dan bayar di tempat).

## 2. Goals (Tujuan)

1. Pelanggan dapat melihat jadwal kosong dan memesan lapangan sendiri lewat website, kapan saja.
2. Tidak terjadi pesanan ganda untuk lapangan dan jam yang sama, termasuk saat dua orang memesan di waktu bersamaan.
3. Admin mengelola seluruh jadwal di satu tempat, termasuk pesanan yang masuk lewat WhatsApp.
4. Admin dapat melihat rekap booking dan pendapatan per periode.
5. Pelanggan dapat memilih bayar di GOR atau bayar online (tahap 2).
6. Sebagai proyek portofolio, aplikasi ini menunjukkan kemampuan membuat aplikasi Laravel dengan logika bisnis nyata, autentikasi dan pembatasan akses, integrasi pembayaran, test otomatis, dan deploy.

## 3. Target Users

| Pengguna | Deskripsi | Kebutuhan utama |
|---|---|---|
| **Pelanggan** | Pemain individu atau kelompok yang ingin menyewa lapangan per jam. | Tahu jam kosong, memesan dengan cepat, bisa membatalkan, melihat riwayat. |
| **Admin GOR** | Pengelola atau penjaga GOR yang mengatur jadwal dan menerima pembayaran di kasir. | Satu jadwal yang akurat, mudah memasukkan pesanan dari WhatsApp, rekap pendapatan. |

## 4. User Stories

### Pelanggan

| ID | User story |
|---|---|
| US-01 | Sebagai pelanggan, saya ingin melihat daftar lapangan dan harganya tanpa harus login, agar saya bisa menilai dulu sebelum memesan. |
| US-02 | Sebagai pelanggan, saya ingin melihat jam kosong tiap lapangan pada tanggal tertentu, agar saya tidak perlu bertanya ke admin. |
| US-03 | Sebagai pelanggan baru, saya ingin mendaftar dengan isian singkat, agar saya bisa segera memesan. |
| US-04 | Sebagai pelanggan, saya ingin memesan satu jam atau lebih secara berurutan, agar bisa bermain sesuai durasi yang saya mau. |
| US-05 | Sebagai pelanggan, saya ingin total harga langsung terlihat sebelum konfirmasi, agar tidak ada kejutan di kasir. |
| US-06 | Sebagai pelanggan, saya ingin tetap kembali ke pilihan jam saya setelah login atau daftar, agar tidak mengulang dari awal. |
| US-07 | Sebagai pelanggan, saya ingin memilih bayar di GOR atau bayar online, agar sesuai kenyamanan saya. |
| US-08 | Sebagai pelanggan, saya ingin mengirim detail booking ke WhatsApp admin dengan satu klik, agar bisa konfirmasi lewat jalur yang biasa saya pakai. |
| US-09 | Sebagai pelanggan, saya ingin melihat riwayat dan status booking saya, agar tahu mana yang sudah dikonfirmasi. |
| US-10 | Sebagai pelanggan, saya ingin membatalkan booking sendiri selama masih dalam batas waktu, agar slot bisa dipakai orang lain. |

### Admin

| ID | User story |
|---|---|
| US-11 | Sebagai admin, saya ingin menambah, mengubah, dan menonaktifkan lapangan, termasuk harga dan jam buka, agar data selalu sesuai kondisi GOR. |
| US-12 | Sebagai admin, saya ingin melihat semua booking dalam daftar dan kalender, agar tahu jadwal hari ini dan hari berikutnya. |
| US-13 | Sebagai admin, saya ingin mengonfirmasi atau menolak booking yang masuk, agar jadwal hanya berisi pesanan yang valid. |
| US-14 | Sebagai admin, saya ingin memasukkan booking manual untuk pelanggan dari WhatsApp atau yang datang langsung, agar semua jadwal ada di satu tempat. |
| US-15 | Sebagai admin, saya ingin menandai booking sebagai lunas, selesai, atau tidak datang, agar catatan pembayaran dan kehadiran akurat. |
| US-16 | Sebagai admin, saya ingin melihat laporan pendapatan per periode dan mengekspornya, agar mudah direkap. |

## 5. Functional Requirements

Prioritas: **Wajib** = harus ada di versi 1.0. **Tahap 2** = dikerjakan setelah semua fitur wajib selesai dan stabil. **Tambahan** = dikerjakan jika waktu cukup.

### 5.1 Akun dan akses

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-01 | Pendaftaran akun dengan nama, email, nomor WhatsApp, dan password. | Wajib |
| FR-02 | Login dan logout. | Wajib |
| FR-03 | Dua peran: pelanggan dan admin. Halaman admin hanya bisa dibuka oleh akun admin. | Wajib |
| FR-04 | Setelah login atau daftar, pelanggan dikembalikan ke halaman pemesanan dengan pilihan jamnya masih terisi. | Wajib |

### 5.2 Lapangan dan jadwal

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-05 | Daftar lapangan dapat dilihat tanpa login (nama, jenis lantai, harga per jam). | Wajib |
| FR-06 | Admin dapat menambah, mengubah, dan menonaktifkan lapangan (nama, jenis lantai, deskripsi, foto, harga per jam, jam buka dan tutup). | Wajib |
| FR-07 | Tampilan jadwal per lapangan dan tanggal. Jam yang sudah terisi ditandai dan tidak bisa dipilih. | Wajib |
| FR-08 | Jadwal dapat dilihat tanpa login. | Wajib |

### 5.3 Booking

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-09 | Pelanggan memilih lapangan, tanggal, jam mulai, dan jam selesai. Booking dihitung per jam, minimal 1 jam dan maksimal 4 jam berurutan. | Wajib |
| FR-10 | Harga tetap per jam. Total = jumlah jam × harga per jam lapangan. Total disimpan di data booking agar laporan lama tidak berubah bila harga naik. | Wajib |
| FR-11 | Validasi: jam bulat, jam selesai setelah jam mulai, berada dalam jam buka, tanggal tidak boleh sudah lewat, pemesanan maksimal 14 hari ke depan, lapangan harus aktif. | Wajib |
| FR-12 | Cek bentrok di server saat tombol pesan ditekan, dalam satu transaksi database dengan penguncian. Jika dua orang memesan jam yang sama bersamaan, hanya satu yang berhasil dan yang lain mendapat pesan agar memilih jam lain. | Wajib |
| FR-13 | Aturan bentrok: booking baru bentrok jika jam mulai lama lebih kecil dari jam selesai baru **dan** jam selesai lama lebih besar dari jam mulai baru. Jam selesai yang sama dengan jam mulai booking lain tidak bentrok. | Wajib |
| FR-14 | Slot dianggap terisi oleh booking berstatus menunggu (yang belum kedaluwarsa), dikonfirmasi, atau selesai. Booking dibatalkan atau tidak datang tidak menghalangi slot. | Wajib |
| FR-15 | Setiap booking punya kode unik. | Wajib |
| FR-16 | Batasi jumlah booking aktif per pelanggan (usulan: 3). | Wajib |
| FR-17 | Setelah booking dibuat, tersedia tombol kirim ke WhatsApp admin dengan pesan yang sudah terisi (kode, lapangan, tanggal, jam). | Wajib |

### 5.4 Status, konfirmasi, dan pembatalan

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-18 | Status booking: menunggu, dikonfirmasi, dibatalkan, tidak datang, selesai. | Wajib |
| FR-19 | Booking baru berstatus menunggu dan langsung mengunci slot. | Wajib |
| FR-20 | Admin mengonfirmasi booking bayar-di-GOR. Jika admin tidak mengonfirmasi dalam batas waktu (usulan: 2 jam, atau sampai jam mulai bila lebih dekat), booking batal otomatis dan slot terbuka. | Wajib |
| FR-21 | Pembatalan otomatis dijalankan penjadwal tiap menit. | Wajib |
| FR-22 | Pelanggan dapat membatalkan sendiri jika status masih menunggu atau dikonfirmasi dan waktu sekarang masih lebih dari batas tertentu sebelum jam mulai (usulan: 3 jam). Di luar batas itu, hanya admin yang dapat membatalkan. | Wajib |
| FR-23 | Pelanggan dapat melihat riwayat dan status booking miliknya sendiri, dan tidak bisa melihat booking orang lain. | Wajib |

### 5.5 Fitur admin

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-24 | Dashboard ringkas: booking hari ini, booking menunggu konfirmasi, pendapatan bulan ini. | Wajib |
| FR-25 | Daftar booking dengan filter (tanggal, lapangan, status). | Wajib |
| FR-26 | Admin dapat mengonfirmasi, membatalkan, menandai lunas, selesai, atau tidak datang. | Wajib |
| FR-27 | Booking manual oleh admin untuk pelanggan tanpa akun (nama dan nomor WhatsApp diisi admin). Aturan bentrok yang sama berlaku. | Wajib |
| FR-28 | Laporan pendapatan per periode, dengan ekspor Excel atau PDF. | Wajib |
| FR-29 | Pelanggan dengan jumlah "tidak datang" tinggi dapat dilihat admin. | Tambahan |

### 5.6 Pembayaran online (tahap 2)

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-30 | Saat memesan, pelanggan memilih bayar di GOR atau bayar online. | Tahap 2 |
| FR-31 | Bayar online memakai Midtrans (mode sandbox). Booking berstatus menunggu pembayaran dengan batas waktu lebih pendek (usulan: 15 menit). | Tahap 2 |
| FR-32 | Status berubah menjadi dikonfirmasi dan lunas otomatis dari notifikasi (webhook) Midtrans, bukan dari klik pelanggan. | Tahap 2 |
| FR-33 | Webhook diverifikasi tanda tangannya, dan notifikasi yang terkirim dua kali tidak membuat data ganda. | Tahap 2 |
| FR-34 | Booking online yang tidak dibayar sampai batas waktu batal otomatis. | Tahap 2 |
| FR-35 | Pengembalian dana untuk pembayaran online diproses manual oleh admin dan statusnya dicatat di sistem. | Tahap 2 |

### 5.7 Fitur tambahan (jika waktu cukup)

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-36 | Jadwal berubah langsung tanpa memuat ulang halaman (real-time). | Tambahan |
| FR-37 | Notifikasi booking lewat email. | Tambahan |

## 6. Non-functional Requirements

| Kategori | Kebutuhan |
|---|---|
| **Keamanan** | Password disimpan dalam bentuk hash. Perlindungan CSRF pada semua form. Semua input divalidasi di server. Pelanggan hanya dapat mengakses data booking miliknya, dan halaman admin dilindungi middleware peran. Kunci rahasia (database, Midtrans) hanya di file `.env` dan tidak masuk Git. |
| **Keandalan data** | Tidak boleh ada pesanan ganda pada lapangan dan jam yang sama, termasuk saat permintaan masuk bersamaan. Pembuatan booking dilakukan dalam transaksi database. |
| **Kinerja** | Halaman jadwal terbuka dalam waktu wajar (target di bawah 3 detik pada koneksi biasa). Tabel booking diberi index pada lapangan dan tanggal. |
| **Kapasitas** | Skala kecil: sekitar 4 lapangan dan puluhan booking per hari. Cukup dijalankan di satu server hosting biasa. |
| **Kemudahan pakai** | Tampilan responsif karena sebagian besar pelanggan memakai ponsel. Form daftar singkat. Pesan error berbahasa Indonesia dan jelas. |
| **Zona waktu** | Seluruh waktu memakai WITA (`Asia/Makassar`). Batas waktu konfirmasi, bayar, dan pembatalan dihitung dari waktu ini. |
| **Kualitas kode** | Ada test otomatis minimal untuk: cek bentrok jadwal, hitung harga, pembatalan otomatis, dan (tahap 2) webhook pembayaran. Riwayat Git rapi dengan pesan commit yang bermakna. |
| **Dokumentasi dan deploy** | README berisi tujuan aplikasi, daftar fitur, screenshot, cara instalasi, dan akun demo. Aplikasi di-deploy dengan tautan demo yang dapat dicoba. |

## 7. Scope (Ruang Lingkup)

### Masuk dalam versi 1.0

- Pendaftaran, login, dan dua peran (pelanggan dan admin).
- Kelola lapangan dan harga per jam oleh admin.
- Lihat jadwal, booking per jam (1 sampai 4 jam berurutan) dengan cek bentrok.
- Konfirmasi admin dengan batas waktu, pembatalan otomatis, dan pembatalan oleh pelanggan.
- Tombol kirim ke WhatsApp dan booking manual oleh admin.
- Dashboard admin dan laporan pendapatan dengan ekspor.
- Pembayaran di GOR (dicatat manual oleh admin).

### Tahap 2 (setelah versi 1.0 stabil)

- Pembayaran online lewat Midtrans sandbox, termasuk webhook dan pembatalan otomatis booking yang tidak dibayar.

### Di luar scope

- Aplikasi mobile bawaan (Android atau iOS). Website responsif sudah cukup.
- Pembayaran dengan uang sungguhan. Midtrans hanya mode sandbox untuk demo.
- Pengembalian dana otomatis.
- Tarif berbeda untuk jam siang, malam, atau akhir pekan (harga tetap per jam).
- Booking berulang mingguan, paket member atau langganan, promo dan diskon.
- Penyewaan perlengkapan (raket, shuttlecock) dan penjualan makanan atau minuman.
- Beberapa cabang GOR dalam satu sistem.
- Verifikasi email dan verifikasi nomor WhatsApp dengan kode OTP.
- Pengiriman pesan WhatsApp otomatis dari sistem.

### Asumsi

- Ada satu GOR dengan beberapa lapangan, dan jam buka sama setiap hari.
- Admin tetap menerima pembayaran di kasir untuk booking bayar-di-GOR.
- Pelanggan memiliki ponsel atau komputer dengan akses internet.

### Alur status booking

```
Bayar di GOR:
  Menunggu → (admin konfirmasi) → Dikonfirmasi → Selesai
                                               → Tidak Datang
  Menunggu → (lewat batas waktu / dibatalkan) → Dibatalkan

Bayar online (tahap 2):
  Menunggu pembayaran → (webhook sukses) → Dikonfirmasi + Lunas → Selesai
  Menunggu pembayaran → (lewat batas waktu) → Dibatalkan
```
