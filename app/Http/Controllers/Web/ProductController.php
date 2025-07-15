<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $productData = $this->productService->storeProduct($request->only(['name', 'quantity', 'price']));

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $productData,
                'message' => 'Product added successfully!'
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

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