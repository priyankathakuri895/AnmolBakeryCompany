@extends('layouts.admin')

@section('title', 'Add raw material')
@section('heading', 'Add raw material')
@section('subtitle', 'Define how it is packed so kg and litres can be calculated')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.materials.store') }}">
                @csrf
                @include('admin.materials._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save material</button>
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
