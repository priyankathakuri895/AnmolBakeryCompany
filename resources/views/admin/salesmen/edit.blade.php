@extends('layouts.admin')

@section('title', 'Edit salesman')
@section('heading', $salesman->name)
@section('subtitle', 'Edit salesman details')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.salesmen.update', $salesman) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.salesmen._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <a href="{{ route('admin.salesmen.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
