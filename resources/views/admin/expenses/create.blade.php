@extends('layouts.admin')

@section('title', 'Add expense')
@section('heading', 'Add an expense')
@section('subtitle', 'Record a business or van cost')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.expenses.store') }}">
                @csrf
                @include('admin.expenses._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save expense</button>
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
