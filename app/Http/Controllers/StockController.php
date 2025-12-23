<?php

namespace App\Http\Controllers;

use App\Stock;
use App\Product;
use App\Supplier;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::with(['product','supplier']);

        if ($request->search) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->orWhereHas('supplier', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $stocks = $query->paginate(10);
        $products = Product::all();
        $suppliers = Supplier::all();

        return view('stock.index', compact('stocks','products','suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'supplier_id' => 'required',
            'qty' => 'required|integer|min:0'
        ]);

        Stock::create($request->all());

        return back()->with('success','Stock added successfully');
    }

    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        $stock->update($request->only('qty'));

        return back()->with('success','Stock updated');
    }

    public function destroy($id)
    {
        Stock::findOrFail($id)->delete();
        return back()->with('success','Stock deleted');
    }
}
