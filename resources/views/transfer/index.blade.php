@extends('layouts.master')

@section('title') Transfer @endsection

@section('top')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">

    <!-- daterange picker -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
    <!-- bootstrap datepicker -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">

    <style>
        span.select2 {
            width: 100% !important;
            margin-bottom: 5px;
        }
        .btntr {
            margin: 5px 0px 15px;
            background: #414141;
            color: #fff;
            padding: 5px 15px;
            border: 2px solid #4d4d4d;
        }
        button.scanto.btntr, button.scanf.btntr {
            display: block;
            width: 100%;
            margin: 5px 0px 15px;
            background: #3e8eba;
            color: #fff;
            border: 0;
            padding: 5px 0;
            border: 2px solid #3880a7;
        }
        button.clear.btntr {
            display: block;
            margin: 5px 0;
        }
        input#to_barcode, input#from_barcode {
            padding: 8px 15px;
            min-width: 100%;
            border: 1px solid #ddd;
        }
        button.clear.btntr {
            display: block;
            margin: 0 0 15px 0;
            background: transparent;
            color: #125981;
            border: 0;
            padding: 0;
        }
        .getlisting {
            background: #fff;
            padding: 20px 10px;
            border: 1px solid #ddd;
            margin: 10px 0 20px;
        }
        .confirm-transfer button {
            background: #01670f;
            display: block;
            color: #ffff;
            width: 100%;
            padding: 8px;
            border: 0;
            font-size: 17px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .select2-container--default .select2-selection--single, .select2-selection .select2-selection--single {
            border: 1px solid #666;
            border-radius: 0;
            padding: 8px 12px;
            height: 40px;
            font-size: 15px;
        }
        .productqty {
            margin: 9px 0;
            padding: 6px 10px;
            font-size: 15px;
        }

        table#productTable {
            border: 0;
            margin-bottom: 10px;
        }

        table#productTable th.ptp {
            width: 65%;
        }


        table#productTable .ptq {
            width: 15%;
        }

        table#productTable th.pta, table#productTable th.pta {
            width: 10%;
        }


        td.ptp select, td.ptq input {
            width: 100% !important;
            padding: 10px 5px;
            border: 0;
            color: #000;
        }

        td.ptm button {
            background: transparent;
            border: 0;
            font-size: 10px;
            padding: 5px;
        }

        button.addpro {
            background: #000;
            color: #ffff;
            border: 0;
            padding: 6px 10px;
            font-size: 12px;
        }
        #productTable th {
            padding: 5px;
            background: #eee;
            color: #000;
        }
        section.content.container-fluid {
            padding: 0;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

@endsection

@section('header') Transfer @endsection
@section('description') This page about your all Transfer Products @endsection

@section('top')
@endsection

@section('breadcrumb')
<ol class="breadcrumb">
    <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active"> Transfer</li>
</ol>
@endsection

@section('content')
    

<div class="container">
<h3>Product Transfer</h3>
<form method="POST" onsubmit="return false;">

<div class="startscan">

    <div id="reader" style="width:100%; max-width:400px;"></div>

    <button onclick="startScanner('from_barcode')" class="scanf btntr">Scan From</button>

    <input type="text" id="from_barcode" placeholder="Scan FROM supplier barcode" >

    <button type="button" onclick="loadSource()" class="loads btntr">Load Source</button>

    <button onclick="document.getElementById('from_barcode').value=''" class="clear btntr">
        Clear Barcode
    </button>

</div>


<div class="getlisting">


    <table border="1" width="100%" id="productTable">
        <thead>
            <tr>
                <th class="ptp">Product</th>
                <th class="pta">Available</th>
                <th class="ptq">Qty</th>
                <th class="ptm"> - </th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button type="button" onclick="addRow()" class="addpro">➕ Add Product</button>

    <!-- <select id="product_id" class="select2 product-qty2"></select>

    <input type="number" id="qty" placeholder="Qty" class="productqty">

    <div id="available"></div> -->

</div>

<div class="transferlisting"> 

    <div id="reader" style="width:100%; max-width:400px;"></div>

    <button onclick="startScanner('to_barcode')"  class="scanto btntr">Scan To</button>

    <input type="text" id="to_barcode" placeholder="Scan TO supplier barcode" >

    <button onclick="document.getElementById('to_barcode').value=''" class="clear btntr">
        Clear
    </button>

</div>

<div class="confirm-transfer">

    <button type="button" onclick="submitTransfer()">Transfer</button>
    @csrf

</div>

</form>

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


    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>

        

        let scanStep = 'from'; // from → to

        function onScanSuccess(decodedText) {

            if (scanStep === 'from') {
                $('#from_barcode').val(decodedText);
                loadSource(); // your existing function
                scanStep = 'to';
                alert('Source scanned. Scan destination barcode.');
            } 
            else {
                $('#to_barcode').val(decodedText);
                submitTransfer(); // your existing function
                alert('Transfer completed.');
            }
        }
        
        function beep() {
            new Audio('/beep.mp3').play();
        }
       
        function onScanFailure(error) {
            // silently ignore scan errors
        }

        let html5QrCode;

        function startScanner(targetInputId) {
            html5QrCode = new Html5Qrcode("reader");

            Html5Qrcode.getCameras().then(cameras => {
                // Prefer back camera
                const backCam = cameras.find(cam =>
                    cam.label.toLowerCase().includes("back") ||
                    cam.label.toLowerCase().includes("rear")
                );

                const cameraId = backCam ? backCam.id : cameras[0].id;

                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: 250
                    },
                    (decodedText) => {
                        // ✅ Fill the correct input
                        document.getElementById(targetInputId).value = decodedText;

                        // Auto actions
                        if (targetInputId === 'from_barcode') {
                            fetchSupplier(decodedText, 'from');
                        } else {
                            fetchSupplier(decodedText, 'to');
                        }

                        // Stop camera
                        html5QrCode.stop();
                        document.getElementById("reader").innerHTML = "";
                    }
                );
            }).catch(err => {
                alert("Camera not available");
                console.error(err);
            });
        }

        function fetchSupplier(barcode, type) {
            fetch(`/transfer/supplier?barcode=${barcode}`)
                .then(res => res.json())
                .then(data => {
                    console.log(type.toUpperCase() + " supplier:", data);
                });
        }


    
    </script>

    <script>

        let fromSupplier = null;
        let toSupplier = null;
        let supplierStocks = [];


        // function loadSource() {
        //     let barcode = $('#from_barcode').val();
        //     $.get('/transfer/supplier', { barcode: barcode })
        //         .done(function (s) {
        //             fromSupplier = s.id;

        //             $.get('/transfer/products', { supplier_id: s.id }, function (stocks) {
        //                 $('#product_id').empty();
        //                 stocks.forEach(st => {
        //                     $('#product_id').append(
        //                         `<option value="${st.product_id}" data-qty="${st.qty}">
        //                             ${st.product.name} (${st.qty})
        //                         </option>`
        //                     );
        //                 });
        //                 $('#product_id').trigger('change');
        //             });

        //         })
        //         .fail(function () {
        //             alert('Source supplier not found');
        //         });
        // }


        function loadSource() {
            $.get('/transfer/supplier', { barcode: $('#from_barcode').val() }, function (s) {
                fromSupplier = s.id;

                $.get('/transfer/products', { supplier_id: s.id }, function (stocks) {
                    supplierStocks = stocks;
                    $('#productTable tbody').empty();
                    addRow(); // start with one row
                });
            }).fail(() => alert('Source supplier not found'));
        }


        function addRow() {
            let options = supplierStocks.map(st =>
                `<option value="${st.product_id}" data-qty="${st.qty}">
                    ${st.product.name}
                </option>`
            ).join('');

            let row = `
            <tr>
                <td class="ptp">
                    <select class="product_select" onchange="updateAvailable(this)">
                        <option value="">Select product</option>
                        ${options}
                    </select>
                </td>
                <td class="available pta">-</td>
                <td class="ptq">
                    <input type="number" class="qty_input" min="1">
                </td>
                <td class="ptm">
                    <button type="button" onclick="removeRow(this)">❌</button>
                </td>
            </tr>
            `;

            $('#productTable tbody').append(row);
        }


        function updateAvailable(select) {
            let available = $('option:selected', select).data('qty') || 0;
            let row = $(select).closest('tr');
            row.find('.available').text(available);

            row.find('.qty_input').off().on('input', function () {
                if (parseInt(this.value) > available) {
                    alert('Quantity exceeds available stock');
                    this.value = available;
                }
            });
        }

        function removeRow(btn) {
            $(btn).closest('tr').remove();
        }

        

        //$('button').prop('disabled', true);
        
        $('#from_barcode').focus();
        $('#to_barcode').focus();
        
      //  if (!confirm('Confirm transfer?')) return;

        // Enable only when all data is ready

        $('#product_id').on('change', function () {
            $('#available').text(
                $('option:selected').data('qty') + ' available'
            );
        });


        $('#qty').on('input', function () {
            let available = $('option:selected').data('qty') || 0;
            let entered = parseInt($(this).val());

            if (entered > available) {
                alert('Quantity exceeds available stock');
                $(this).val(available);
            }
        });

        // function submitTransfer() {
        //     $.get('/transfer/supplier', { barcode: $('#to_barcode').val() }, function (s) {
        //         toSupplier = s.id;

        //         $.post('/transfer/submit', {
        //             _token: $('meta[name=csrf-token]').attr('content'),
        //             from_supplier_id: fromSupplier,
        //             to_supplier_id: toSupplier,
        //             product_id: $('#product_id').val(),
        //             qty: $('#qty').val()
        //         }, function () {
        //             alert('Transfer successful');

        //             // 1️⃣ Refresh the page after success
        //             location.reload();

        //         });
        //     });
        // }

        function submitTransfer() {
            let products = [];

            $('#productTable tbody tr').each(function () {
                let productId = $(this).find('.product_select').val();
                let qty = $(this).find('.qty_input').val();

                if (productId && qty > 0) {
                    products.push({
                        product_id: productId,
                        qty: qty
                    });
                }
            });

            if (products.length === 0) {
                alert('Add at least one product');
                return;
            }

            $.get('/transfer/supplier', { barcode: $('#to_barcode').val() }, function (s) {
                toSupplier = s.id;

                $.post('/transfer/submit', {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    from_supplier_id: fromSupplier,
                    to_supplier_id: toSupplier,
                    products: products
                }, function () {
                    alert('Transfer successful');
                    location.reload();
                });
            }).fail(() => alert('Destination supplier not found'));
        }
        </script>

    </script>

@endsection