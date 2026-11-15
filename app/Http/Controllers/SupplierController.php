<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::withCount('purchaseOrders');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $suppliers = $query->latest()->paginate(10)->withQueryString();

        return view('master-data.supplier.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('master-data.supplier.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier): View
    {
        $purchaseOrders = $supplier->purchaseOrders()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('master-data.supplier.show', compact('supplier', 'purchaseOrders'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('master-data.supplier.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchaseOrders()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki riwayat pembelian.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
