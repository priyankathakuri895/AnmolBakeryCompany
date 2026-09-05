@extends('layouts.admin')

@section('title', 'Add product')
@section('heading', 'Add product')
@section('subtitle', 'Add a finished good to the catalog sold from the vans')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.store') }}">
                @csrf
                @include('admin.products._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save product</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
