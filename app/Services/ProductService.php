<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ProductService
{
    private $jsonFile = 'products.json';

    public function getAllProducts()
    {
        if (!Storage::exists($this->jsonFile)) {
            return [];
        }

        $jsonContent = Storage::get($this->jsonFile);
        $products = json_decode($jsonContent, true) ?? [];

        usort($products, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $products;
    }

    public function storeProduct(array $data)
    {
        $productData = [
            'id' => time() . rand(1000, 9999),
            'name' => $data['name'],
            'quantity' => (int)$data['quantity'],
            'price' => (float)$data['price'],
            'total_value' => (int)$data['quantity'] * (float)$data['price'],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];

        $products = $this->getAllProducts();
        array_unshift($products, $productData);
        Storage::put($this->jsonFile, json_encode($products, JSON_PRETTY_PRINT));

        return $productData;
    }

    public function updateProduct($id, array $data)
    {
        $products = $this->getAllProducts();
        $productIndex = null;

        foreach ($products as $index => $product) {
            if ($product['id'] == $id) {
                $productIndex = $index;
                break;
            }
        }

        if ($productIndex === null) {
            return null;
        }

        $products[$productIndex] = [
            'id' => $id,
            'name' => $data['name'],
            'quantity' => (int)$data['quantity'],
            'price' => (float)$data['price'],
            'total_value' => (int)$data['quantity'] * (float)$data['price'],
            'created_at' => $products[$productIndex]['created_at'],
            'updated_at' => now()->toISOString()
        ];

        Storage::put($this->jsonFile, json_encode($products, JSON_PRETTY_PRINT));

        return $products[$productIndex];
    }

    public function getTotalSum()
    {
        $products = $this->getAllProducts();
        return array_sum(array_column($products, 'total_value'));
    }
}