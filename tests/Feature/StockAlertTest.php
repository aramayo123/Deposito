<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_alert_created_when_stock_reaches_minimum(): void
    {
        $p = Product::query()->create([
            'product_code' => 'A1',
            'available_quantity' => 10,
            'damaged_quantity' => 0,
            'minimum_stock' => 5,
        ]);
        $this->put(route('products.update', $p), [
            'product_code' => 'A1',
            'available_quantity' => 5,
            'damaged_quantity' => 0,
            'minimum_stock' => 5,
        ]);
        $this->assertTrue(
            StockAlert::query()->where('product_id', $p->id)->where('alert_type', 'low_stock')->exists()
        );
    }

    public function test_alert_created_when_stock_is_zero(): void
    {
        $p = Product::query()->create([
            'product_code' => 'A2',
            'available_quantity' => 2,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        $this->put(route('products.update', $p), [
            'product_code' => 'A2',
            'available_quantity' => 0,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        $this->assertTrue(
            StockAlert::query()->where('product_id', $p->id)->where('alert_type', 'out_of_stock')->exists()
        );
    }

    public function test_can_mark_alert_as_read(): void
    {
        $p = Product::query()->create([
            'product_code' => 'A3',
            'available_quantity' => 1,
            'damaged_quantity' => 0,
            'minimum_stock' => 5,
        ]);
        $a = StockAlert::query()->create([
            'product_id' => $p->id,
            'alert_type' => 'low_stock',
            'current_quantity' => 1,
            'minimum_stock' => 5,
            'is_read' => false,
        ]);
        $this->patchJson("/api/stock-alerts/{$a->id}/read")->assertOk();
        $this->assertTrue($a->fresh()->is_read);
    }

    public function test_no_duplicate_alerts_for_same_product(): void
    {
        $p = Product::query()->create([
            'product_code' => 'A4',
            'available_quantity' => 2,
            'damaged_quantity' => 0,
            'minimum_stock' => 10,
        ]);
        $this->put(route('products.update', $p), [
            'product_code' => 'A4',
            'available_quantity' => 2,
            'damaged_quantity' => 0,
            'minimum_stock' => 10,
        ]);
        $this->put(route('products.update', $p), [
            'product_code' => 'A4',
            'available_quantity' => 2,
            'damaged_quantity' => 0,
            'minimum_stock' => 10,
        ]);
        $c = StockAlert::query()->where('product_id', $p->id)->where('is_read', false)->count();
        $this->assertLessThanOrEqual(1, $c);
    }
}
