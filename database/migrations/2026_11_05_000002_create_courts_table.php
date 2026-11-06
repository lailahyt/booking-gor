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
            $table->unsignedInteger('price_per_hour'); // rupiah, misal 30000
            $table->time('open_time')->default('08:00:00');
            $table->time('close_time')->default('23:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
