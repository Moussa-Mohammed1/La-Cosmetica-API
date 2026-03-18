<?php

namespace App\Http\Controllers;

use App\DAO\Interfaces\CategoryDAOInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryDAOInterface $categoryDAO,
    ) {
    }

    public function index(): JsonResponse
    {
        $categories = $this->categoryDAO->getAll();

        return response()->json($categories);
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryDAO->findById($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json($category);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = $this->categoryDAO->create($validated);

        return response()->json($category, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = $this->categoryDAO->findById($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $updatedCategory = $this->categoryDAO->update($id, $validated);

        return response()->json($updatedCategory);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = $this->categoryDAO->findById($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        $this->categoryDAO->delete($id);

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
