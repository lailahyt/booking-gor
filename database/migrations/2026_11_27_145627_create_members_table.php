<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20); // nomor WhatsApp
            $table->foreignId('court_id')->constrained()->restrictOnDelete();

            // Jadwal tetap: hari main diambil dari starts_on (tanggal sesi pertama),
            // sesi berikutnya tiap 7 hari.
            $table->date('starts_on');
            $table->time('start_time');
            $table->unsignedTinyInteger('duration_hours'); // 3 atau 4
            $table->unsignedTinyInteger('sessions')->default(4);

            // Biaya satu periode; dibagi rata ke total_price tiap sesi
            $table->unsignedInteger('monthly_fee');
            // unpaid | paid (berlaku untuk seluruh periode)
            $table->string('payment_status', 20)->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['court_id', 'starts_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
