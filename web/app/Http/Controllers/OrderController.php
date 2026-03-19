<?php

namespace App\Http\Controllers;

use _PHPStan_781aefaf6\React\Http\Io\Transaction;
use App\DAO\Interfaces\OrderDAOInterface;
use App\DTO\CreateOrderDTO;
use App\DTO\OrderItemDTO;
use App\Models\Product;
use DB;
use Dotenv\Repository\RepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderDAOInterface $orderDAO,
    ) {
    }

    public function index(): JsonResponse
    {
        $orders = $this->orderDAO->getAll();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderDAO->findById($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json($order);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,preparing,delivered,cancelled'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);
        $user = auth('api')->user();
        $order = DB::transaction(function () use ($validated, $user) {
            $items = [];
            
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'message' => "Product '{$product->name}' has insufficient stock. Available: {$product->stock}, Requested: {$item['quantity']}"
                    ]);
                }
                
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'ItemPrice' => $product->prix,
                ];
            }
            $order = $this->orderDAO->create(
                CreateOrderDTO::fromArray([
                    'user_id' => $user->id,
                    'status' => 'pending'
                ])
            );
            $order->orderItems()->createMany($items);

            return $order->load('orderItems');
        });
        return response()->json($order, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $order = $this->orderDAO->findById($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,preparing,delivered,cancelled'],
        ]);

        $updatedOrder = $this->orderDAO->updateStatus($id, $validated['status']);

        return response()->json($updatedOrder);
    }

    public function addItem(Request $request, int $orderId): JsonResponse
    {
        $order = $this->orderDAO->findById($orderId);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'ItemPrice' => ['nullable', 'numeric', 'min:0'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock < $validated['quantity']) {
            return response()->json([
                'message' => "Product has insufficient stock. Available: {$product->stock}, Requested: {$validated['quantity']}",
            ], 422);
        }

        $dto = OrderItemDTO::fromArray([
            'order_id' => $orderId,
                        'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'ItemPrice' => $validated['ItemPrice'] ?? $product->prix,
        ]);

        $this->orderDAO->addItem($orderId, $dto);

    $product->decrement('stock', $validated['quantity']);

        return response()->json([
            'message' => 'Item added successfully.',
            'order' => $this->orderDAO->findById($orderId),
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = $this->orderDAO->findById($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $this->orderDAO->delete($id);

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Please authenticate before cancelling an order.',
            ], 401);
        }

        $order = $this->orderDAO->findById($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to cancel this order.',
            ], 403);
        }

        if ($order->status === 'delivered') {
            return response()->json([
                'message' => 'Cannot cancel an order that has already been delivered.',
            ], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Order is already cancelled.',
            ], 422);
        }

        $cancelledOrder = $this->orderDAO->updateStatus($id, 'cancelled');

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order' => $cancelledOrder,
        ]);
    }

    public function prepare(int $id): JsonResponse
    {
        $order = $this->orderDAO->findById($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Cannot prepare a cancelled order.',
            ], 422);
        }

        if ($order->status === 'delivered') {
            return response()->json([
                'message' => 'Cannot prepare an order that has already been delivered.',
            ], 422);
        }

        if ($order->status === 'preparing') {
            return response()->json([
                'message' => 'Order is already being prepared.',
            ], 422);
        }

        $preparedOrder = $this->orderDAO->updateStatus($id, 'preparing');

        return response()->json([
            'message' => 'Order prepared successfully.',
            'order' => $preparedOrder,
        ]);
    }
}
                            // Restore product stock before deleting
                            foreach ($order->orderItems as $item) {
                                $product = Product::findOrFail($item->product_id);
                                $product->increment('stock', $item->quantity);
                            }

                    // Restore product stock
                    foreach ($order->orderItems as $item) {
                        $product = Product::findOrFail($item->product_id);
                        $product->increment('stock', $item->quantity);
                    }

            
            
            
            // Decrement product stock
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }
