<?php

namespace App\DAO\Interfaces;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryDAOInterface
{
	public function getAll(): Collection;

	public function findById(int $id): ?Category;

	public function create(array $data): Category;

	public function update(int $id, array $data): Category;

	public function delete(int $id): bool;
}
