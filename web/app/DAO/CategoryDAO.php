<?php

namespace App\DAO;

use App\DAO\Interfaces\CategoryDAOInterface;
use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryDAO implements CategoryDAOInterface
{
	public function getAll(): Collection
	{
		return Category::query()->get();
	}

	public function findById(int $id): ?Category
	{
		return Category::query()->find($id);
	}

	public function create(array $data): Category
	{
		return Category::query()->create([
			'name' => trim($data['name']),
		]);
	}

	public function update(int $id, array $data): Category
	{
		$category = Category::query()->findOrFail($id);

		$category->update([
			'name' => trim($data['name']),
		]);

		return $category->refresh();
	}

	public function delete(int $id): bool
	{
		return Category::query()->whereKey($id)->delete() > 0;
	}
}
