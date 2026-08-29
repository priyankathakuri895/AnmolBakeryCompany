@extends('layouts.admin')

@section('title', 'Add supplier')
@section('heading', 'Add supplier')
@section('subtitle', 'A wholesaler that delivers raw material to the warehouse')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.suppliers.store') }}">
                @csrf
                @include('admin.suppliers._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save supplier</button>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
