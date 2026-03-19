<?php

namespace App\Http\Controllers;

use App\DAO\Interfaces\ProductDAOInterface;
use App\DTO\CreateProductDTO;
use App\Http\Requests\StoreProductImageRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    public function __construct(
        private ProductDAOInterface $productDAO
    ) {
    }

    public function index()
    {
        $products = $this->productDAO->getAll();
        return response()->json([
            'products' => $products
        ]);
    }
    public function show(int $id)
    {
        $product = $this->productDAO->findById($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json($product);
    }
    public function store(StoreProductRequest $request)
    {
        $dto = CreateProductDTO::fromArray($request->validated());
        $product = $this->productDAO->create($dto);

        return response()->json($product, 201);
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $product = $this->productDAO->findById($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $dto = CreateProductDTO::fromArray($request->validated());
        $updatedProduct = $this->productDAO->update($id, $dto);

        return response()->json($updatedProduct);
    }

    public function destroy(int $id)
    {
        $product = $this->productDAO->findById($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $this->productDAO->delete($id);

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function addImage(StoreProductImageRequest $request, int $id)
    {
        $product = $this->productDAO->findById($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $files = [];

        if ($request->hasFile('image')) {
            $files[] = $request->file('image');
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $files[] = $file;
            }
        }

        $createdImages = [];

        foreach ($files as $file) {
            $path = $file->store('products', 'public');
            $createdImages[] = $product->images()->create([
                'content' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Image(s) added successfully.',
            'images' => $createdImages,
        ], 201);
    }
}
