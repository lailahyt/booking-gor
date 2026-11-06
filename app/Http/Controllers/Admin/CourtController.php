<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;

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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'floor_type' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_per_hour' => ['required', 'integer', 'min:1000', 'max:1000000'],
            'open_time' => ['required', 'regex:/^([01]\d|2[0-3]):00$/'],
            'close_time' => ['required', 'regex:/^([01]\d|2[0-3]):00$/', 'after:open_time'],
        ], [
            'name.required' => 'Nama lapangan wajib diisi.',
            'price_per_hour.required' => 'Harga per jam wajib diisi.',
            'price_per_hour.integer' => 'Harga harus berupa angka bulat, misalnya 30000.',
            'price_per_hour.min' => 'Harga minimal Rp1.000.',
            'open_time.regex' => 'Jam buka harus jam bulat, misalnya 08:00.',
            'close_time.regex' => 'Jam tutup harus jam bulat, misalnya 23:00.',
            'close_time.after' => 'Jam tutup harus lebih akhir dari jam buka.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
