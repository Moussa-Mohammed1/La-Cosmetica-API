<?php

namespace App\DAO\Interfaces;

use App\DTO\CreateOrderDTO;
use App\DTO\OrderItemDTO;
use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderDAOInterface
{
	public function getAll(): Collection;

	public function findById(int $id): ?Order;

	public function create(CreateOrderDTO $dto): Order;

	public function addItem(int $orderId, OrderItemDTO $dto): void;

	public function updateStatus(int $id, string $status): Order;

	public function delete(int $id): bool;
}
