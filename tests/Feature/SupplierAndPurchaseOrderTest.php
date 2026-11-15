<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeUserWithRole(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

function makeProductWithStock(int $stock = 10): Product
{
    $category = Category::create([
        'name' => 'Minuman Test',
        'description' => 'Kategori pengujian',
    ]);

    return Product::create([
        'category_id' => $category->id,
        'code' => 'PRD-'.uniqid(),
        'name' => 'Teh Botol Test',
        'unit' => 'btl',
        'price' => 5000,
        'stock' => $stock,
    ]);
}

test('guests and kasir cannot access supplier and purchase order management', function () {
    $this->get(route('suppliers.index'))->assertRedirect(route('login'));
    $this->get(route('purchase-orders.index'))->assertRedirect(route('login'));

    $kasir = makeUserWithRole('kasir');

    $this->actingAs($kasir)->get(route('suppliers.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('purchase-orders.index'))->assertForbidden();
});

test('admin can create search update and delete a supplier without purchase orders', function () {
    $admin = makeUserWithRole('admin');

    $this->actingAs($admin)
        ->post(route('suppliers.store'), [
            'name' => 'PT Sumber Makmur',
            'phone' => '08123456789',
            'email' => 'sales@sumbermakmur.test',
            'address' => 'Jl. Merdeka No. 10',
        ])
        ->assertRedirect(route('suppliers.index'));

    $supplier = Supplier::where('name', 'PT Sumber Makmur')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('suppliers.index', ['search' => 'Makmur']))
        ->assertOk()
        ->assertSee('PT Sumber Makmur');

    $this->actingAs($admin)
        ->put(route('suppliers.update', $supplier), [
            'name' => 'PT Sumber Makmur Jaya',
            'phone' => '08129999888',
            'email' => 'sales@sumbermakmur.test',
            'address' => 'Jl. Merdeka No. 12',
        ])
        ->assertRedirect(route('suppliers.index'));

    expect($supplier->fresh()->name)->toBe('PT Sumber Makmur Jaya');

    $this->actingAs($admin)
        ->delete(route('suppliers.destroy', $supplier))
        ->assertRedirect(route('suppliers.index'));

    expect(Supplier::find($supplier->id))->toBeNull();
});

test('admin cannot delete a supplier that has purchase orders', function () {
    $admin = makeUserWithRole('admin');
    $product = makeProductWithStock(5);
    $supplier = Supplier::create([
        'name' => 'CV Distribusi Utama',
        'phone' => '0811111111',
    ]);

    PurchaseOrder::create([
        'po_number' => 'PO-20261115-0001',
        'supplier_id' => $supplier->id,
        'user_id' => $admin->id,
        'order_date' => '2026-11-15',
        'status' => 'pending',
        'total' => 20000,
    ]);

    $this->actingAs($admin)
        ->delete(route('suppliers.destroy', $supplier))
        ->assertRedirect(route('suppliers.index'))
        ->assertSessionHas('error');

    expect(Supplier::find($supplier->id))->not->toBeNull();
});

test('pending purchase order does not add stock until received and locks afterwards', function () {
    $admin = makeUserWithRole('admin');
    $product = makeProductWithStock(10);
    $supplier = Supplier::create(['name' => 'PT Distributor Berkah']);

    $this->actingAs($admin)
        ->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'order_date' => '2026-11-15',
            'status' => 'pending',
            'notes' => 'Order mingguan',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 24,
                    'cost_price' => 3500,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    $po = PurchaseOrder::firstOrFail();

    expect($po->status)->toBe('pending')
        ->and($po->total)->toBe(84000)
        ->and($product->fresh()->stock)->toBe(10);

    $this->actingAs($admin)
        ->put(route('purchase-orders.update', $po), [
            'supplier_id' => $supplier->id,
            'order_date' => '2026-11-15',
            'status' => 'pending',
            'notes' => 'Revisi qty',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 30,
                    'cost_price' => 3500,
                ],
            ],
        ])
        ->assertRedirect(route('purchase-orders.show', $po));

    expect($po->fresh()->total)->toBe(105000)
        ->and($product->fresh()->stock)->toBe(10);

    $this->actingAs($admin)
        ->post(route('purchase-orders.receive', $po))
        ->assertRedirect(route('purchase-orders.show', $po));

    expect($po->fresh()->status)->toBe('received')
        ->and($product->fresh()->stock)->toBe(40);

    // Receiving, editing, or deleting a received PO must not change stock or remove record
    $this->actingAs($admin)
        ->post(route('purchase-orders.receive', $po))
        ->assertSessionHas('error');

    $this->actingAs($admin)
        ->get(route('purchase-orders.edit', $po))
        ->assertRedirect(route('purchase-orders.show', $po));

    $this->actingAs($admin)
        ->delete(route('purchase-orders.destroy', $po))
        ->assertRedirect(route('purchase-orders.index'))
        ->assertSessionHas('error');

    expect($product->fresh()->stock)->toBe(40)
        ->and(PurchaseOrder::find($po->id))->not->toBeNull();
});

test('creating purchase order directly with received status increments product stock immediately', function () {
    $admin = makeUserWithRole('admin');
    $product = makeProductWithStock(5);
    $supplier = Supplier::create(['name' => 'PT Langsung Terima']);

    $this->actingAs($admin)
        ->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'order_date' => '2026-11-15',
            'status' => 'received',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 15,
                    'cost_price' => 4000,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($product->fresh()->stock)->toBe(20);
});
