@extends('layouts.admin')

@section('title', 'Edit expense')
@section('heading', 'Edit expense')
@section('subtitle', $expense->category->label().' — Rs. '.number_format($expense->amount, 2))

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">
                @csrf
                @method('PUT')
                @include('admin.expenses._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
