<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabel ini baru dipakai di tahap bayar online (Midtrans sandbox).
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->unique(); // id pesanan yang dikirim ke Midtrans
            $table->unsignedInteger('gross_amount');
            $table->string('status', 30)->default('pending'); // status dari Midtrans
            $table->string('payment_type', 50)->nullable(); // qris, bank_transfer, dst
            $table->dateTime('paid_at')->nullable();
            $table->json('raw_response')->nullable(); // kabar mentah dari webhook
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
