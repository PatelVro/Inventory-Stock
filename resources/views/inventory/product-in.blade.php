@extends('layouts.master') {{-- your layout --}}

@section('content')
<div class="container">
    <h1 class="mb-4">Product In</h1>

    {{-- Manual barcode input --}}
    <div class="mb-4">
        <label class="form-label">Location Barcode (Manual)</label>
        <input type="text" id="manual-barcode" class="form-control"
               placeholder="Enter barcode manually">
    </div>

    {{-- Scanner controls --}}
    <div class="mb-3">
        <button class="btn btn-primary" id="start-scan">Start Scanner</button>
        <button class="btn btn-danger" id="stop-scan">Stop Scanner</button>
    </div>

    {{-- Scanner area --}}
    <div id="scanner-container" style="width: 100%; max-width: 400px; border: 1px solid #ccc; padding: 10px;"></div>

    {{-- Scanned barcode result --}}
    <div class="mt-3">
        <label class="form-label">Scanned Barcode</label>
        <input type="text" id="scanned-barcode" class="form-control" readonly>
    </div>
</div>
@endsection

@section('scripts')
{{-- QuaggaJS CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

<script>
let scannerRunning = false;

document.getElementById('start-scan').addEventListener('click', function () {
    if (scannerRunning) return;

    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: document.querySelector('#scanner-container'),
            constraints: {
                facingMode: "environment" // back camera
            },
        },
        decoder: {
            readers: ["code_128_reader", "ean_reader", "ean_8_reader", "upc_reader", "code_39_reader"]
        }
    }, function (err) {
        if (err) {
            console.error(err);
            alert('Camera not available or permission denied.');
            return;
        }
        Quagga.start();
        scannerRunning = true;
    });

    Quagga.onDetected(function (data) {
        let code = data.codeResult.code;
        document.getElementById('scanned-barcode').value = code;
        document.getElementById('manual-barcode').value = code;

        Quagga.stop();
        scannerRunning = false;
    });
});

document.getElementById('stop-scan').addEventListener('click', function () {
    if (scannerRunning) {
        Quagga.stop();
        scannerRunning = false;
    }
});
</script>
@endsection
