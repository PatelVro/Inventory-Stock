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
<form method="POST">
<div id="reader" style="width:100%; max-width:400px;"></div>
<input type="text" id="from_barcode" placeholder="Scan FROM supplier barcode">
<button type="button" onclick="loadSource()">Load Source</button>

<br>
<br>
<br>

<select id="product_id" class="select2 product-qty2"></select>
<input type="number" id="qty" placeholder="Qty">
<div id="available"></div>

<br>
<br>
<br>
<div id="reader" style="width:100%; max-width:400px;"></div>
<input type="text" id="to_barcode" placeholder="Scan TO supplier barcode">

<button type="button" onclick="submitTransfer()">Transfer</button>
@csrf
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

        const html5QrCode = new Html5Qrcode("reader");

        Html5Qrcode.getCameras().then(cameras => {
            const backCam = cameras.find(cam =>
                cam.label.toLowerCase().includes("back") ||
                cam.label.toLowerCase().includes("rear")
            );

            html5QrCode.start(
                backCam ? backCam.id : cameras[0].id,
                { fps: 10, qrbox: 250 },
                barcode => {
                    console.log("Scanned:", barcode);
                    html5QrCode.stop();
                }
            );
        });
        </script>
    <script>

        let fromSupplier = null;
        let toSupplier = null;

        function loadSource() {
            let barcode = $('#from_barcode').val();
            $.get('/transfer/supplier', { barcode: barcode })
                .done(function (s) {
                    fromSupplier = s.id;

                    $.get('/transfer/products', { supplier_id: s.id }, function (stocks) {
                        $('#product_id').empty();
                        stocks.forEach(st => {
                            $('#product_id').append(
                                `<option value="${st.product_id}" data-qty="${st.qty}">
                                    ${st.product.name} (${st.qty})
                                </option>`
                            );
                        });
                        $('#product_id').trigger('change');
                    });

                })
                .fail(function () {
                    alert('Source supplier not found');
                });
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

        function submitTransfer() {
            $.get('/transfer/supplier', { barcode: $('#to_barcode').val() }, function (s) {
                toSupplier = s.id;

                $.post('/transfer/submit', {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    from_supplier_id: fromSupplier,
                    to_supplier_id: toSupplier,
                    product_id: $('#product_id').val(),
                    qty: $('#qty').val()
                }, function () {
                    alert('Transfer successful');
                });
            });
        }


    </script>

@endsection