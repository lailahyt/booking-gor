<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Support\Minutes;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CourtController extends Controller
{
    public function index()
    {
        $courts = Court::orderBy('name')->get();

        return view('admin.courts.index', compact('courts'));
    }

    public function create()
    {
        return view('admin.courts.create');
    }

    public function store(Request $request)
    {
        Court::create($this->validated($request));

        return redirect()->route('admin.courts.index')
            ->with('success', 'Lapangan berhasil ditambahkan.');
    }

    public function edit(Court $court)
    {
        return view('admin.courts.edit', compact('court'));
    }

    public function update(Request $request, Court $court)
    {
        $court->update($this->validated($request));

        return redirect()->route('admin.courts.index')
            ->with('success', 'Lapangan berhasil diperbarui.');
    }

    public function toggle(Court $court)
    {
        $court->update(['is_active' => ! $court->is_active]);

        return back()->with(
            'success',
            $court->is_active ? 'Lapangan diaktifkan.' : 'Lapangan dinonaktifkan.'
        );
    }

    private function validated(Request $request): array
    {
        $timePattern = '/^([01]\d|2[0-3]):(00|30)$/';

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'floor_type' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_3_hours' => ['required', 'integer', 'min:1000', 'max:5000000'],
            'price_4_hours' => ['required', 'integer', 'min:1000', 'max:5000000'],
            'slot_step_minutes' => ['required', 'integer', 'in:' . implode(',', Court::STEP_OPTIONS)],
            'open_time' => ['required', 'regex:' . $timePattern],
            'close_time' => ['required', 'regex:' . $timePattern, 'after:open_time'],
        ], [
            'name.required' => 'Nama lapangan wajib diisi.',
            'price_3_hours.required' => 'Harga paket 3 jam wajib diisi.',
            'price_3_hours.integer' => 'Harga harus berupa angka bulat, misalnya 110000.',
            'price_3_hours.min' => 'Harga minimal Rp1.000.',
            'price_4_hours.required' => 'Harga paket 4 jam wajib diisi.',
            'price_4_hours.integer' => 'Harga harus berupa angka bulat, misalnya 140000.',
            'price_4_hours.min' => 'Harga minimal Rp1.000.',
            'slot_step_minutes.required' => 'Pilih langkah jam mulai.',
            'slot_step_minutes.in' => 'Langkah jam mulai harus 30 atau 60 menit.',
            'open_time.regex' => 'Jam buka harus pas atau setengah jam, misalnya 07:00 atau 07:30.',
            'close_time.regex' => 'Jam tutup harus pas atau setengah jam, misalnya 23:00 atau 23:30.',
            'close_time.after' => 'Jam tutup harus lebih akhir dari jam buka.',
        ]);

        $open = Minutes::parse($data['open_time']);
        $close = Minutes::parse($data['close_time']);
        $step = (int) $data['slot_step_minutes'];
        $errors = [];

        // jam mulai dihitung dari jam buka, jadi jam buka harus jatuh di kelipatan langkah
        if ($open % $step !== 0) {
            $errors['open_time'] = $step === 60
                ? 'Dengan langkah 60 menit, jam buka harus jam bulat, misalnya 07:00.'
                : 'Jam buka harus pas atau setengah jam, misalnya 07:00 atau 07:30.';
        }

        // paket terpendek 3 jam harus muat dalam jam operasional
        if ($close - $open < 180) {
            $errors['close_time'] = 'Jam operasional minimal 3 jam agar paket 3 jam bisa dipesan.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
