<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\ProductExit;
use App\Models\ProductHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_global_by_product_name(): void
    {
        Product::query()->create([
            'product_code' => 'SR1',
            'name' => 'Amoladora especial',
            'available_quantity' => 5,
            'damaged_quantity' => 0,
            'minimum_stock' => 2,
        ]);
        $r = $this->getJson('/api/reports/search?q=Amoladora&type=product');
        $r->assertOk();
        $this->assertNotEmpty($r->json('products'));
    }

    public function test_can_search_global_by_product_code(): void
    {
        Product::query()->create([
            'product_code' => 'COD-XYZ',
            'name' => null,
            'available_quantity' => 1,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        $r = $this->getJson('/api/reports/search?q=COD-XYZ&type=product');
        $r->assertOk();
        $this->assertNotEmpty($r->json('products'));
    }

    public function test_can_search_global_by_technician(): void
    {
        ProductExit::query()->create([
            'exit_code' => 'SAL-2099-9001',
            'exit_date' => now()->toDateString(),
            'exit_time' => '10:00:00',
            'technician_name' => 'Diego López',
            'license_plate' => null,
            'is_for_workshop' => false,
        ]);
        $r = $this->getJson('/api/reports/search?q=Diego&type=technician');
        $r->assertOk();
        $this->assertNotEmpty($r->json('exits'));
    }

    public function test_can_search_global_by_license_plate(): void
    {
        ProductExit::query()->create([
            'exit_code' => 'SAL-2099-9002',
            'exit_date' => now()->toDateString(),
            'exit_time' => '10:00:00',
            'technician_name' => 'X',
            'license_plate' => 'ABC123',
            'is_for_workshop' => false,
        ]);
        $r = $this->getJson('/api/reports/search?q=ABC123&type=license_plate');
        $r->assertOk();
        $this->assertNotEmpty($r->json('exits'));
    }

    public function test_can_search_global_by_entry_code(): void
    {
        ProductEntry::query()->create([
            'entry_code' => 'ENT-2099-0001',
            'entry_date' => now()->toDateString(),
            'entry_time' => '09:00:00',
        ]);
        $r = $this->getJson('/api/reports/search?q=ENT-2099-0001&type=entry_code');
        $r->assertOk();
        $this->assertNotEmpty($r->json('entries'));
    }

    public function test_can_search_global_by_exit_code(): void
    {
        ProductExit::query()->create([
            'exit_code' => 'SAL-2099-0001',
            'exit_date' => now()->toDateString(),
            'exit_time' => '09:00:00',
            'is_for_workshop' => true,
        ]);
        $r = $this->getJson('/api/reports/search?q=SAL-2099-0001&type=exit_code');
        $r->assertOk();
        $this->assertNotEmpty($r->json('exits'));
    }

    public function test_product_history_shows_all_movements_in_order(): void
    {
        $p = Product::query()->create([
            'product_code' => 'ORD',
            'available_quantity' => 5,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        ProductHistory::query()->create(['product_id' => $p->id, 'action_type' => 'created', 'description' => 'a']);
        ProductHistory::query()->create(['product_id' => $p->id, 'action_type' => 'updated', 'description' => 'b']);
        $rows = ProductHistory::query()->where('product_id', $p->id)->orderByDesc('id')->get();
        $this->assertCount(2, $rows);
    }
}
