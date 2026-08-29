@extends('layouts.admin')

@section('title', 'Edit supplier')
@section('heading', $supplier->name)
@section('subtitle', 'Edit supplier details')

@section('topbar')
    <a href="{{ route('admin.vehicles.index', ['supplier' => $supplier->id]) }}" class="btn btn-outline btn-sm">
        Vehicles ({{ $supplier->vehicles()->count() }})
    </a>
@endsection

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}">
                @csrf
                @method('PUT')
                @include('admin.suppliers._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
