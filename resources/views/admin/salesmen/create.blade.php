@extends('layouts.admin')

@section('title', 'Add salesman')
@section('heading', 'Add salesman')
@section('subtitle', 'Add a staff member who can be assigned to a van')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.salesmen.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.salesmen._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save salesman</button>
                    <a href="{{ route('admin.salesmen.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
