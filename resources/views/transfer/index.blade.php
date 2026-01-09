@extends('layouts.master')

@section('title', 'Transfer')

@section('top')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">

<style>
.btntr { margin:5px 0; padding:5px 15px; border:2px solid #4d4d4d; color:#fff; background:#414141; }
button.scanto.btntr, button.scanf.btntr { background:#3e8eba; border:0; width:100%; }
button.clear.btntr { background:transparent; color:#125981; border:0; }
.getlisting { background:#fff; padding:20px 10px; border:1px solid #ddd; margin:10px 0 20px; }
.confirm-transfer button { background:#01670f; color:#fff; width:100%; padding:10px; font-size:17px; }
table#productTable th, table#productTable td { padding:5px; }
.transfer-image { margin:15px 0; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
$.ajaxSetup({
    headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});
</script>
@endsection

@section('content')
<div class="container">
    <h3>Product Transfer</h3>

    {{-- FROM --}}
    <div class="startscan">
        <div id="reader" style="width:100%; max-width:400px;"></div>
        <button onclick="startScanner('from_barcode')" class="scanf btntr">Scan From</button>
        <input type="text" id="from_barcode" placeholder="Scan FROM supplier barcode">
        <button onclick="loadSource()" class="btntr">Load Source</button>
        <button onclick="$('#from_barcode').val('')" class="clear btntr">Clear</button>
    </div>

    {{-- PRODUCT LIST --}}
    <div class="getlisting">
        <table border="1" width="100%" id="productTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Available</th>
                    <th>Qty</th>
                    <th>-</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" onclick="addRow()">➕ Add Product</button>
    </div>

    {{-- ONE IMAGE PER TRANSFER --}}
    <div class="transfer-image">
        <label><strong>Transfer Image</strong></label>
        <input type="file" id="transfer_image" accept="image/*" capture="environment">
    </div>

    {{-- TO --}}
    <div class="transferlisting">
        <div id="reader2" style="width:100%; max-width:400px;"></div>
        <button onclick="startScanner('to_barcode')" class="scanto btntr">Scan To</button>
        <input type="text" id="to_barcode" placeholder="Scan TO supplier barcode">
        <button onclick="$('#to_barcode').val('')" class="clear btntr">Clear</button>
    </div>

    <div class="confirm-transfer">
        <button onclick="submitTransfer()">Transfer</button>
    </div>
</div>
@endsection

@section('bot')
<script>
let fromSupplier = null;
let supplierStocks = [];

// Load source supplier & products
function loadSource() {
    $.get('/transfer/supplier', { barcode: $('#from_barcode').val() }, function(s) {
        fromSupplier = s.id;
        $.get('/transfer/products', { supplier_id: s.id }, function(stocks) {
            supplierStocks = stocks;
            $('#productTable tbody').empty();
            addRow();
        });
    }).fail(() => alert('Source supplier not found'));
}

// Add product row (NO IMAGE HERE)
function addRow() {
    let options = supplierStocks.map(st =>
        `<option value="${st.product_id}" data-qty="${st.qty}">${st.product.name}</option>`
    ).join('');

    $('#productTable tbody').append(`
        <tr>
            <td>
                <select class="product_select" onchange="updateAvailable(this)">
                    <option value="">Select product</option>${options}
                </select>
            </td>
            <td class="available">-</td>
            <td><input type="number" class="qty_input" min="1"></td>
            <td><button onclick="removeRow(this)">❌</button></td>
        </tr>
    `);
}

function updateAvailable(select) {
    let qty = $('option:selected', select).data('qty') || 0;
    let row = $(select).closest('tr');
    row.find('.available').text(qty);
    row.find('.qty_input').on('input', function(){
        if (this.value > qty) this.value = qty;
    });
}

function removeRow(btn) {
    $(btn).closest('tr').remove();
}

// QR Scanner
function startScanner(target) {
    let reader = target === 'from_barcode' ? 'reader' : 'reader2';
    let qr = new Html5Qrcode(reader);

    Html5Qrcode.getCameras().then(cameras => {
        let cam = cameras.find(c => /rear|back|environment/i.test(c.label)) || cameras[0];
        qr.start(cam.id, { fps:10, qrbox:250 }, txt => {
            $('#' + target).val(txt);
            qr.stop();
        });
    });
}

// SUBMIT (ONE IMAGE)
function submitTransfer() {
    if (!fromSupplier || !$('#to_barcode').val()) {
        alert('Scan both barcodes');
        return;
    }

    let products = [];
    let formData = new FormData();
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    formData.append('from_supplier_id', fromSupplier);

    $('#productTable tbody tr').each(function(){
        let pid = $(this).find('.product_select').val();
        let qty = $(this).find('.qty_input').val();
        if(pid && qty > 0) products.push({ product_id: pid, qty });
    });

    if (!products.length) {
        alert('Add products');
        return;
    }

    $.get('/transfer/supplier', { barcode: $('#to_barcode').val() }, function(s){
        formData.append('to_supplier_id', s.id);
        formData.append('products', JSON.stringify(products));

        let image = $('#transfer_image')[0].files[0];
        if (image) formData.append('image', image);

        $.ajax({
            url:'/transfer/submit',
            type:'POST',
            data:formData,
            contentType:false,
            processData:false,
            success:res => { alert(res.message); location.reload(); },
            error:err => alert(err.responseJSON?.message || 'Transfer failed')
        });
    }).fail(() => alert('Destination supplier not found'));
}
</script>
@endsection
