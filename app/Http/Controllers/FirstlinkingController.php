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
        // Basic validation
        $request->validate([
            'supplier_barcode' => 'required',
            'products' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Decode products JSON
        $products = json_decode($request->products, true);

        if (!is_array($products) || count($products) === 0) {
            return response()->json(['message' => 'Invalid product data'], 422);
        }

        // Validate each product
        foreach ($products as $item) {
            if (
                !isset($item['product_id']) ||
                !isset($item['qty']) ||
                !is_numeric($item['qty']) ||
                $item['qty'] <= 0
            ) {
                return response()->json(['message' => 'Invalid product entry'], 422);
            }
        }

        // Find barcode + supplier
        $barcode = Barcode::where('name', $request->supplier_barcode)->first();

        if (!$barcode || !$barcode->supplier) {
            return response()->json(['message' => 'Scan location first'], 400);
        }

        $supplierId = $barcode->supplier->id;

        // Save image once
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imageName = time().'_'.$request->image->getClientOriginalName();
            $request->image->move(public_path('uploads/product_in'), $imageName);
            $imagePath = 'uploads/product_in/'.$imageName;
        }

        // DB transaction
        DB::transaction(function () use ($supplierId, $products, $imagePath) {

            foreach ($products as $item) {

                $stock = Stock::firstOrCreate(
                    [
                        'supplier_id' => $supplierId,
                        'product_id'  => $item['product_id']
                    ],
                    ['qty' => 0]
                );

                $stock->qty += $item['qty'];
                $stock->save();

                Product_In::create([
                    'product_id'  => $item['product_id'],
                    'supplier_id' => $supplierId,
                    'qty'         => $item['qty'],
                    'date'        => now(),
                    'image'       => $imagePath
                ]);
            }
        });

        return response()->json(['message' => 'Products linked successfully']);
    }


}
