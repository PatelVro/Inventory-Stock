<?php

namespace App\Http\Controllers;

use App\Barcode;
use App\Supplier;
use App\Stock;
use App\Product_Out;
use App\Product_In;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:staff,admin');
    }

    // Show transfer page
    public function index()
    {
        return view('transfer.index');
    }

    // Get supplier by barcode
    public function getSupplierByBarcode(Request $request)
    {
        $request->validate(['barcode' => 'required']);

        $barcode = Barcode::where('name', $request->barcode)->first();

        if (!$barcode) {
            return response()->json(['error' => 'Barcode not found'], 404);
        }

        $supplier = $barcode->supplier;

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        return response()->json($supplier);
    }

    // Get products for supplier
    public function getSupplierProducts(Request $request)
    {
        $stocks = Stock::with('product')
            ->where('supplier_id', $request->supplier_id)
            ->where('qty', '>', 0)
            ->get();

        return response()->json($stocks);
    }

    // Submit transfer
    public function transfer(Request $request)
    {
        $request->validate([
            'from_supplier_id' => 'required',
            'to_supplier_id'   => 'required|different:from_supplier_id',
            'products'         => 'required|json',
            'images.*'         => 'nullable|image|max:2048',
        ]);

        $products = json_decode($request->products, true);

        DB::transaction(function () use ($request, $products) {

            foreach ($products as $index => $item) {

                $fromStock = Stock::where('supplier_id', $request->from_supplier_id)
                    ->where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($fromStock->qty < $item['qty']) {
                    abort(422, 'Not enough stock for product ID ' . $item['product_id']);
                }

                // ✅ Default image paths
                $outImagePath = null;
                $inImagePath  = null;

                if ($request->hasFile("images.$index")) {
                    $image     = $request->file("images.$index");
                    $fileName  = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                    // 📂 Product Out image
                    $outDir = public_path('uploads/product_out');
                    if (!file_exists($outDir)) {
                        mkdir($outDir, 0755, true);
                    }
                    $image->move($outDir, $fileName);
                    $outImagePath = 'uploads/product_out/' . $fileName;

                    // 📂 Product In image (copy)
                    $inDir = public_path('uploads/product_in');
                    if (!file_exists($inDir)) {
                        mkdir($inDir, 0755, true);
                    }
                    copy(
                        public_path($outImagePath),
                        public_path('uploads/product_in/' . $fileName)
                    );
                    $inImagePath = 'uploads/product_in/' . $fileName;
                }

                // 🔻 Product Out
                Product_Out::create([
                    'product_id'  => $item['product_id'],
                    'supplier_id' => $request->from_supplier_id,
                    'qty'         => $item['qty'],
                    'date'        => now(),
                    'image'       => $outImagePath,
                ]);

                $fromStock->decrement('qty', $item['qty']);

                // 🔺 Product In
                $toStock = Stock::firstOrCreate(
                    [
                        'supplier_id' => $request->to_supplier_id,
                        'product_id'  => $item['product_id'],
                    ],
                    ['qty' => 0]
                );

                Product_In::create([
                    'product_id'  => $item['product_id'],
                    'supplier_id' => $request->to_supplier_id,
                    'qty'         => $item['qty'],
                    'date'        => now(),
                    'image'       => $inImagePath,
                ]);

                $toStock->increment('qty', $item['qty']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Transfer completed successfully',
        ]);
    }


}
