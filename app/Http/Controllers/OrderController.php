<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'customer_number' => 'required|string',
            'invoice_number' => 'required|string',
        ]);

        $order = Order::where(
            'customer_number',
            $validated['customer_number']
        )
        ->where(
            'invoice_number',
            $validated['invoice_number']
        )
        ->first();

        if (!$order) {
            return response()->json([
                'message' => 'No order found. Please check your information.'
            ], 404);
        }

        return response()->json([
            'message' => 'Order found successfully!',
            'order' => $order
        ], 200);
    }
}
