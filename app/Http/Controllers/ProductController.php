<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where('name', 'ilike', "%{$request->search}%")
                ->orWhere('category', 'ilike', "%{$request->search}%");
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('name')->paginate(10);
        return response()->json(['data' => $products]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'category'    => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($request->only('name', 'category', 'description'));
        return response()->json(['message' => 'Produk berhasil ditambahkan', 'data' => $product], 201);
    }

    public function show(Product $product)
    {
        return response()->json(['data' => $product]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'category'    => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $product->update($request->only('name', 'category', 'description'));
        return response()->json(['message' => 'Produk berhasil diupdate', 'data' => $product]);
    }

    public function destroy(Product $product)
    {
        if ($product->transactionDetails()->exists()) {
            return response()->json(['message' => 'Produk tidak bisa dihapus karena sudah digunakan di transaksi'], 422);
        }

        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    public function all()
    {
        $products = Product::select('id', 'name', 'category')->orderBy('name')->get();
        return response()->json(['data' => $products]);
    }

    public function categories()
    {
        $categories = Product::select('category')->distinct()->orderBy('category')->pluck('category');
        return response()->json(['data' => $categories]);
    }
}
