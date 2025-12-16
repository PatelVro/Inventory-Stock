@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Product Out</h1>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary mb-3">
        &larr; Back
    </a>
    <p>Record outgoing products here.</p>
</div>
@endsection
