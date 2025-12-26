<?php

namespace App\Http\Controllers;


use App\Exports\ExportSuppliers;
use App\Imports\SuppliersImport;
use App\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Excel;
use PDF;
use App\Barcode;


class SupplierController extends Controller
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
        $suppliers = Supplier::all();
        return view('suppliers.index');
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
   $this->validate($request, [
        'name'      => 'required',
        'address'   => 'required',
        'barcode'       => 'required',
    ]);

    $input = $request->all();

        $barcode = new Barcode;
        $barcode->name = $input['barcode'];
        $barcode->save();
        

        $input['image'] = null;

        if ($request->hasFile('image')){
            $input['image'] = '/upload/location/'.str_slug($input['name'], '-').'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('/upload/location/'), $input['image']);
        }

        $input['barcode_id'] = $barcode->id;
        Supplier::create($input);

        return response()->json([
            'success' => true,
            'message' => 'Location Created'
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
        $supplier = Supplier::find($id);
        //$supplier['barcode'] = $suppliers->barcode->name;
        return $supplier;
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
        $supplier = Supplier::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|min:2',
            'address' => 'required|string|min:2',
            'barcode'       => 'required',
        ]);

        $input = $request->all();
        $produk = Supplier::findOrFail($id);

        $input['image'] = $produk->image;

        if ($request->hasFile('image')){
            if (!$produk->image == NULL){
                unlink(public_path($produk->image));
            }
            $input['image'] = '/upload/location/'.str_slug($input['name'], '-').'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('/upload/location/'), $input['image']);
        }

        $barcode = Barcode::findOrFail($produk->barcode_id);
        $barcode->name = $input['barcode'];
        $barcode->save();

        $produk->update($input);

        return response()->json([
            'success' => true,
            'message' => 'Supplier Updated'
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
        
        $suppliers = Supplier::findOrFail($id);

        if (!$suppliers->image == NULL){
            unlink(public_path($suppliers->image));
        }


        Supplier::destroy($id);

        return response()->json([
            'success'    => true,
            'message'    => 'Supplier Delete'
        ]);
    }

    public function apiSuppliers()
    {
        $suppliers = Supplier::all();

        return Datatables::of($suppliers)
            ->addColumn('action', function($suppliers){
                return '<a onclick="editForm('. $suppliers->id .')" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a> ' .
                    '<a onclick="deleteData('. $suppliers->id .')" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i> Delete</a>';
            })
            ->addColumn('barcode_name', function ($suppliers){
                return $suppliers->barcode->name;
            })
            ->addColumn('barcode_image', function ($suppliers){
                return '<a href="https://barcode.tec-it.com/barcode.ashx?data='.$suppliers->barcode->name.'&code=EAN13&dpi=96&imagetype=Png&download=true" style="margin: 0 auto;display: block;text-align:center;" title="Download Barcode" target="_blank" download><img class="img-responsive img-thumbnail" src="https://barcode.tec-it.com/barcode.ashx?data='.$suppliers->barcode->name.'&code=EAN13&dpi=96"><br>Download</a>';
            })
            ->addColumn('show_photo', function($suppliers){
                if ($suppliers->image == NULL){
                    return 'No Image';
                }
                return '<img class="rounded-square" width="100" src="'. url($suppliers->image) .'" alt="">';
            })
            ->addColumn('action', function($suppliers){
                return '<a onclick="editForm('. $suppliers->id .')" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a> ' .
                    '<a onclick="deleteData('. $suppliers->id .')" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i> Delete</a>';
            })
            ->rawColumns(['barcode','barcode_image','category_name','show_photo','action'])->make(true);
    }

    public function ImportExcel(Request $request)
    {
        if ($request->hasFile('file')) {
            //Validasi
            $this->validate($request, [
                'file' => 'required',
                'extension' => 'mimes:xls,xlsx|max:10240',
            ]);
            if ($request->file('file')->isValid()) {
                //UPLOAD FILE
                $file = $request->file('file'); //GET FILE
                Excel::import(new SuppliersImport, $file); //IMPORT FILE
                return redirect()->back()->with(['success' => 'Upload file data suppliers !']);
            }
        }

        return redirect()->back()->with(['error' => 'Please choose file before!']);
    }

    public function exportSuppliersAll()
    {
        $suppliers = Supplier::all();
        $pdf = PDF::loadView('suppliers.SuppliersAllPDF',compact('suppliers'));
        return $pdf->download('suppliers.pdf');
    }

    public function exportExcel()
    {
        return (new ExportSuppliers)->download('suppliers.xlsx');
    }
    
}
