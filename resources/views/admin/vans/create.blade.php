@extends('layouts.admin')

@section('title', 'Add van')
@section('heading', 'Add van')
@section('subtitle', 'Register a van in the delivery fleet')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vans.store') }}">
                @csrf
                @include('admin.vans._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save van</button>
                    <a href="{{ route('admin.vans.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
