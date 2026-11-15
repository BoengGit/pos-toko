<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : now()->subDays(6)->startOfDay();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : now()->endOfDay();

        $query = Transaction::with('user')
            ->whereBetween('created_at', [$start, $end]);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $transactions = $query->latest()->get();

        $totalPenjualan = $transactions->sum('total');
        $jumlahTransaksi = $transactions->count();
        $kasirs = User::orderBy('name')->get();

        return view('reports.sales', compact('transactions', 'totalPenjualan', 'jumlahTransaksi', 'start', 'end', 'kasirs'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : now()->subDays(6)->startOfDay();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : now()->endOfDay();

        $filename = 'laporan-penjualan-'.$start->format('Ymd').'-'.$end->format('Ymd').'.xlsx';

        return Excel::download(new SalesExport($start, $end, $request->user_id), $filename);
    }
}
