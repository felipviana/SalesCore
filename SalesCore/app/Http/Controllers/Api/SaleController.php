<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sale::with([
            'customer',
            'items.product',
            'payments.paymentMethod'
        ]);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $sales = $query
            ->latest()
            ->get();

        return response()->json($sales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],

            'discount' => ['nullable', 'numeric', 'min:0'],
            'addition' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],

            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.installments' => ['nullable', 'integer', 'min:1'],
            'payments.*.authorization_code' => ['nullable', 'string', 'max:100'],
        ]);

        return DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($validated['items'] as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);

                if (!$this->isActive($product->active)) {
                    throw ValidationException::withMessages([
                        'items' => "O produto {$product->name} está inativo."
                    ]);
                }

                $quantity = (float) $itemData['quantity'];
                $availableStock = (float) $product->stock_quantity;

                if ($availableStock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Estoque insuficiente para o produto {$product->name}. Estoque atual: {$availableStock}."
                    ]);
                }

                $unitPrice = (float) $product->sale_price;
                $itemDiscount = (float) ($itemData['discount'] ?? 0);
                $itemTotal = ($quantity * $unitPrice) - $itemDiscount;

                if ($itemTotal < 0) {
                    throw ValidationException::withMessages([
                        'items' => "O desconto do produto {$product->name} não pode ser maior que o total do item."
                    ]);
                }

                $subtotal += $itemTotal;

                $newStock = $availableStock - $quantity;

                $itemsToCreate[] = [
                    'product' => $product,
                    'previous_stock' => $availableStock,
                    'new_stock' => $newStock,
                    'data' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit' => $product->unit,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount' => $itemDiscount,
                        'total' => $itemTotal,
                    ]
                ];
            }

            $saleDiscount = (float) ($validated['discount'] ?? 0);
            $addition = (float) ($validated['addition'] ?? 0);
            $total = ($subtotal - $saleDiscount) + $addition;

            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'total' => 'O total da venda precisa ser maior que zero.'
                ]);
            }

            $paidTotal = collect($validated['payments'])->sum(function ($payment) {
                return (float) $payment['amount'];
            });

            if (round($paidTotal, 2) !== round($total, 2)) {
                throw ValidationException::withMessages([
                    'payments' => 'O total pago precisa ser igual ao total da venda.'
                ]);
            };

            $sale = Sale::create([
                'customer_id' => $validated['customer_id'] ?? null,
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount' => $saleDiscount,
                'addition' => $addition,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsToCreate as $itemToCreate) {
                $sale->items()->create($itemToCreate['data']);

                $product = $itemToCreate['product'];

                $product->update([
                    'stock_quantity' => $itemToCreate['new_stock']
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'type' => 'sale',
                    'quantity' => abs($itemToCreate['data']['quantity']),
                    'previous_stock' => $itemToCreate['previous_stock'],
                    'new_stock' => $itemToCreate['new_stock'],
                    'source_type' => 'sale',
                    'source_id' => $sale->id,
                    'notes' => "Saída de estoque pela venda #{$sale->id}.",
                ]);
            }

            foreach ($validated['payments'] as $paymentData) {
                $paymentMethod = PaymentMethod::findOrFail($paymentData['payment_method_id']);

                $sale->payments()->create([
                    'payment_method_id' => $paymentMethod->id,
                    'payment_method_name' => $paymentMethod->name,
                    'amount' => $paymentData['amount'],
                    'installments' => $paymentData['installments'] ?? null,
                    'authorization_code' => $paymentData['authorization_code'] ?? null,
                ]);
            }

            $sale->load([
                'customer',
                'items.product',
                'payments.paymentMethod'
            ]);

            return response()->json([
                'message' => 'Venda registrada com sucesso.',
                'sale' => $sale
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'customer',
            'items.product',
            'payments.paymentMethod'
        ]);

        return response()->json($sale);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        return response()->json([
            'message' => 'Atualização de venda ainda não implementada. Nesta fase, a venda registrada poderá ser cancelada.'
        ], 501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        if ($sale->status === 'canceled') {
            return response()->json([
                'message' => 'Esta venda já esta cancelada.'
            ], 422);
        }

        DB::transaction(function () use ($sale) {
            $sale->load('items.product');

            foreach ($sale->items as $item) {
                if (!$item->product_id) {
                    continue;
                }

                $product = Product::lockForUpdate()->find($item->product_id);

                if (!$product) {
                    continue;
                }

                $previousStock = (float) $product->stock_quantity;
                $quantity = (float) $item->quantity;
                $newStock = $previousStock + $quantity;

                $product->update([
                    'stock_quantity' => $newStock
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'type' => 'sale_cancel',
                    'quantity' => abs($quantity),
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'source_type' => 'sale',
                    'source_id' => $sale->id,
                    'notes' => "Entrada de estoque pelo cancelamento da venda #{$sale->id}.",
                ]);
            }

            $sale->update([
                'status' => 'canceled'
            ]);
        });

        return response()->json([
            'message' => 'Venda cancelada com sucesso.'
        ]);
    }

    private function isActive($value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }
}
