@extends('layouts.admin')

@section('title', 'Edit vehicle')
@section('heading', $vehicle->vehicle_number)
@section('subtitle', 'Edit vehicle details')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicles.update', $vehicle) }}">
                @csrf
                @method('PUT')
                @include('admin.vehicles._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
