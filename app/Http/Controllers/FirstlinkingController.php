<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Product;
use App\Product_In;
use App\Stock;
use App\Barcode;
use Illuminate\Support\Facades\DB;

class FirstlinkingController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:staff,admin');
    }
    public function index()
    {
        $categories = Category::all();
        return view('firstlinking.index', compact('categories'));
    }

    // Get products by category
    public function productsByCategory(Request $request)
    {
        return Product::where('category_id', $request->category_id)->get();
    }

    // Get supplier/location by scanned barcode
    public function supplierByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required'
        ]);

        // 1️⃣ Find barcode by scanned number
        $barcode = Barcode::where('name', $request->barcode)->first();

        if (!$barcode) {
            return response()->json(['error' => 'Barcode not found'], 404);
        }

        // 2️⃣ Get supplier linked to this barcode
        $supplier = $barcode->supplier; // <-- THIS LINE YOU ASKED ABOUT

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        return response()->json($supplier);
    }

    // Submit firstlinking
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'supplier_barcode' => 'required',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|integer|min:1'
        ]);

        $barcode = Barcode::where('name', $validated['supplier_barcode'])->first();

        if (!$barcode || !$barcode->supplier) {
            return response()->json(['message' => 'Scan location first'], 400);
        }

        $supplierId = $barcode->supplier->id;

        DB::transaction(function () use ($supplierId, $request) {
            foreach ($request->products as $item) {
                $stock = Stock::firstOrCreate(
                    ['supplier_id' => $supplierId, 'product_id' => $item['product_id']],
                    ['qty' => 0]
                );
                $stock->qty += $item['qty'];
                $stock->save();

                Product_In::create([
                    'product_id'  => $item['product_id'],
                    'supplier_id' => $supplierId,
                    'qty'         => $item['qty'],
                    'date'        => now()
                ]);
            }
        });

        return response()->json(['message' => 'Products linked successfully']);
    }

}
