<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // misal BK-20261105-A7K2

            // user_id boleh kosong: booking manual oleh admin (WhatsApp / datang langsung)
            // untuk pelanggan yang belum punya akun
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('customer_name'); // dari akun (web) atau diisi admin (manual)
            $table->string('customer_phone', 20); // nomor WhatsApp
            $table->foreignId('court_id')->constrained()->restrictOnDelete();

            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('total_price'); // disimpan agar laporan lama tidak berubah saat harga naik

            // pending | confirmed | cancelled | no_show | completed
            $table->string('status', 20)->default('pending');
            // on_site | online
            $table->string('payment_method', 20)->default('on_site');
            // unpaid | paid | refunded
            $table->string('payment_status', 20)->default('unpaid');
            // web | admin (admin = input manual dari WhatsApp)
            $table->string('source', 20)->default('web');

            $table->dateTime('expires_at')->nullable(); // batas waktu konfirmasi atau bayar
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // paling sering dipakai: cek bentrok dan tampilan kalender
            $table->index(['court_id', 'booking_date']);
            // dipakai penjadwal untuk membatalkan booking kedaluwarsa
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
