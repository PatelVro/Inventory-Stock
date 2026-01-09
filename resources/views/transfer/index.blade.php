@extends('layouts.master')

@section('title', 'Transfer')

@section('top')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">

<style>
/* Table & buttons styles */
.btntr { margin:5px 0; padding:5px 15px; border:2px solid #4d4d4d; color:#fff; background:#414141; }
button.scanto.btntr, button.scanf.btntr { background:#3e8eba; border:0; color:#fff; width:100%; }
button.clear.btntr { background:transparent; color:#125981; border:0; }
.getlisting { background:#fff; padding:20px 10px; border:1px solid #ddd; margin:10px 0 20px; }
.confirm-transfer button { background:#01670f; color:#fff; width:100%; padding:8px; border:0; font-size:17px; font-weight:500; text-transform:uppercase; }
table#productTable th, table#productTable td { padding:5px; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
$.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
</script>
@endsection

@section('content')
<div class="container">
    <h3>Product Transfer</h3>

    <div class="startscan">
        <div id="reader" style="width:100%; max-width:400px;"></div>
        <button onclick="startScanner('from_barcode')" class="scanf btntr">Scan From</button>
        <input type="text" id="from_barcode" placeholder="Scan FROM supplier barcode">
        <button type="button" onclick="loadSource()" class="loads btntr">Load Source</button>
        <button onclick="document.getElementById('from_barcode').value=''" class="clear btntr">Clear Barcode</button>
    </div>

    <div class="getlisting">
        <table border="1" width="100%" id="productTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Available</th>
                    <th>Qty</th>
                    <th>Image</th>
                    <th>-</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" onclick="addRow()" class="addpro">➕ Add Product</button>
    </div>

    <div class="transferlisting">
        <div id="reader2" style="width:100%; max-width:400px;"></div>
        <button onclick="startScanner('to_barcode')" class="scanto btntr">Scan To</button>
        <input type="text" id="to_barcode" placeholder="Scan TO supplier barcode">
        <button onclick="document.getElementById('to_barcode').value=''" class="clear btntr">Clear</button>
    </div>

    <div class="confirm-transfer">
        <button type="button" onclick="submitTransfer()">Transfer</button>
    </div>
</div>
@endsection

@section('bot')
<script>
let fromSupplier = null;
let toSupplier = null;
let supplierStocks = [];

// Load products from source supplier
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

// Add product row
function addRow() {
    let options = supplierStocks.map(st =>
        `<option value="${st.product_id}" data-qty="${st.qty}">${st.product.name}</option>`
    ).join('');

    let row = `
    <tr>
        <td><select class="product_select" onchange="updateAvailable(this)"><option value="">Select product</option>${options}</select></td>
        <td class="available">-</td>
        <td><input type="number" class="qty_input" min="1"></td>
        <td><input type="file" class="image_input" accept="image/*"></td>
        <td><button type="button" onclick="removeRow(this)">❌</button></td>
    </tr>`;
    $('#productTable tbody').append(row);
}

function updateAvailable(select) {
    let available = $('option:selected', select).data('qty') || 0;
    let row = $(select).closest('tr');
    row.find('.available').text(available);
    row.find('.qty_input').off().on('input', function() {
        if (parseInt(this.value) > available) this.value = available;
    });
}

function removeRow(btn) { $(btn).closest('tr').remove(); }

// QR Scanner
function startScanner(targetId) {
    let html5QrCode = new Html5Qrcode(targetId === 'from_barcode' ? "reader" : "reader2");
    Html5Qrcode.getCameras().then(cameras => {
        const cameraId = cameras[0].id;
        html5QrCode.start(cameraId, { fps: 10, qrbox: 250 }, (decodedText) => {
            document.getElementById(targetId).value = decodedText;
            html5QrCode.stop();
        });
    }).catch(err => console.error(err));
}

// Submit transfer
function submitTransfer() {
    if (!fromSupplier || !$('#to_barcode').val()) { alert('Scan both barcodes'); return; }
    let products = [];
    let index = 0;

    $('#productTable tbody tr').each(function(){
        let pid = $(this).find('.product_select').val();
        let qty = $(this).find('.qty_input').val();

        if (pid && qty > 0) {
            products.push({
                product_id: pid,
                qty: qty,
                image_index: index // 🔑 KEY FIX
            });
            index++;
        }
    });

    if(products.length===0){ alert('Add products'); return; }

    $.get('/transfer/supplier', { barcode: $('#to_barcode').val() }, function(s){
        toSupplier = s.id;
        let formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('from_supplier_id', fromSupplier);
        formData.append('to_supplier_id', toSupplier);
        formData.append('products', JSON.stringify(products));

        let imgIndex = 0;

        $('#productTable tbody tr').each(function(){
            let pid = $(this).find('.product_select').val();
            let qty = $(this).find('.qty_input').val();

            if (pid && qty > 0) {
                let fileInput = $(this).find('.image_input')[0];
                if (fileInput && fileInput.files[0]) {
                    formData.append(`images[${imgIndex}]`, fileInput.files[0]);
                }
                imgIndex++;
            }
        });


        $.ajax({
            url:'/transfer/submit',
            type:'POST',
            data: formData,
            contentType:false,
            processData:false,
            success: res => { alert(res.message); location.reload(); },
            error: err => { alert(err.responseJSON?.message || 'Transfer failed'); console.error(err); }
        });
    }).fail(() => alert('Destination supplier not found'));
}
</script>
@endsection
