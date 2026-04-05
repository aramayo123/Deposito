<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductExit;
use App\Models\ProductHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExitTest extends TestCase
{
    use RefreshDatabase;

    protected Product $p;

    protected function setUp(): void
    {
        parent::setUp();
        $this->p = Product::query()->create([
            'product_code' => 'X-P',
            'available_quantity' => 20,
            'damaged_quantity' => 0,
            'minimum_stock' => 2,
        ]);
    }

    public function test_can_create_exit_for_technician(): void
    {
        $this->post(route('exits.store'), [
            'exit_date' => now()->toDateString(),
            'exit_time' => '11:00',
            'technician_name' => 'Diego',
            'license_plate' => 'ABC123',
            'is_for_workshop' => false,
            'items' => [
                ['product_id' => $this->p->id, 'quantity' => 2],
            ],
        ])->assertRedirect();
        $this->assertSame(18, (int) $this->p->fresh()->available_quantity);
    }

    public function test_can_create_exit_for_workshop(): void
    {
        $this->post(route('exits.store'), [
            'exit_date' => now()->toDateString(),
            'exit_time' => '11:00',
            'is_for_workshop' => true,
            'items' => [
                ['product_id' => $this->p->id, 'quantity' => 1],
            ],
        ])->assertRedirect();
    }

    public function test_exit_decrements_available_quantity(): void
    {
        $this->post(route('exits.store'), [
            'exit_date' => now()->toDateString(),
            'exit_time' => '11:00',
            'is_for_workshop' => true,
            'items' => [
                ['product_id' => $this->p->id, 'quantity' => 5],
            ],
        ]);
        $this->assertSame(15, (int) $this->p->fresh()->available_quantity);
    }

    public function test_cannot_exit_more_than_available_stock(): void
    {
        $this->post(route('exits.store'), [
            'exit_date' => now()->toDateString(),
            'exit_time' => '11:00',
            'is_for_workshop' => true,
            'items' => [
                ['product_id' => $this->p->id, 'quantity' => 100],
            ],
        ])->assertSessionHasErrors();
    }

    public function test_exit_requires_technician_when_not_workshop(): void
    {
        $this->post(route('exits.store'), [
            'exit_date' => now()->toDateString(),
            'exit_time' => '11:00',
            'is_for_workshop' => false,
            'items' => [
                ['product_id' => $this->p->id, 'quantity' => 1],
            ],
        ])->assertSessionHasErrors('technician_name');
    }

    public function test_exit_creates_history_for_each_product(): void
    {
        $this->post(route('exits.store'), [
            'exit_date' => now()->toDateString(),
            'exit_time' => '11:00',
            'is_for_workshop' => true,
            'items' => [
                ['product_id' => $this->p->id, 'quantity' => 1],
            ],
        ]);
        $this->assertTrue(
            ProductHistory::query()->where('product_id', $this->p->id)->where('action_type', 'exit')->exists()
        );
    }

    public function test_exit_code_is_auto_generated(): void
    {
        $this->post(route('exits.store'), [
            'exit_date' => now()->toDateString(),
            'exit_time' => '11:00',
            'is_for_workshop' => true,
            'items' => [
                ['product_id' => $this->p->id, 'quantity' => 1],
            ],
        ]);
        $x = ProductExit::query()->latest('id')->first();
        $this->assertMatchesRegularExpression('/^SAL-\d{4}-\d{4}$/', $x->exit_code);
    }

    public function test_can_filter_exits_by_technician(): void
    {
        ProductExit::query()->create([
            'exit_code' => 'SAL-2099-9999',
            'exit_date' => now()->toDateString(),
            'exit_time' => '12:00:00',
            'technician_name' => 'Diego Test',
            'license_plate' => null,
            'is_for_workshop' => false,
        ]);
        $r = $this->getJson('/api/exits?technician=Diego');
        $r->assertOk();
        $data = $r->json('data');
        $this->assertNotEmpty($data);
    }

    public function test_can_filter_exits_by_license_plate(): void
    {
        ProductExit::query()->create([
            'exit_code' => 'SAL-2099-9998',
            'exit_date' => now()->toDateString(),
            'exit_time' => '12:00:00',
            'technician_name' => 'X',
            'license_plate' => 'ABC123',
            'is_for_workshop' => false,
        ]);
        $r = $this->getJson('/api/exits?license_plate=ABC123');
        $r->assertOk();
    }
}
