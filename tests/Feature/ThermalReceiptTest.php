<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('thermal receipt renders 58mm print layout and transaction details', function () {
    $kasir = User::factory()->create([
        'name' => 'Kasir Test',
        'role' => 'kasir',
    ]);

    $category = Category::create([
        'name' => 'Sembako',
        'description' => 'Kebutuhan pokok',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'code' => 'SMB-001',
        'name' => 'Beras Ramos 5kg',
        'unit' => 'sak',
        'price' => 75000,
        'stock' => 20,
    ]);

    $transaction = Transaction::create([
        'invoice_number' => 'INV-20261115-0001',
        'user_id' => $kasir->id,
        'total' => 150000,
        'pay' => 200000,
        'change' => 50000,
    ]);

    TransactionDetail::create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 75000,
        'subtotal' => 150000,
    ]);

    $this->actingAs($kasir)
        ->get(route('pos.struk', $transaction))
        ->assertOk()
        ->assertSee('size: 58mm auto;', false)
        ->assertSee('thermal-receipt', false)
        ->assertSee('Cetak Thermal (58mm)')
        ->assertSee('INV-20261115-0001')
        ->assertSee('Beras Ramos 5kg')
        ->assertSee('150.000');
});

test('database seeder is idempotent across repeated runs', function () {
    $this->seed(DatabaseSeeder::class);
    $firstCategoryCount = Category::count();

    $this->seed(DatabaseSeeder::class);

    expect(User::where('email', 'admin@barokahmart.test')->count())->toBe(1)
        ->and(User::where('email', 'kasir@barokahmart.test')->count())->toBe(1)
        ->and($firstCategoryCount)->toBeGreaterThan(0)
        ->and(Category::count())->toBe($firstCategoryCount);
});
