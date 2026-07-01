@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Tambah Role</div>
    <div class="card-body">
        <form action="{{ route('role.store') }}" method="POST">
            @csrf
            <div class="mb-3"><label>Nama Role</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label>Deskripsi</label><textarea name="description" class="form-control"></textarea></div>
            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('role.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection




