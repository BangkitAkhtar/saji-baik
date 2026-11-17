<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Outlet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function period(Request $request): array
    {
        $data = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $from = Carbon::parse($data['from'] ?? today()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($data['to'] ?? today()->toDateString())->endOfDay();
        if ($to->lt($from) || $from->diffInDays($to) > 366) {
            throw ValidationException::withMessages(['to' => 'Pilih rentang tanggal berurutan, maksimal satu tahun.']);
        }

        return [$from, $to];
    }

    public function index(Request $request): View
    {
        [$from,$to] = $this->period($request);
        $query = Order::where('status', 'completed')->whereBetween('completed_at', [$from, $to]);
        $metrics = [
            'portions' => (clone $query)->sum('quantity'),
            'orders' => (clone $query)->count(),
            'accounts' => (clone $query)->distinct()->count('user_id'),
            'revenue' => (clone $query)->sum('total_price'),
        ];
        $daily = (clone $query)->selectRaw('DATE(completed_at) as day, SUM(quantity) as portions, COUNT(*) as orders, SUM(total_price) as revenue')->groupByRaw('DATE(completed_at)')->orderBy('day')->get();
        $byOutlet = Outlet::withSum(['completedOrders as served_portions' => fn ($q) => $q->whereBetween('completed_at', [$from, $to])], 'quantity')->orderBy('name')->get();
        $missed = Order::where('status', 'missed')->whereBetween('missed_at', [$from, $to])->sum('quantity');

        return view('admin.reports', compact('metrics', 'daily', 'byOutlet', 'missed', 'from', 'to'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from,$to] = $this->period($request);

        return response()->streamDownload(function () use ($from, $to): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal selesai', 'Pesanan selesai', 'Porsi diambil', 'Penerimaan (Rp)'], ',', '"', '');
            Order::where('status', 'completed')->whereBetween('completed_at', [$from, $to])->selectRaw('DATE(completed_at) as day, COUNT(*) as orders, SUM(quantity) as portions, SUM(total_price) as revenue')->groupByRaw('DATE(completed_at)')->orderBy('day')->get()->each(function ($row) use ($output): void {
                fputcsv($output, [$row->day, $row->orders, $row->portions, $row->revenue], ',', '"', '');
            });
            fclose($output);
        }, 'laporan-sdg2-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
