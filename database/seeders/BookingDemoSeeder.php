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

        // 4 lapangan, harga Rp30.000 per jam
        foreach (range(1, 4) as $i) {
            Court::updateOrCreate(
                ['name' => "Lapangan $i"],
                [
                    'floor_type' => 'karpet',
                    'price_per_hour' => 30000,
                    'open_time' => '08:00:00',
                    'close_time' => '23:00:00',
                    'is_active' => true,
                ]
            );
        }
    }
}
