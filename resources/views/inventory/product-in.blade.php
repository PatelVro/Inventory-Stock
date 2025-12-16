@extends('layouts.master') {{-- your layout --}}

@section('content')
<div class="container">
    <h1 class="mb-4">Product In</h1>

    {{-- Manual barcode input --}}
    <div class="mb-4">
        <label class="form-label">Location Barcode (Manual)</label>
        <input type="text" id="manual-barcode" class="form-control" placeholder="Enter barcode manually">
    </div>

    {{-- Scanner area --}}
    <div id="scanner" style="width: 100%; max-width: 400px; border: 1px solid #ccc; padding: 10px;"></div>

    {{-- Scanned barcode result --}}
    <div class="mt-3">
        <label class="form-label">Scanned Barcode</label>
        <input type="text" id="scanned-barcode" class="form-control" readonly>
    </div>
</div>
@endsection

@section('scripts')
{{-- Html5-qrcode library --}}
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scannedInput = document.getElementById('scanned-barcode');
    const manualInput = document.getElementById('manual-barcode');

    function onScanSuccess(decodedText, decodedResult) {
        scannedInput.value = decodedText;
        manualInput.value = decodedText;

        // Optionally stop scanner after first scan
        html5QrcodeScanner.clear().catch(err => console.error(err));
    }

    function onScanFailure(error) {
        // console.log(`Scan failed: ${error}`);
    }

    const html5QrcodeScanner = new Html5Qrcode("scanner");

    // Start the camera automatically
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
                alert("Unable to access camera. Make sure it is allowed and you are using HTTPS or localhost.");
            });
        } else {
            alert("No camera found on this device.");
        }
    }).catch(err => {
        console.error("Camera error:", err);
        alert("Unable to get cameras. Make sure camera is allowed.");
    });
});
</script>
@endsection
