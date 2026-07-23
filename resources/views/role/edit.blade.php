@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Edit Role</div>
    <div class="card-body">
        <form action="{{ route('role.update',$role->id_role) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3"><label for="role_edit_name">Nama Role</label><input type="text" id="role_edit_name" name="name" value="{{ $role->name }}" class="form-control" required></div>
            <div class="mb-3"><label for="role_edit_description">Deskripsi</label><textarea id="role_edit_description" name="description" class="form-control">{{ $role->description }}</textarea></div>
            <button class="btn btn-success">Update</button>
            <a href="{{ route('role.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection




