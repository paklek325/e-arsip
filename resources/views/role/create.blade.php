@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Tambah Role</div>
    <div class="card-body">
        <form action="{{ route('role.store') }}" method="POST">
            @csrf
            <div class="mb-3"><label for="role_name">Nama Role</label><input type="text" id="role_name" name="name" class="form-control" required></div>
            <div class="mb-3"><label for="role_description">Deskripsi</label><textarea id="role_description" name="description" class="form-control"></textarea></div>
            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('role.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection




