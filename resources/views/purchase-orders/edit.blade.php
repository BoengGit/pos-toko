<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Ubah Purchase Order: {{ $purchaseOrder->po_number }}</h2>
    </x-slot>

    @php
        $initialItems = old('items', $purchaseOrder->details->map(fn ($detail) => [
            'product_id' => $detail->product_id,
            'qty' => $detail->qty,
            'cost_price' => $detail->cost_price,
        ])->values()->all());
    @endphp

    <div class="py-12" x-data="poForm(@js($products), @js($initialItems))">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Supplier</label>
                            <select name="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        @selected(old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id)>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Order</label>
                            <input type="date" name="order_date"
                                value="{{ old('order_date', $purchaseOrder->order_date->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="pending" @selected(old('status', $purchaseOrder->status) === 'pending')>
                                    Pending (Belum Diterima)
                                </option>
                                <option value="received" @selected(old('status', $purchaseOrder->status) === 'received')>
                                    Diterima (Langsung Tambah Stok)
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea name="notes" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium text-gray-800">Daftar Barang Pembelian</h3>
                            <button type="button" @click="addRow()"
                                class="bg-gray-800 text-white px-3 py-1.5 rounded-md text-xs">
                                + Tambah Baris
                            </button>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">Produk</th>
                                    <th class="px-3 py-2 text-center w-28">Qty</th>
                                    <th class="px-3 py-2 text-right w-40">Harga Beli</th>
                                    <th class="px-3 py-2 text-right w-40">Subtotal</th>
                                    <th class="px-3 py-2 text-center w-16">Hapus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <select :name="'items[' + index + '][product_id]'" x-model="item.product_id"
                                                class="w-full rounded-md border-gray-300 text-sm">
                                                <option value="">Pilih produk</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="product.id"
                                                        :selected="Number(item.product_id) === Number(product.id)"
                                                        x-text="product.code + ' - ' + product.name + ' (Stok: ' + product.stock + ')'">
                                                    </option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="1" :name="'items[' + index + '][qty]'"
                                                x-model.number="item.qty"
                                                class="w-full rounded-md border-gray-300 text-center text-sm">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="1" :name="'items[' + index + '][cost_price]'"
                                                x-model.number="item.cost_price"
                                                class="w-full rounded-md border-gray-300 text-right text-sm">
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium"
                                            x-text="formatRupiah((item.qty || 0) * (item.cost_price || 0))">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="removeRow(index)"
                                                class="text-red-600" x-show="items.length > 1">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <div class="mt-4 flex justify-end text-lg font-semibold">
                            <span>Total Pembelian: <span x-text="formatRupiah(total())"></span></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-sm text-gray-500">Batal</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    @push('scripts')
        <script>
            function poForm(products, initialItems) {
                return {
                    products: products,
                    items: initialItems,
                    addRow() {
                        this.items.push({ product_id: '', qty: 1, cost_price: 0 });
                    },
                    removeRow(index) {
                        this.items.splice(index, 1);
                    },
                    total() {
                        return this.items.reduce((sum, item) => sum + ((Number(item.qty) || 0) * (Number(item.cost_price) || 0)), 0);
                    },
                    formatRupiah(value) {
                        return 'Rp' + Number(value || 0).toLocaleString('id-ID');
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
