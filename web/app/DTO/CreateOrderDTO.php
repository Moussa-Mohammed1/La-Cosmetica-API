<?php

namespace App\DTO;

use Illuminate\Http\Request;

final class CreateOrderDTO
{ 
	public function __construct(
		public int $user_id,
		public string $status = 'pending',
		public array $items = [],
	) {
	}

	public static function fromRequest(Request $request): self
	{
		$data = method_exists($request, 'validated')
			? $request->validated()
			: $request->all();

		return self::fromArray($data);
	}

	public static function fromArray(array $data): self
	{
		$items = array_map(
			static function (array $item): array {
				$price = $item['ItemPrice'] ?? $item['item_price'] ?? 0;

				return [
					'quantity' => (int) $item['quantity'],
					'ItemPrice' => number_format((float) $price, 2, '.', ''),
				];
			},
			$data['items'] ?? []
		);

		return new self(
			user_id: (int) $data['user_id'],
			status: $data['status'] ?? 'pending',
			items: $items,
		);
	}

	public function toOrderArray(): array
	{
		return [
			'user_id' => $this->user_id,
			'status' => $this->status,
		];
	}

	public function toOrderItemsArray(): array
	{
		return $this->items;
	}
}
