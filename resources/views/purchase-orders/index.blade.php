<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">
                Riwayat Pembelian Stok (Purchase Order)
            </h2>
            <a href="{{ route('purchase-orders.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm">
                + Buat Purchase Order
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex flex-wrap gap-3 mb-4">
                    <select name="supplier_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="received" @selected(request('status') === 'received')>Diterima</option>
                    </select>

                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">Filter</button>
                    <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 text-gray-500">Reset</a>
                </form>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">No. PO</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tanggal</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Supplier</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Item</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($purchaseOrders as $po)
                            <tr>
                                <td class="px-4 py-2 text-sm font-medium">{{ $po->po_number }}</td>
                                <td class="px-4 py-2 text-sm">{{ $po->order_date->format('d M Y') }}</td>
                                <td class="px-4 py-2 text-sm">{{ $po->supplier->name }}</td>
                                <td class="px-4 py-2 text-sm text-center">{{ $po->details_count }} item</td>
                                <td class="px-4 py-2 text-sm text-center">
                                    @if ($po->status === 'received')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">Diterima</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-right">
                                    Rp{{ number_format($po->total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-center space-x-2">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="text-gray-600">Detail</a>
                                    @if ($po->status === 'pending')
                                        <a href="{{ route('purchase-orders.edit', $po) }}" class="text-indigo-600">Ubah</a>
                                        <form action="{{ route('purchase-orders.receive', $po) }}" method="POST"
                                            onsubmit="return confirm('Tandai barang diterima dan tambahkan ke stok produk?')"
                                            class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600">Terima</button>
                                        </form>
                                        <form action="{{ route('purchase-orders.destroy', $po) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus PO {{ $po->po_number }}?')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada data Purchase Order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $purchaseOrders->links() }}
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
