<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Services\ProductService;
use App\Models\Product;
use App\Traits\ResponseTrait;

class ProductController extends BaseController
{
    use ResponseTrait;

    public function __construct(protected ProductService $productService){}

    public function index()
    {
        $products = $this->productService->getAllProducts();
        return view('products.index', compact('products'));
    }

    public function store(StoreProductRequest $request)
    {
        $productData = $this->productService->storeProduct($request->validated());
        $product = Product::create($productData);


        return $this->success([
            'product' => new ProductResource($product),
            'message' => 'Product added successfully!'
        ]);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $updatedProduct = $this->productService->updateProduct($id, $request->validated());

        if (!$updatedProduct) {
            return $this->error('Product not found');
        }

        return $this->success([
            'product' => $updatedProduct,
            'message' => 'Product updated successfully!'
        ]);
    }
    public function getProducts()
    {
        $products = $this->productService->getAllProducts();
        $totalSum = $this->productService->getTotalSum();
        
        return $this->success([
            'products' => $products,
            'total_sum' => $totalSum
        ]);
    }
}