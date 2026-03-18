<?php

namespace App\DAO;

use App\DAO\Interfaces\OrderDAOInterface;
use App\DTO\CreateOrderDTO;
use App\DTO\OrderItemDTO;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderDAO implements OrderDAOInterface
{
	public function getAll(): Collection
	{
		return Order::query()->with(['user', 'orderItems'])->get();
	}

	public function findById(int $id): ?Order
	{
		return Order::query()->with(['user', 'orderItems'])->find($id);
	}

	public function create(CreateOrderDTO $dto): Order
	{
		return DB::transaction(function () use ($dto): Order {
			$orderPayload = method_exists($dto, 'toOrderArray')
				? $dto->toOrderArray()
				: [
					'user_id' => $dto->user_id,
					'status' => $dto->status ?? 'pending',
				];

			$order = Order::query()->create($orderPayload);

			$itemsPayload = method_exists($dto, 'toOrderItemsArray')
				? $dto->toOrderItemsArray()
				: [];

			if (!empty($itemsPayload)) {
				$order->orderItems()->createMany($itemsPayload);
			}

			return $order->load(['user', 'orderItems']);
		});
	}

	public function addItem(int $orderId, OrderItemDTO $dto): void
	{
		$order = Order::query()->findOrFail($orderId);
		$payload = $dto->toArray();
		$payload['order_id'] = $order->id;

		$order->orderItems()->create($payload);
	}

	public function updateStatus(int $id, string $status): Order
	{
		$order = Order::query()->findOrFail($id);
		$order->update(['status' => $status]);

		return $order->load(['user', 'orderItems']);
	}

	public function delete(int $id): bool
	{
		return DB::transaction(function () use ($id): bool {
			$order = Order::query()->find($id);

			if (!$order) {
				return false;
			}

			$order->orderItems()->delete();

			return $order->delete();
		});
	}
}
