@extends('layouts.master')

@section('title', 'Firstlinking')

@section('top')
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .btntr { margin: 5px 0; padding: 6px 10px; background:#3e8eba; color:#fff; border:0; width:100%; }
    input, select { width:100%; padding:8px; margin-bottom:5px; }

    button {
    color: #fff;
    background: #00283f;
    border: 0;
    margin: 5px 0;
    padding: 10px;
    display: block;
    width: 100%;
    }

    button.submit {
        background: #004b00;
    }

    button.cancel {
        background: none;
        width: auto;
    }

</style>
@endsection

@section('header', 'Firstlinking')
@section('description', 'Link products to a supplier/location using barcode.')

@section('breadcrumb')
<ol class="breadcrumb">
    <li><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Firstlinking</li>
</ol>
@endsection

@section('content')
<div class="container">
    <h3>Firstlinking</h3>
    <form method="POST" action="javascript:void(0)">
        @csrf
        <div>
            <label>Step 1: Select Category</label>
            <select id="category_id">
                <option value="">Select Category</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Step 2: Select Product</label>
            
            <select id="product_id">
                <!-- <option value="">Select Product</option> -->
            </select>
        </div>

        <div>
            <label>Step 3: Quantity</label>
            <input type="number" id="qty" min="1">
        </div>

        <div>
            <button type="button" onclick="addProduct()">➕ Add Product</button>
        </div>

        <table border="1" width="100%" id="productTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        
        <div>
            <label>Step 4: Scan Barcode</label>
            <div id="reader" style="width:100%; max-width:400px;"></div>
            <input type="text" id="supplier_barcode" placeholder="Scan supplier/location barcode">
            <button type="button" onclick="startScanner()" >Scan Barcode</button>
        </div>

        
        <div>
            <label>Step 5: Take Photo</label>
            <input
                type="file"
                id="image"
                name="image"
                accept="image/*"
                capture="environment"
            >
        </div>
        <div>
            <button type="button" onclick="submitFirstlinking()" class="submit">Submit</button>
        </div>
    </form>
</div>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


let productSelect = null;

function initProductSelect() {
    if (productSelect) {
        productSelect.destroy();
    }

    productSelect = new TomSelect('#product_id', {
        placeholder: 'Search product...',
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        maxItems: 1,        // ✅ THIS enforces single select
        closeAfterSelect: true,
        hideSelected: true,
        options: [],          // IMPORTANT
        create: false
    });
}

$(document).ready(function () {
    initProductSelect();
});

$('#category_id').on('change', function () {
    let catId = $(this).val();
    if (!catId) return;

    if (!productSelect) {
        console.error('Tom Select not initialized');
        return;
    }

    $.get("{{ route('firstlinking.products') }}", { category_id: catId }, function (data) {

        // CLEAR previous products
        productSelect.clear(true);
        productSelect.clearOptions();

        // ADD new products
        productSelect.addOptions(data);

        // REFRESH dropdown
        productSelect.refreshOptions(false);
    });
});

function addProduct() {
    let pid = $('#product_id').val();
    let pname = $('#product_id option:selected').text();
    let qty = $('#qty').val();

    if(!pid || !qty || qty <= 0) { alert('Select product and enter quantity'); return; }

    products.push({ product_id: pid, qty: parseInt(qty) });

    let row = `<tr>
        <td>${pname}</td>
        <td>${qty}</td>
        <td><button type="button" class="cancel" onclick="removeRow(this, ${pid})">❌</button></td>
    </tr>`;
    $('#productTable tbody').append(row);

    $('#product_id').val(''); $('#qty').val('');
}

function removeRow(btn, pid) {
    $(btn).closest('tr').remove();
    products = products.filter(p => p.product_id != pid);
}

// Barcode scanner
let html5QrCode;
function startScanner() {
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: 250 };

    // Try to open back camera
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        decodedText => {
            $('#supplier_barcode').val(decodedText);
            html5QrCode.stop();
        },
        error => {}
    ).catch(err => {
        alert("Unable to access back camera: " + err);
    });
}


function submitFirstlinking() {
    let barcode = $('#supplier_barcode').val();

    if (!barcode) {
        alert('Scan location first');
        return;
    }

    if (products.length === 0) {
        alert('Add at least one product');
        return;
    }

    let formData = new FormData();
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    formData.append('supplier_barcode', barcode);
    formData.append('products', JSON.stringify(products));

    let imageFile = document.getElementById('image').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }

    $.ajax({
        url: "{{ route('firstlinking.submit') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function (res) {
            alert(res.message);
            location.reload();
        },
        error: function (err) {
            alert(err.responseJSON?.message || 'Something went wrong');
        }
    });
}



</script>
@endsection