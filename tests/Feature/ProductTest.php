<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\StockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product_with_valid_data(): void
    {
        $response = $this->post(route('products.store'), [
            'product_code' => 'TST-001',
            'name' => 'Test',
            'available_quantity' => 10,
            'damaged_quantity' => 0,
            'minimum_stock' => 5,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['product_code' => 'TST-001']);
    }

    public function test_cannot_create_product_without_code(): void
    {
        $response = $this->post(route('products.store'), [
            'available_quantity' => 1,
            'minimum_stock' => 1,
        ]);
        $response->assertSessionHasErrors('product_code');
    }

    public function test_product_code_must_be_unique(): void
    {
        Product::query()->create([
            'product_code' => 'DUP',
            'name' => 'A',
            'available_quantity' => 1,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        $response = $this->post(route('products.store'), [
            'product_code' => 'DUP',
            'available_quantity' => 1,
            'minimum_stock' => 1,
        ]);
        $response->assertSessionHasErrors('product_code');
    }

    public function test_can_update_damaged_quantity(): void
    {
        $p = Product::query()->create([
            'product_code' => 'DMG',
            'available_quantity' => 10,
            'damaged_quantity' => 2,
            'minimum_stock' => 5,
        ]);
        $this->patch(route('products.damaged', $p), [
            'damaged_quantity' => 5,
        ])->assertRedirect();
        $p->refresh();
        $this->assertSame(5, (int) $p->damaged_quantity);
        $this->assertSame(7, (int) $p->available_quantity);
    }

    public function test_damaged_update_recalculates_available_quantity(): void
    {
        $p = Product::query()->create([
            'product_code' => 'DMG2',
            'available_quantity' => 8,
            'damaged_quantity' => 2,
            'minimum_stock' => 1,
        ]);
        $this->patch(route('products.damaged', $p), ['damaged_quantity' => 4]);
        $p->refresh();
        $this->assertSame(6, (int) $p->available_quantity);
    }

    public function test_low_stock_alert_is_created_when_below_minimum(): void
    {
        $p = Product::query()->create([
            'product_code' => 'LOW',
            'available_quantity' => 10,
            'damaged_quantity' => 0,
            'minimum_stock' => 5,
        ]);
        $this->put(route('products.update', $p), [
            'product_code' => 'LOW',
            'available_quantity' => 3,
            'damaged_quantity' => 0,
            'minimum_stock' => 5,
        ]);
        $this->assertTrue(
            StockAlert::query()->where('product_id', $p->id)->where('is_read', false)->exists()
        );
    }

    public function test_can_upload_product_photo(): void
    {
        Storage::fake('public');
        $p = Product::query()->create([
            'product_code' => 'PIC',
            'available_quantity' => 1,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        $file = UploadedFile::fake()->image('p.jpg');
        $this->post(route('products.photo', $p), [
            'photo' => $file,
        ])->assertRedirect();
        $p->refresh();
        $this->assertNotNull($p->photo);
        Storage::disk('public')->assertExists($p->photo);
    }

    public function test_product_history_is_recorded_on_create(): void
    {
        $this->post(route('products.store'), [
            'product_code' => 'H1',
            'available_quantity' => 1,
            'minimum_stock' => 1,
        ]);
        $p = Product::query()->where('product_code', 'H1')->first();
        $this->assertTrue(
            ProductHistory::query()->where('product_id', $p->id)->where('action_type', 'created')->exists()
        );
    }

    public function test_product_history_is_recorded_on_update(): void
    {
        $p = Product::query()->create([
            'product_code' => 'H2',
            'available_quantity' => 5,
            'damaged_quantity' => 0,
            'minimum_stock' => 2,
        ]);
        $this->put(route('products.update', $p), [
            'product_code' => 'H2',
            'available_quantity' => 6,
            'damaged_quantity' => 0,
            'minimum_stock' => 2,
        ]);
        $this->assertTrue(
            ProductHistory::query()->where('product_id', $p->id)->where('action_type', 'updated')->exists()
        );
    }

    public function test_soft_delete_preserves_history(): void
    {
        $p = Product::query()->create([
            'product_code' => 'SD',
            'available_quantity' => 1,
            'damaged_quantity' => 0,
            'minimum_stock' => 1,
        ]);
        ProductHistory::query()->create([
            'product_id' => $p->id,
            'action_type' => 'created',
            'description' => 'x',
        ]);
        $this->delete(route('products.destroy', $p));
        $this->assertSoftDeleted('products', ['id' => $p->id]);
        $this->assertDatabaseHas('product_histories', ['product_id' => $p->id]);
    }
}
