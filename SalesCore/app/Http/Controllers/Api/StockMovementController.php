<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with('product');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $stockMovements = $query
            ->latest()
            ->get();

        return response()->json($stockMovements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::in(['manual_entry', 'manual_exit', 'adjustment'])],
            'quantity' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated){
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

            $previousStock = (float) $product->stock_quantity;
            $quantity = (float) $validated['quantity'];
            $type = $validated['type'];

            if ($type !== 'adjustment' && $quantity <= 0){
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade precisa ser maior que zero.'
                ]);
            }

            if ($type === 'manual_entry') {
                $movementQuantity = abs($quantity);
                $newStock = $previousStock + $movementQuantity;
            } elseif ($type === 'manual_exit') {
                if ($previousStock < $quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Estoque insuficiente. Estoque atual: {$previousStock}.'
                    ]);
                }

                $movementQuantity = -abs($quantity);
                $newStock = $previousStock - $quantity;
            } else {
                $newStock = $quantity;
                $movementQuantity = $newStock - $previousStock;
            }

            $product->update([
                'stock_quantity' => $newStock
            ]);

            $stockMovement = StockMovement::create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'type' => $type,
                'quantity' => $movementQuantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'source_type' => 'manual',
                'source_id' => null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'message' => 'Movimentação de estoque registrada com sucesso.',
                'stock_movement' => $stockMovement
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        $stockMovement->load('product');

        return response()->json($stockMovement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
