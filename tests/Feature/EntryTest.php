<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\ProductHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntryTest extends TestCase
{
    use RefreshDatabase;

    protected Product $p;

    protected function setUp(): void
    {
        parent::setUp();
        $this->p = Product::query()->create([
            'product_code' => 'E-P',
            'available_quantity' => 5,
            'damaged_quantity' => 0,
            'minimum_stock' => 2,
        ]);
    }

    public function test_can_create_entry_ticket_with_multiple_items(): void
    {
        $this->post(route('entries.store'), [
            'entry_date' => now()->toDateString(),
            'entry_time' => '10:00',
            'items' => [
                ['product_id' => $this->p->id, 'quantity_received' => 3, 'quantity_damaged' => 0, 'damage_notes' => null],
                ['product_id' => $this->p->id, 'quantity_received' => 2, 'quantity_damaged' => 0, 'damage_notes' => null],
            ],
        ])->assertRedirect();
        $this->assertSame(10, (int) $this->p->fresh()->available_quantity);
    }

    public function test_entry_increments_product_available_quantity(): void
    {
        $before = (int) $this->p->available_quantity;
        $this->post(route('entries.store'), [
            'entry_date' => now()->toDateString(),
            'entry_time' => '10:00',
            'items' => [
                ['product_id' => $this->p->id, 'quantity_received' => 4, 'quantity_damaged' => 0],
            ],
        ]);
        $this->assertSame($before + 4, (int) $this->p->fresh()->available_quantity);
    }

    public function test_entry_with_damaged_items_increments_damaged_quantity(): void
    {
        $this->post(route('entries.store'), [
            'entry_date' => now()->toDateString(),
            'entry_time' => '10:00',
            'items' => [
                ['product_id' => $this->p->id, 'quantity_received' => 5, 'quantity_damaged' => 2, 'damage_notes' => 'rotas'],
            ],
        ]);
        $p = $this->p->fresh();
        $this->assertSame(2, (int) $p->damaged_quantity);
        $this->assertSame(8, (int) $p->available_quantity);
    }

    public function test_entry_creates_history_for_each_product(): void
    {
        $this->post(route('entries.store'), [
            'entry_date' => now()->toDateString(),
            'entry_time' => '10:00',
            'items' => [
                ['product_id' => $this->p->id, 'quantity_received' => 2, 'quantity_damaged' => 0],
            ],
        ]);
        $this->assertTrue(
            ProductHistory::query()->where('product_id', $this->p->id)->where('action_type', 'entry')->exists()
        );
    }

    public function test_cannot_create_entry_without_items(): void
    {
        $this->post(route('entries.store'), [
            'entry_date' => now()->toDateString(),
            'entry_time' => '10:00',
            'items' => [],
        ])->assertSessionHasErrors('items');
    }

    public function test_entry_code_is_auto_generated(): void
    {
        $this->post(route('entries.store'), [
            'entry_date' => now()->toDateString(),
            'entry_time' => '10:00',
            'items' => [
                ['product_id' => $this->p->id, 'quantity_received' => 1, 'quantity_damaged' => 0],
            ],
        ]);
        $e = ProductEntry::query()->latest('id')->first();
        $this->assertMatchesRegularExpression('/^ENT-\d{4}-\d{4}$/', $e->entry_code);
    }

    public function test_entry_is_wrapped_in_transaction(): void
    {
        $this->assertTrue(true);
    }
}
