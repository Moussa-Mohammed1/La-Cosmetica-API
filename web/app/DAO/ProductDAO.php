<?php

namespace App\DAO;

use App\DAO\Interfaces\ProductDAOInterface;
use App\DTO\CreateProductDTO;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductDAO implements ProductDAOInterface
{
	public function getAll(): Collection
	{
		return Product::query()->with(['category', 'images'])->get();
	}

	public function findById(int $id): ?Product
	{
		return Product::query()->with(['category', 'images'])->find($id);
	}

	public function create(CreateProductDTO $dto): Product
	{
		return Product::query()->create($dto->toArray());
	}

	public function update(int $id, CreateProductDTO $dto): Product
	{
		$product = Product::query()->findOrFail($id);
		$product->update($dto->toArray());

		return $product->refresh();
	}

	public function delete(int $id): bool
	{
		return Product::query()->whereKey($id)->delete() > 0;
	}
}
