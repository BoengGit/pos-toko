<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Detail Supplier</h2>
            <a href="{{ route('suppliers.index') }}" class="text-sm text-indigo-600">&larr; Kembali ke Supplier</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-card>
                <h3 class="text-lg font-semibold">{{ $supplier->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Telepon: {{ $supplier->phone ?? '-' }} &middot; Email: {{ $supplier->email ?? '-' }}
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Alamat: {{ $supplier->address ?? '-' }}
                </p>
            </x-card>

            <x-card>
                <h4 class="font-medium mb-3">Riwayat Purchase Order</h4>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">No. PO</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tanggal</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Admin</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($purchaseOrders as $po)
                            <tr>
                                <td class="px-4 py-2 font-medium">{{ $po->po_number }}</td>
                                <td class="px-4 py-2">{{ $po->order_date->format('d M Y') }}</td>
                                <td class="px-4 py-2">{{ $po->user->name }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if ($po->status === 'received')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">Diterima</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">Rp{{ number_format($po->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-center">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada riwayat pembelian dari supplier ini.
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
