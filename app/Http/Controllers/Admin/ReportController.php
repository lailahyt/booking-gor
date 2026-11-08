<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RevenueReportExport;
use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Services\RevenueReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public const MAX_DAYS = 366;
    public const PER_PAGE = 25;

    public function __construct(private RevenueReportService $reports) {}

    public function index(Request $request)
    {
        [$from, $to, $courtId] = $this->filters($request);

        $report = $this->reports->build($from->toDateString(), $to->toDateString(), $courtId);
        $query = $this->query($from, $to, $courtId);

        $page = LengthAwarePaginator::resolveCurrentPage();

        $detail = new LengthAwarePaginator(
            $report['bookings']->forPage($page, self::PER_PAGE)->values(),
            $report['bookings']->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $query]
        );

        return view('admin.reports.index', [
            'report' => $report,
            'detail' => $detail,
            'from' => $from,
            'to' => $to,
            'courtId' => $courtId,
            'courts' => Court::orderBy('name')->get(),
            'query' => $query,
        ]);
    }

    public function excel(Request $request)
    {
        [$from, $to, $courtId] = $this->filters($request);

        $report = $this->reports->build($from->toDateString(), $to->toDateString(), $courtId);

        return Excel::download(new RevenueReportExport($report), $this->filename($from, $to, 'xlsx'));
    }

    public function pdf(Request $request)
    {
        [$from, $to, $courtId] = $this->filters($request);

        $report = $this->reports->build($from->toDateString(), $to->toDateString(), $courtId);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'courtName' => $courtId ? Court::find($courtId)?->name : null,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->filename($from, $to, 'pdf'));
    }

    /**
     * Membaca dan memeriksa filter. Bawaan: bulan ini.
     * Mengembalikan [tanggal awal, tanggal akhir, id lapangan atau null].
     */
    private function filters(Request $request): array
    {
        $request->merge([
            'from' => $request->query('from') ?: today()->startOfMonth()->toDateString(),
            'to' => $request->query('to') ?: today()->endOfMonth()->toDateString(),
        ]);

        $data = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'court_id' => ['nullable', 'exists:courts,id'],
        ], [
            'from.date_format' => 'Tanggal awal tidak valid.',
            'to.date_format' => 'Tanggal akhir tidak valid.',
            'to.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
            'court_id.exists' => 'Lapangan tidak ditemukan.',
        ]);

        $from = Carbon::createFromFormat('Y-m-d', $data['from'])->startOfDay();
        $to = Carbon::createFromFormat('Y-m-d', $data['to'])->startOfDay();

        if ($from->diffInDays($to) > self::MAX_DAYS) {
            throw ValidationException::withMessages([
                'to' => 'Rentang laporan maksimal ' . self::MAX_DAYS . ' hari.',
            ]);
        }

        $courtId = ! empty($data['court_id']) ? (int) $data['court_id'] : null;

        return [$from, $to, $courtId];
    }

    private function query(Carbon $from, Carbon $to, ?int $courtId): array
    {
        return array_filter([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'court_id' => $courtId,
        ]);
    }

    private function filename(Carbon $from, Carbon $to, string $extension): string
    {
        return 'laporan-pendapatan-' . $from->toDateString() . '-sd-' . $to->toDateString() . '.' . $extension;
    }
}
