<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\HistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_entry_event_correctly(): void
    {
        $p = Product::query()->create([
            'product_code' => 'U1',
            'available_quantity' => 5,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        app(HistoryService::class)->recordEntry($p, 99, 3, 1, 5, 7, 0, 1);
        $h = $p->histories()->first();
        $this->assertSame('entry', $h->action_type);
        $this->assertSame(3, (int) $h->quantity_change);
    }

    public function test_records_exit_event_correctly(): void
    {
        $p = Product::query()->create([
            'product_code' => 'U2',
            'available_quantity' => 10,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        app(HistoryService::class)->recordExit($p, 88, 4, 10, 6, 'T', 'P');
        $h = $p->histories()->first();
        $this->assertSame('exit', $h->action_type);
        $this->assertSame(-4, (int) $h->quantity_change);
    }

    public function test_records_damaged_event_correctly(): void
    {
        $p = Product::query()->create([
            'product_code' => 'U3',
            'available_quantity' => 8,
            'damaged_quantity' => 2,
            'minimum_stock' => 1,
        ]);
        app(HistoryService::class)->recordDamaged($p, 2, 8, 6, 2, 4);
        $h = $p->histories()->first();
        $this->assertSame('damaged_marked', $h->action_type);
    }

    public function test_quantity_before_and_after_are_correct(): void
    {
        $p = Product::query()->create([
            'product_code' => 'U4',
            'available_quantity' => 10,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        app(HistoryService::class)->recordExit($p, 1, 3, 10, 7, null, null);
        $h = $p->histories()->first();
        $this->assertSame(10, (int) $h->quantity_before);
        $this->assertSame(7, (int) $h->quantity_after);
    }
}
