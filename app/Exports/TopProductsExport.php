<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class TopProductsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        protected $start,
        protected $end,
        protected $userId = null
    ) {}

    public function collection(): Collection
    {
        return TransactionDetail::with(['product' => fn ($q) => $q->withTrashed()])
            ->select('product_id')
            ->selectRaw('SUM(qty) as total_qty, SUM(subtotal) as total_pendapatan')
            ->whereHas('transaction', function ($query) {
                $query->whereBetween('created_at', [$this->start, $this->end]);

                if ($this->userId) {
                    $query->where('user_id', $this->userId);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Kode Produk', 'Nama Produk', 'Total Terjual (Qty)', 'Total Pendapatan'];
    }

    /**
     * @param  TransactionDetail  $item
     * @return array<int, mixed>
     */
    public function map($item): array
    {
        return [
            $item->product?->code ?? '-',
            $item->product?->name ?? 'Produk Terhapus',
            $item->total_qty,
            $item->total_pendapatan,
        ];
    }

    public function title(): string
    {
        return 'Produk Terlaris';
    }
}
