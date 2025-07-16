<?php
namespace App\Http\Controllers\Web\Products;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    public function __construct(protected ProductService $productService){}

    public function index()
    {
        $products = $this->productService->getAllProducts();
        return view('products.index', compact('products'));
    }

    public function store(StoreProductRequest $request)
    {
        $productData = $this->productService->storeProduct($request->only(['name', 'quantity', 'price']));

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $productData,
                'message' => 'Product added successfully!'
            ]
        ]);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $updatedProduct = $this->productService->updateProduct($id, $request->only(['name', 'quantity', 'price']));

        if (!$updatedProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $updatedProduct,
                'message' => 'Product updated successfully!'
            ]
        ]);
    }


    public function getProducts()
    {
        $products = $this->productService->getAllProducts();
        $totalSum = $this->productService->getTotalSum();
        
        return response()->json([
            'success' => true,
            'products' => $products,
            'total_sum' => $totalSum
        ]);
    }
}