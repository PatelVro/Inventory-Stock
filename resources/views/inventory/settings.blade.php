@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Inventory Settings</h1>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary mb-3">
        &larr; Back
    </a>
    <p>Manage inventory configuration.</p>
</div>
@endsection
