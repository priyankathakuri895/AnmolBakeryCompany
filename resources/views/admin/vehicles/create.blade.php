@extends('layouts.admin')

@section('title', 'Add vehicle')
@section('heading', 'Add supplier vehicle')
@section('subtitle', 'Register a vehicle that belongs to one of your suppliers')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicles.store') }}">
                @csrf
                @include('admin.vehicles._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save vehicle</button>
                    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
