@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Edit Role</div>
    <div class="card-body">
        <form action="{{ route('role.update',$role->id_role) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3"><label>Nama Role</label><input type="text" name="name" value="{{ $role->name }}" class="form-control" required></div>
            <div class="mb-3"><label>Deskripsi</label><textarea name="description" class="form-control">{{ $role->description }}</textarea></div>
            <button class="btn btn-success">Update</button>
            <a href="{{ route('role.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection




