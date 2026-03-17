<?php

namespace App\DTO;

use App\Http\Requests\StoreProductRequest;

final class CreateProductDTO
{
	public function __construct(
		public int $category_id,
		public string $name,
		public int $stock,
		public string $description,
		public string $prix,
	) {
	}

	public static function fromRequest(StoreProductRequest $request): self
	{
		return self::fromArray($request->validated());
	}

	public static function fromArray(array $data): self
	{
		return new self(
			category_id: (int) $data['category_id'],
			name: trim($data['name']),
			stock: (int) $data['stock'],
			description: trim($data['description']),
			prix: number_format((float) $data['prix'], 2, '.', ''),
		);
	}

	public function toArray(): array
	{
		return [
			'category_id' => $this->category_id,
			'name' => $this->name,
			'stock' => $this->stock,
			'description' => $this->description,
			'prix' => $this->prix,
		];
	}
}
