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
        $this->middleware('role:staff');
    }

    public function index()
    {
        return view('transfer.index');
    }

    // Step 1 & 4: Get supplier by barcode
    public function getSupplierByBarcode(Request $request)
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


    

    // Step 2: Get products at supplier
    public function getSupplierProducts(Request $request)
    {
        $stocks = Stock::with('product')
            ->where('supplier_id', $request->supplier_id)
            ->where('qty', '>', 0)
            ->get();

        return response()->json($stocks);
    }



    // Step 3 & 4: Transfer logic
    public function transfer(Request $request)
    {
        $request->validate([
            'from_supplier_id' => 'required',
            'to_supplier_id'   => 'required|different:from_supplier_id',
            'product_id'       => 'required',
            'qty'              => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($request) {

            // SOURCE STOCK
            $fromStock = Stock::where('supplier_id', $request->from_supplier_id)
                ->where('product_id', $request->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($fromStock->qty < $request->qty) {
                abort(422, 'Not enough stock at source');
            }

            // PRODUCT OUT
            Product_Out::create([
                'product_id'  => $request->product_id,
                'supplier_id' => $request->from_supplier_id,
                'qty'         => $request->qty,
                'date'        => now()
            ]);

            $fromStock->decrement('qty', $request->qty);

            // DESTINATION STOCK
            $toStock = Stock::firstOrCreate(
                [
                    'supplier_id' => $request->to_supplier_id,
                    'product_id'  => $request->product_id
                ],
                ['qty' => 0]
            );

            // PRODUCT IN
            Product_In::create([
                'product_id'  => $request->product_id,
                'supplier_id' => $request->to_supplier_id,
                'qty'         => $request->qty,
                'date'        => now()
            ]);

            $toStock->increment('qty', $request->qty);
        });

        return response()->json([
            'success' => true,
            'message' => 'Transfer completed successfully'
        ]);
    }
}
