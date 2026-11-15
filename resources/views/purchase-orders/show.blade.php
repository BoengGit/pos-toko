<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Detail Purchase Order</h2>
            <a href="{{ route('purchase-orders.index') }}" class="text-sm text-indigo-600">
                &larr; Kembali ke Pembelian
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-3 bg-green-100 text-green-700 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-3 bg-red-100 text-red-700 rounded-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <x-card>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $purchaseOrder->po_number }}</h3>
                        <p class="text-sm text-gray-500">
                            Supplier: {{ $purchaseOrder->supplier->name }} &middot;
                            Tanggal: {{ $purchaseOrder->order_date->format('d M Y') }} &middot;
                            Dibuat oleh: {{ $purchaseOrder->user->name }}
                        </p>
                        @if ($purchaseOrder->notes)
                            <p class="text-sm text-gray-600 mt-1">Catatan: {{ $purchaseOrder->notes }}</p>
                        @endif
                    </div>
                    <div>
                        @if ($purchaseOrder->status === 'received')
                            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">
                                Diterima
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium">
                                Pending
                            </span>
                        @endif
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 text-sm mb-4">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Kode</th>
                            <th class="px-3 py-2 text-left">Produk</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Harga Beli</th>
                            <th class="px-3 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($purchaseOrder->details as $detail)
                            <tr>
                                <td class="px-3 py-2">{{ $detail->product->code }}</td>
                                <td class="px-3 py-2">{{ $detail->product->name }}</td>
                                <td class="px-3 py-2 text-right">{{ $detail->qty }} {{ $detail->product->unit }}</td>
                                <td class="px-3 py-2 text-right">
                                    Rp{{ number_format($detail->cost_price, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    Rp{{ number_format($detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-between items-center border-t pt-4">
                    <div class="space-x-2">
                        @if ($purchaseOrder->status === 'pending')
                            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                                class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md text-sm">
                                Ubah PO
                            </a>
                            <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST"
                                onsubmit="return confirm('Tandai barang diterima dan tambahkan stok produk?')"
                                class="inline">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm">
                                    Terima Barang (+Stok)
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Total Pembelian</p>
                        <p class="text-xl font-semibold">Rp{{ number_format($purchaseOrder->total, 0, ',', '.') }}</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
