<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WorkspaceOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->latest('id');
        $role = $request->user()->role;

        if ($role === 'Purchasing') {
            $query->where('purchase_required', true);
        } elseif ($role === 'Warehouse') {
            $query->where('purchase_required', false)->whereIn('status', ['Pending', 'Processing']);
        } elseif ($role === 'Route') {
            $query->whereIn('status', ['Ready for Route', 'Shipped']);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['Administrator', 'Sales'], true), 403);

        $data = $request->validate([
            'customer_number' => ['required', 'string', 'max:100'],
            'invoice_number' => ['required', 'string', 'max:100', 'unique:orders,invoice_number'],
            'product_name' => ['required', 'string', 'max:255'],
            'order_date' => ['nullable', 'date'],
            'purchase_required' => ['sometimes', 'boolean'],
        ]);

        $purchaseRequired = (bool) ($data['purchase_required'] ?? false);

        $order = Order::create([
            ...$data,
            'purchase_required' => $purchaseRequired,
            'status' => $purchaseRequired ? 'Pending' : 'Processing',
        ]);

        return response()->json([
            'message' => 'Pedido registrado correctamente.',
            'order' => $order,
        ], 201);
    }

    public function update(Request $request, Order $order)
    {
        $role = $request->user()->role;
        $data = $request->validate([
            'action' => ['required', 'in:sales_update,purchase_complete,warehouse_ready,dispatch,deliver'],
            'delivery_evidence' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $data['action'];

        if ($action === 'sales_update') {
            abort_unless(in_array($role, ['Administrator', 'Sales'], true), 403);
            $details = $request->validate([
                'customer_number' => ['required', 'string', 'max:100'],
                'invoice_number' => ['required', 'string', 'max:100', 'unique:orders,invoice_number,'.$order->id],
                'product_name' => ['required', 'string', 'max:255'],
                'order_date' => ['nullable', 'date'],
                'purchase_required' => ['required', 'boolean'],
            ]);
            $wasPurchaseRequired = $order->purchase_required;
            $order->fill($details);
            if ($details['purchase_required'] && ! $wasPurchaseRequired) {
                $order->status = 'Pending';
            } elseif (! $details['purchase_required'] && $wasPurchaseRequired && $order->status === 'Pending') {
                $order->status = 'Processing';
            }
        } elseif ($action === 'purchase_complete') {
            abort_unless(in_array($role, ['Administrator', 'Purchasing'], true), 403);
            if (! $order->purchase_required) {
                throw ValidationException::withMessages(['action' => 'Este pedido no tiene una compra pendiente.']);
            }
            $order->purchase_required = false;
            $order->status = 'Processing';
        } elseif ($action === 'warehouse_ready') {
            abort_unless(in_array($role, ['Administrator', 'Warehouse'], true), 403);
            $order->status = 'Ready for Route';
        } elseif ($action === 'dispatch') {
            abort_unless(in_array($role, ['Administrator', 'Route'], true), 403);
            $order->status = 'Shipped';
        } elseif ($action === 'deliver') {
            abort_unless(in_array($role, ['Administrator', 'Route'], true), 403);
            $request->validate([
                'delivery_evidence' => ['required', 'string', 'max:2000'],
            ]);
            $order->status = 'Delivered';
            $order->delivery_evidence = $data['delivery_evidence'];
        }

        $order->save();

        return response()->json([
            'message' => 'Pedido actualizado correctamente.',
            'order' => $order->fresh(),
        ]);
    }
}
