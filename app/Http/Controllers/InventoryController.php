<?php

namespace App\Http\Controllers;

class InventoryController extends Controller
{
    public function index()
    {
        return view('inventory.index');
    }

    public function productIn()
    {
        return view('inventory.product-in');
    }

    public function productOut()
    {
        return view('inventory.product-out');
    }

    public function addProduct()
    {
        return view('inventory.add-product');
    }

    public function settings()
    {
        return view('inventory.settings');
    }
}
