<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('name')->get();

        return response()->json($paymentMethods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);

        $paymentMethod = PaymentMethod::create($validated);

        return response()->json([
            'message' => 'Forma de pagamento cadastrada com sucesso.',
            'payment_method' => $paymentMethod
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        return response()->json($paymentMethod);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);

        $paymentMethod->update($validated);

        return response()->json([
            'message' => 'Forma de pagamento atualizada com sucesso.',
            'payment_method' => $paymentMethod
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return response()->json([
            'message' => 'Forma de pagamento removida com sucesso. '
        ]);
    }
}
