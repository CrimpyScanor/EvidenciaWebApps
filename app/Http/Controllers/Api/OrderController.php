<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user:id,name,email', 'invoice'])
            ->latest('created_at')->paginate(15);
        return response()->json($orders);
    }

    public function archived()
    {
        $orders = Order::onlyTrashed()->with(['user:id,name,email', 'invoice'])
            ->orderByDesc('deleted_at')->paginate(15);
        return response()->json($orders);
    }

    public function show(Order $order)
    {
        return response()->json(['data' => $order->load(['user:id,name,email', 'invoice', 'items'])]);
    }

    public function showByInvoice(string $invoice)
    {
        $record = Invoice::where('invoice_number', $invoice)->with(['order.user:id,name,email', 'order.items'])->first();
        if (!$record || !$record->order) {
            return response()->json(['message' => 'Order with this invoice number was not found.'], 404);
        }
        return response()->json(['data' => $record->order->load('invoice')]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
            'total_value' => ['required', 'integer', 'min:0'],
            'tax' => ['required', 'integer', 'min:0'],
            'shipping_charges' => ['required', 'integer', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create($validated + ['status' => 'pending']);
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
            Invoice::create([
                'invoice_number' => $invoiceNumber,
                'raised_at' => now(),
                'status' => 'issued',
                'total_amount' => $order->total_value + $order->tax + $order->shipping_charges,
                'order_id' => $order->id,
            ]);
            return $order;
        });

        return response()->json(['message' => 'Order created successfully.', 'data' => $order->load(['user:id,name,email', 'invoice'])], 201);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
            'total_value' => ['sometimes', 'required', 'integer', 'min:0'],
            'tax' => ['sometimes', 'required', 'integer', 'min:0'],
            'shipping_charges' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);
        $order->update($validated);
        if ($order->invoice) {
            $order->invoice->update(['total_amount' => $order->total_value + $order->tax + $order->shipping_charges]);
        }
        return response()->json(['message' => 'Order updated successfully.', 'data' => $order->fresh()->load(['user:id,name,email', 'invoice'])]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
        ]);
        $order->update(['status' => $validated['status']]);
        return response()->json(['message' => 'Order status updated successfully.', 'data' => $order->fresh()->load('invoice')]);
    }

    public function destroy(Order $order)
    {
        $order->delete(); // SoftDeletes archives the order instead of permanently removing it.
        return response()->json(['message' => 'Order archived successfully.', 'order_id' => $order->id]);
    }
}
