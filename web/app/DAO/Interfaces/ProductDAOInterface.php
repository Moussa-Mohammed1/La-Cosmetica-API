<?php

namespace App\DAO\Interfaces;

use App\DTO\CreateProductDTO;
use App\Models\Product;
use Illuminate\Support\Collection;

interface ProductDAOInterface
{
	public function getAll(): Collection;

	public function findById(int $id): ?Product;

	public function create(CreateProductDTO $dto): Product;

	public function update(int $id, CreateProductDTO $dto): Product;

	public function delete(int $id): bool;
}
