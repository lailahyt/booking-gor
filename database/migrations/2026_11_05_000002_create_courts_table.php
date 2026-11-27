<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('floor_type')->nullable(); // karpet, kayu, semen
            $table->text('description')->nullable();
            $table->string('photo')->nullable();

            // Harga paket (bukan per jam), dalam rupiah. Server menghitung harga dari sini.
            $table->unsignedInteger('price_3_hours'); // misal 110000
            $table->unsignedInteger('price_4_hours'); // misal 140000

            $table->time('open_time')->default('07:00:00');
            $table->time('close_time')->default('23:30:00');

            // Langkah jam mulai dalam menit: 30 (07:00, 07:30, ...) atau 60 (07:00, 08:00, ...)
            $table->unsignedSmallInteger('slot_step_minutes')->default(30);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
