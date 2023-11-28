<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sale;
use App\Models\product;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    public function getSales()
    {
        $sales = Sale::with('product')->get(); // Asumiendo que tienes una relación llamada "product" en tu modelo Sale
        return response()->json($sales, 200);
    
    }


    public function newSale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'total' => 'required',
            'profit' => 'required',
            'hour' => 'required',
            'product_id' => 'required|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        // Obtén el producto y su stock
        $product = Product::findOrFail($request->input('product_id'));
        $cantidad = $product->stock;

        // Verifica si hay suficiente stock para realizar la venta
        if ($cantidad >= $request->input('amount')) {
            // Crea la venta
            $sale = Sale::create($validator->validated());

            // Actualiza el stock del producto
            $nuevoStock = $cantidad - $request->input('amount');
            $product->update(['stock' => $nuevoStock]);

            // Agrega el nombre del producto a la respuesta
            $productName = $product->name;

            return response()->json([
                'sale' => $sale,
                'product_name' => $productName,
                'message' => 'Venta creada con éxito'
            ], 201);
        } else {
            return response()->json(['message' => 'No hay suficiente stock'], 400);
        }
    }
}