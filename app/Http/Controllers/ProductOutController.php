<?php

namespace App\Http\Controllers;

use App\Category;
use App\Customer;
use App\Supplier;
use App\Stock;
use App\Exports\ExportProductOut;
use App\Product;
use App\Product_Out;
use App\Company;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use PDF;


class ProductOutController extends Controller
{

    
    public function __construct()
    {
        $this->middleware('role:admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::orderBy('name','ASC')
            ->get()
            ->pluck('name','id');

        $supplier = Supplier::orderBy('name','ASC')
            ->get()
            ->pluck('name','id');

        $invoice_data = Product_Out::all();
        return view('product_out.index', compact('products','supplier', 'invoice_data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|integer',
            'supplier_id' => 'required|integer',
            'qty'         => 'required|integer|min:1',
            'date'        => 'required',
            'image'       => 'required|image|mimes:jpg,jpeg,png|max:2048' // <- required now
        ]);

        // Find stock by product + supplier
        $stock = Stock::where('product_id', $request->product_id)
                    ->where('supplier_id', $request->supplier_id)
                    ->first();

        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock not found for this product and location'
            ], 422);
        }

        if ($stock->qty < $request->qty) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available'
            ], 422);
        }

        $imageName = time().'_'.str_replace(' ', '_', $request->image->getClientOriginalName());
        $request->image->move(public_path('uploads'), $imageName);
        $imagePath = 'uploads/'.$imageName;

        $productOut = Product_Out::create([
            'product_id'  => $request->product_id,
            'supplier_id' => $request->supplier_id,
            'qty'         => $request->qty,
            'date'        => $request->date,
            'image'       => $imagePath
        ]);

        // Reduce stock qty
        $stock->qty -= $request->qty;
        $stock->save();

        // Reduce product total qty
        $product = Product::find($request->product_id);
        $product->qty -= $request->qty;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product Out saved and stock updated'
        ]);
    }




    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $Product_Out = Product_Out::find($id);
        return $Product_Out;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id'  => 'required',
            'supplier_id' => 'required',
            'qty'         => 'required|numeric|min:1',
            'date'        => 'required',
            'image'       => 'required|image|mimes:jpg,jpeg,png|max:2048' // <- required now
        ]);

        $productOut = Product_Out::findOrFail($id);

        // Handle image upload
        $imageName = time().'_'.$request->image->getClientOriginalName();
        $request->image->move(public_path('uploads'), $imageName);
        $productOut->image = 'uploads/'.$imageName;

        // Calculate qty difference for stock and product update
        $qtyDifference = $request->qty - $productOut->qty;

        $stock = Stock::where('product_id', $request->product_id)
                    ->where('supplier_id', $request->supplier_id)
                    ->first();

        if (!$stock || ($stock->qty < $qtyDifference && $qtyDifference > 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock'
            ], 422);
        }

        // Update product_out
        $productOut->product_id  = $request->product_id;
        $productOut->supplier_id = $request->supplier_id;
        $productOut->qty         = $request->qty;
        $productOut->date        = $request->date;
        $productOut->save();

        // Update stock qty
        $stock->qty -= $qtyDifference;
        $stock->save();

        // Update product qty
        $product = Product::findOrFail($request->product_id);
        $product->qty -= $qtyDifference;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product Out Updated'
        ]);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $productOut = Product_Out::findOrFail($id);

        // Restore stock qty
        $stock = Stock::where('product_id', $productOut->product_id)
                    ->where('supplier_id', $productOut->supplier_id)
                    ->first();

        if ($stock) {
            $stock->qty += $productOut->qty;
            $stock->save();
        }

        // Restore product qty
        $product = Product::findOrFail($productOut->product_id);
        $product->qty += $productOut->qty;
        $product->save();

        $productOut->delete();

        return response()->json([
            'success' => true,
            'message' => 'Products Out Deleted'
        ]);
    }




    public function apiProductsOut()
{
    $productsOut = Product_Out::with(['product', 'supplier'])->get();

    return DataTables::of($productsOut)
        ->addColumn('products_name', function ($row) {
            return optional($row->product)->name ?? '-';
        })
        ->addColumn('supplier_name', function ($row) {
            return optional($row->supplier)->name ?? '-';
        })
        ->addColumn('multiple_export', function ($row) {
            return '<input type="checkbox" name="exportpdf[]" value="'.$row->id.'">';
        })
        ->addColumn('action', function ($row) {
            return '
                <button onclick="editForm('.$row->id.')" class="btn btn-primary btn-xs">Edit</button>
                <button onclick="deleteData('.$row->id.')" class="btn btn-danger btn-xs">Delete</button>
            ';
        })
        ->addColumn('image', function ($productsOut) {
            if ($productsOut->image) {
                return '<img src="'.asset($productsOut->image).'" width="60" class="img-thumbnail">';
            }
            return '-';
        })
        ->rawColumns(['multiple_export','action','image'])
        ->make(true);
}


    public function exportProductOutAll()
    {
        $Product_Out = Product_Out::all();
        $pdf = PDF::loadView('product_out.productOutAllPDF',compact('Product_Out'));
        return $pdf->download('product_out.pdf');
    }

    public function exportProductOut(Request $request)
    {
        $idst = explode(",",$request->exportpdf);
        $Product_Out = Product_Out::find($idst);
        $companyInfo = Company::find(1);

        $pdf = PDF::setOptions([
            'images' => true,
            'isHtml5ParserEnabled' => true, 
            'isRemoteEnabled' => true
        ])->loadView('product_out.productOutPDF', compact('Product_Out', 'companyInfo'))->setPaper('a4', 'portrait')->stream();
        return $pdf->download(date("Y-m-d H:i:s",time()).'_Product_Out.pdf');
    }

    public function exportExcel()
    {
        return (new ExportProductOut)->download('product_out.xlsx');
    }

    public function checkAvailable(Request $request)
    {
        $product_id  = $request->product_id;
        $supplier_id = $request->supplier_id;

        $stock = Stock::where('product_id', $product_id)
                    ->where('supplier_id', $supplier_id)
                    ->first();

        return response()->json([
            'qty'      => $stock ? $stock->qty : 0,
            'product'  => optional($stock->product)->name ?? '',
            'location' => optional($stock->supplier)->name ?? ''
        ]);
    }
}
