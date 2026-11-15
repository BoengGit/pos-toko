<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['supplier', 'user'])->withCount('details');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->latest()->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get(['id', 'code', 'name', 'price', 'stock', 'unit']);

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $purchaseOrder = DB::transaction(function () use ($validated) {
            [$details, $total] = $this->prepareDetails($validated['items']);

            $po = PurchaseOrder::create([
                'po_number' => $this->generatePoNumber(),
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'order_date' => $validated['order_date'],
                'status' => $validated['status'],
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            $po->details()->createMany($details);

            if ($po->status === 'received') {
                $this->incrementProductStock($details);
            }

            return $po;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order berhasil disimpan.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'user', 'details.product']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if ($purchaseOrder->status === 'received') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order yang sudah diterima tidak dapat diubah.');
        }

        $purchaseOrder->load('details.product');
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get(['id', 'code', 'name', 'price', 'stock', 'unit']);

        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'products'));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === 'received') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order yang sudah diterima tidak dapat diubah.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $purchaseOrder) {
            [$details, $total] = $this->prepareDetails($validated['items']);

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'status' => $validated['status'],
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            $purchaseOrder->details()->delete();
            $purchaseOrder->details()->createMany($details);

            if ($validated['status'] === 'received') {
                $this->incrementProductStock($details);
            }
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order berhasil diperbarui.');
    }

    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === 'received') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order ini sudah berstatus diterima.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            $lockedPo = PurchaseOrder::lockForUpdate()->with('details')->findOrFail($purchaseOrder->id);

            if ($lockedPo->status === 'received') {
                return;
            }

            $items = $lockedPo->details->map(fn ($detail) => [
                'product_id' => $detail->product_id,
                'qty' => $detail->qty,
            ])->all();

            $this->incrementProductStock($items);
            $lockedPo->update(['status' => 'received']);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Barang berhasil diterima dan stok produk telah ditambahkan.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === 'received') {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'Purchase Order yang sudah diterima tidak dapat dihapus.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil dihapus.');
    }

    /**
     * Prepare detail rows and compute total cost.
     *
     * @param  array<int, array{product_id: int|string, qty: int|string, cost_price: int|string}>  $items
     * @return array{0: array<int, array<string, int>>, 1: int}
     */
    private function prepareDetails(array $items): array
    {
        $details = [];
        $total = 0;

        foreach ($items as $item) {
            $qty = (int) $item['qty'];
            $costPrice = (int) $item['cost_price'];
            $subtotal = $qty * $costPrice;
            $total += $subtotal;

            $details[] = [
                'product_id' => (int) $item['product_id'],
                'qty' => $qty,
                'cost_price' => $costPrice,
                'subtotal' => $subtotal,
            ];
        }

        return [$details, $total];
    }

    /**
     * Lock product rows and increment stock atomically.
     *
     * @param  array<int, array{product_id: int, qty: int}>  $items
     */
    private function incrementProductStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::lockForUpdate()->findOrFail($item['product_id']);
            $product->increment('stock', (int) $item['qty']);
        }
    }

    private function generatePoNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';
        $latestPo = PurchaseOrder::where('po_number', 'like', $prefix.'%')
            ->orderByDesc('po_number')
            ->first();

        $nextNumber = $latestPo ? ((int) substr($latestPo->po_number, -4) + 1) : 1;

        return $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
