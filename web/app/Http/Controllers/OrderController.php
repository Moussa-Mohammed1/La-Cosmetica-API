<?php

namespace App\Http\Controllers;

use _PHPStan_781aefaf6\React\Http\Io\Transaction;
use App\DAO\Interfaces\OrderDAOInterface;
use App\DTO\CreateOrderDTO;
use App\DTO\OrderItemDTO;
use App\Models\Product;
use DB;
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
        $order = DB::transaction(function() use ($validated, $user){
            $items = [];
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $item[] = [
                    'product_id' => $product->id,
                    'quantity' =>$item['quantity'],
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
            'quantity' => ['required', 'integer', 'min:1'],
            'ItemPrice' => ['required', 'numeric', 'min:0'],
           ]);

        $dto = OrderItemDTO::fromArray([
            'order_id' => $orderId,
            'quantity' => $validated['quantity'],
            'ItemPrice' => $validated['ItemPrice'] ?? $validated['item_price'],
        ]);

        $this->orderDAO->addItem($orderId, $dto);

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
}
