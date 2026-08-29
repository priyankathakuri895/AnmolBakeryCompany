@extends('layouts.admin')

@section('title', 'Edit raw material')
@section('heading', $material->name)
@section('subtitle', 'Edit material details')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.materials.update', $material) }}">
                @csrf
                @method('PUT')
                @include('admin.materials._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
