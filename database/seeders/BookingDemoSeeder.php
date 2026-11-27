<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BookingDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Akun demo (ganti password sebelum dipakai sungguhan)
        User::updateOrCreate(
            ['email' => 'admin@gor.test'],
            [
                'name' => 'Admin GOR',
                'phone' => '081200000000',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'pelanggan@gor.test'],
            [
                'name' => 'Pelanggan Demo',
                'phone' => '081211111111',
                'role' => 'customer',
                'password' => Hash::make('password'),
            ]
        );

        // 3 lapangan, buka 07:00 sampai 23:30
        // Paket: 3 jam = Rp110.000, 4 jam = Rp140.000
        // Langkah jam mulai 30 menit (nilai final mengikuti jawaban admin GOR)
        foreach (range(1, 3) as $i) {
            Court::updateOrCreate(
                ['name' => "Lapangan $i"],
                [
                    'floor_type' => 'karpet',
                    'price_3_hours' => 110000,
                    'price_4_hours' => 140000,
                    'open_time' => '07:00:00',
                    'close_time' => '23:30:00',
                    'slot_step_minutes' => 30,
                    'is_active' => true,
                ]
            );
        }
    }
}
