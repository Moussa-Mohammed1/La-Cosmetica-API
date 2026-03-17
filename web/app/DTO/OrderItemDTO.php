<?php

namespace App\DTO;

use App\Http\Requests\StoreOrderItemRequest;

final class OrderItemDTO
{
	public function __construct(
		public int $order_id,
		public int $quantity,
		public string $ItemPrice,
	) {
	}

	public static function fromRequest(StoreOrderItemRequest $request): self
	{
		return self::fromArray($request->validated());
	}

	public static function fromArray(array $data): self
	{
		return new self(
			order_id: (int) $data['order_id'],
			quantity: (int) $data['quantity'],
			ItemPrice: number_format((float) $data['ItemPrice'], 2, '.', ''),
		);
	}

	public function toArray(): array
	{
		return [
			'order_id' => $this->order_id,
			'quantity' => $this->quantity,
			'ItemPrice' => $this->ItemPrice,
		];
	}
}
