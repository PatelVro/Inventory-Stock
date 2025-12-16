@extends('layouts.master')

@section('content')
<div class="container mt-4">
    <h1>Product In</h1>


<a href="{{ route('inventory.index') }}" class="btn btn-secondary mb-3">
    &larr; Back
</a>

    {{-- Manual barcode input --}}
    <div class="mb-3">
        <label>Location Barcode (Manual)</label>
        <input type="text" id="manual-barcode" class="form-control" placeholder="Enter barcode manually">
    </div>

    {{-- Scanner --}}
    <div id="scanner" style="width:100%; max-width:400px; height:300px; border:1px solid #ccc; margin-bottom:10px;"></div>

    {{-- Scanned barcode --}}
    <div class="mb-3">
        <label>Scanned Barcode</label>
        <input type="text" id="scanned-barcode" class="form-control" readonly>
    </div>

    {{-- Optional submit button --}}
    <button class="btn btn-success" id="save-barcode">Save Barcode</button>
</div>
@endsection

@section('scripts')
{{-- Load Html5-qrcode --}}
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const scannedInput = document.getElementById('scanned-barcode');
    const manualInput = document.getElementById('manual-barcode');

    function onScanSuccess(decodedText, decodedResult) {
        scannedInput.value = decodedText;
        manualInput.value = decodedText;

        // Stop scanning after first scan
        html5QrcodeScanner.stop().then(ignore => {
            console.log("Scanner stopped after first scan");
        }).catch(err => console.error(err));
    }

    function onScanFailure(error) {
        // console.log("Scan failure:", error);
    }

    const html5QrcodeScanner = new Html5Qrcode("scanner");

    // Make sure the scanner div is fully rendered
    setTimeout(() => {
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                const cameraId = cameras[0].id;
                html5QrcodeScanner.start(
                    cameraId,
                    { fps: 10, qrbox: 250 },
                    onScanSuccess,
                    onScanFailure
                ).catch(err => {
                    console.error("Camera start failed:", err);
                    alert("Unable to access camera. Use localhost or HTTPS.");
                });
            } else {
                alert("No camera found on this device.");
            }
        }).catch(err => {
            console.error("Camera error:", err);
            alert("Unable to access camera. Use localhost or HTTPS.");
        });
    }, 500); // slight delay to ensure div is rendered
});
</script>
@endsection
