@extends('layouts.admin')

@section('title', 'Edit van')
@section('heading', $van->name)
@section('subtitle', 'Edit van details')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vans.update', $van) }}">
                @csrf
                @method('PUT')
                @include('admin.vans._form')

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <a href="{{ route('admin.vans.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
