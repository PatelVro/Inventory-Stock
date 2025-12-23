@extends('layouts.master')

@section('title') Stock @endsection

@section('top')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">

    <!-- daterange picker -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
    <!-- bootstrap datepicker -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('header') Stock @endsection
@section('description') This page about your all products out @endsection

@section('top')
@endsection

@section('breadcrumb')
<ol class="breadcrumb">
    <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active"> Stock</li>
</ol>
@endsection

@section('content')
    
   
<div class="box">
<h3>Stock List</h3>

{{-- Search --}}
<form method="GET" class="mb-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product or location">
    <button class="btn btn-primary btn-sm">Search</button>
</form>

{{-- Add Stock --}}
<form method="POST" action="{{ route('stock.store') }}" class="mb-4">
    @csrf
    <select name="product_id" required>
        <option value="">Select Product</option>
        @foreach($products as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
        @endforeach
    </select>

    <select name="supplier_id" required>
        <option value="">Select Location</option>
        @foreach($suppliers as $s)
            <option value="{{ $s->id }}">{{ $s->name }}</option>
        @endforeach
    </select>

    <input type="number" name="qty" placeholder="Qty" required>
    <button class="btn btn-success btn-sm">Add</button>
</form>

{{-- Table --}}

<div class="box-body">
<table class="table  table-striped">
<thead>
<tr>
    <th>#</th>
    <th>Product</th>
    <th>Location</th>
    <th>Quantity</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
    
@foreach($stocks as $stock)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $stock->product->name }}</td>
    <td>{{ $stock->supplier->name }}</td>
    <td>
        <form method="POST" action="{{ route('stock.update',$stock->id) }}">
            @csrf
            @method('PUT')
            <input type="number" name="qty" value="{{ $stock->qty }}" style="width:80px">
            <button class="btn btn-warning btn-sm">Update</button>
        </form>
    </td>
    <td>
        <form method="POST" action="{{ route('stock.destroy',$stock->id) }}">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete stock?')">Delete</button>
        </form>
    </td>
</tr>
@endforeach
</tbody>
</table>
</div>
{{ $stocks->links() }}

</div>


@endsection

@section('bot')

    <!-- DataTables -->
    <script src=" {{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }} "></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }} "></script>


    <!-- InputMask -->
    <script src="{{ asset('assets/plugins/input-mask/jquery.inputmask.js') }}"></script>
    <script src="{{ asset('assets/plugins/input-mask/jquery.inputmask.date.extensions.js') }}"></script>
    <script src="{{ asset('assets/plugins/input-mask/jquery.inputmask.extensions.js') }}"></script>
    <!-- date-range-picker -->
    <script src="{{ asset('assets/bower_components/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <!-- bootstrap datepicker -->
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <!-- bootstrap color picker -->
    <script src="{{ asset('assets/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js') }}"></script>
    <!-- bootstrap time picker -->
    <script src="{{ asset('assets/plugins/timepicker/bootstrap-timepicker.min.js') }}"></script>
    {{-- Validator --}}
    <script src="{{ asset('assets/validator/validator.min.js') }}"></script>


@endsection
