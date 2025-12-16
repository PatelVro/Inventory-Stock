<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/inventory.css') }}">

@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Inventory</h1>

    <div class="inventory-buttons">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <div class="bt bt1">
                        <a href="{{ route('inventory.product.in') }}" class="inventory-btn">
                            <i class="fa-solid fa-arrow-down"></i>
                            <span>Product In</span>
                        </a>
                    </div>
                    
                </div>

                <div class="col-6">
                    <div class="bt bt2">
                        <a href="{{ route('inventory.product.out') }}" class="inventory-btn">
                            <i class="fa-solid fa-arrow-up"></i>
                            <span>Product Out</span>
                        </a>
                    </div>

                </div>
                <div class="col-6">
                    <div class="bt bt3">
                        <a href="{{ route('inventory.add.product') }}" class="inventory-btn">
                            <i class="fa-solid fa-plus"></i>
                            <span>Add Product</span>
                        </a>
                    </div>
                </div>
                <div class="col-6">
                    <div class="bt bt4">
                        <a href="{{ route('inventory.settings') }}" class="inventory-btn">
                            <i class="fa-solid fa-gear"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>    
    </div>
</div>
@endsection
