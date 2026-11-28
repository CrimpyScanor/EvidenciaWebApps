<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class WorkspaceOrderController extends Controller
{
    // Aplicar filtros reutilizables
    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = $request->validate([
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'customer_number' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:Ordered,In process,In route,Delivered'],
        ]);

        return $query
            ->when($filters['invoice_number'] ?? null, function ($q, $value) {
                $q->where('invoice_number', 'like', "%{$value}%");
            })
            ->when($filters['customer_number'] ?? null, function ($q, $value) {
                $q->where('customer_number', 'like', "%{$value}%");
            })
            ->when($filters['date'] ?? null, function ($q, $value) {
                $q->where(function ($subquery) use ($value) {
                    $subquery->whereDate('order_date', $value)
                        ->orWhereDate('order_datetime', $value);
                });
            })
            ->when($filters['status'] ?? null, function ($q, $value) {
                $q->where('status', $value);
            });
    }

    // Verificar qué pedidos puede consultar cada departamento
    private function canAccessOrder(Request $request, Order $order): bool
    {
        $role = $request->user()->role;

        if (in_array($role, ['Administrator', 'Sales'], true)) {
            return true;
        }

        if ($role === 'Purchasing') {
            return $order->purchase_required;
        }

        if ($role === 'Warehouse') {
            return ! $order->purchase_required
                && in_array($order->status, ['Ordered', 'In process'], true);
        }

        if ($role === 'Route') {
            return in_array($order->status, ['In route', 'Delivered'], true);
        }

        return false;
    }

    // Listar y filtrar pedidos activos
    public function index(Request $request)
    {
        $query = Order::query()->latest('id');
        $role = $request->user()->role;

        if ($role === 'Purchasing') {
            $query->where('purchase_required', true);
        } elseif ($role === 'Warehouse') {
            $query->where('purchase_required', false)
                ->whereIn('status', ['Ordered', 'In process']);
        } elseif ($role === 'Route') {
            $query->whereIn('status', ['In route', 'Delivered']);
        }

        return $this->applyFilters($query, $request)->get();
    }

    // Registrar un nuevo pedido
    public function store(Request $request)
    {
        abort_unless(
            in_array($request->user()->role, ['Administrator', 'Sales'], true),
            403
        );

        $data = $request->validate([
            'customer_number' => ['required', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_fiscal_information' => ['nullable', 'string'],
            'invoice_number' => ['required', 'string', 'max:100', 'unique:orders,invoice_number'],
            'product_name' => ['required', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'order_date' => ['nullable', 'date'],
            'order_datetime' => ['nullable', 'date'],
            'purchase_required' => ['sometimes', 'boolean'],
        ]);

        $data['purchase_required'] = (bool) ($data['purchase_required'] ?? false);

        // Todos los pedidos comienzan como Ordered
        $data['status'] = 'Ordered';

        $order = Order::create($data);

        return response()->json([
            'message' => 'Pedido registrado correctamente.',
            'order' => $order,
        ], 201);
    }

    // Consultar los detalles de un pedido
    public function show(Request $request, Order $order)
    {
        abort_unless($this->canAccessOrder($request, $order), 403);

        return response()->json($order);
    }

    // Actualizar un pedido según el rol
    public function update(Request $request, Order $order)
    {
        $role = $request->user()->role;

        $data = $request->validate([
            'action' => [
                'required',
                'in:sales_update,purchase_complete,warehouse_ready,deliver,admin_set_status'
            ],
            'delivery_evidence' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $data['action'];
        
        if ($action === 'admin_set_status') {
            abort_unless($role === 'Administrator', 403);

            $statusData = $request->validate([
                'status' => [
                    'required',
                    'in:Ordered,In process,In route,Delivered'
                ],
            ]);

            $order->status = $statusData['status'];

        } elseif ($action === 'sales_update') {
            abort_unless(in_array($role, ['Administrator', 'Sales'], true), 403);

            $details = $request->validate([
                'customer_number' => ['required', 'string', 'max:100'],
                'customer_name' => ['nullable', 'string', 'max:255'],
                'customer_fiscal_information' => ['nullable', 'string'],
                'invoice_number' => [
                    'required',
                    'string',
                    'max:100',
                    'unique:orders,invoice_number,' . $order->id
                ],
                'product_name' => ['required', 'string', 'max:255'],
                'delivery_address' => ['nullable', 'string'],
                'notes' => ['nullable', 'string'],
                'order_date' => ['nullable', 'date'],
                'order_datetime' => ['nullable', 'date'],
                'purchase_required' => ['required', 'boolean'],
            ]);

            $wasPurchaseRequired = $order->purchase_required;

            $order->fill($details);

            if ($details['purchase_required'] && ! $wasPurchaseRequired) {
                $order->status = 'Ordered';
            } elseif (
                ! $details['purchase_required']
                && $wasPurchaseRequired
                && $order->status === 'Ordered'
            ) {
                $order->status = 'In process';
            }

        } elseif ($action === 'purchase_complete') {
            abort_unless(in_array($role, ['Administrator', 'Purchasing'], true), 403);

            if (! $order->purchase_required) {
                throw ValidationException::withMessages([
                    'action' => 'Este pedido no tiene una compra pendiente.'
                ]);
            }

            $order->purchase_required = false;
            $order->status = 'In process';

        } elseif ($action === 'warehouse_ready') {
            abort_unless(in_array($role, ['Administrator', 'Warehouse'], true), 403);

            if ($order->purchase_required ||
                ! in_array($order->status, ['Ordered', 'In process'], true)) {
                abort(403);
            }

            $order->status = 'In route';

        } elseif ($action === 'deliver') {
            abort_unless(in_array($role, ['Administrator', 'Route'], true), 403);

            if ($order->status !== 'In route') {
                abort(403);
            }

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

    // Eliminación lógica
    public function destroy(Request $request, Order $order)
    {
        abort_unless(
            in_array($request->user()->role, ['Administrator', 'Sales'], true),
            403
        );

        $order->delete();

        return response()->json([
            'message' => 'Pedido eliminado correctamente.'
        ]);
    }

    // Consultar pedidos eliminados
    public function deletedIndex(Request $request)
    {
        abort_unless(
            in_array($request->user()->role, ['Administrator', 'Sales'], true),
            403
        );

        $query = Order::onlyTrashed()->latest('deleted_at');

        return $this->applyFilters($query, $request)->get();
    }

    // Restaurar un pedido eliminado
    public function restore(Request $request, string $id)
    {
        abort_unless(
            in_array($request->user()->role, ['Administrator', 'Sales'], true),
            403
        );

        $order = Order::onlyTrashed()->findOrFail($id);

        $order->restore();

        return response()->json([
            'message' => 'Pedido restaurado correctamente.',
            'order' => $order->fresh(),
        ]);
    }
}
