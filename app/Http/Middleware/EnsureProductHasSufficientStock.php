<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProductHasSufficientStock
{
    public function handle(Request $request, Closure $next): Response
    {
        $items = $request->input('items', []);
        foreach ($items as $i => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);
            if ($productId < 1 || $qty < 1) {
                continue;
            }
            $product = Product::query()->select(['id', 'product_code', 'available_quantity'])->find($productId);
            if ($product && $qty > (int) $product->available_quantity) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Stock insuficiente.',
                        'errors' => [
                            "items.{$i}.quantity" => ['Stock insuficiente para '.($product->product_code ?? 'el producto')],
                        ],
                    ], 422);
                }

                return back()->withErrors([
                    "items.{$i}.quantity" => 'Stock insuficiente.',
                ])->withInput();
            }
        }

        return $next($request);
    }
}
