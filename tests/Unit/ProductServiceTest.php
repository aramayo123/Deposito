<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_status_out_when_zero_available(): void
    {
        $p = Product::query()->make([
            'available_quantity' => 0,
            'damaged_quantity' => 0,
            'minimum_stock' => 5,
        ]);
        $this->assertSame('out', $p->stock_status);
    }

    public function test_total_quantity_accessor(): void
    {
        $p = Product::query()->make([
            'available_quantity' => 3,
            'damaged_quantity' => 2,
            'minimum_stock' => 1,
        ]);
        $this->assertSame(5, $p->total_quantity);
    }
}
