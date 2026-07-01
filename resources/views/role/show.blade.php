@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Detail Role</div>
    <div class="card-body">
        <p><b>Nama Role:</b> {{ $role->name }}</p>
        <p><b>Deskripsi:</b> {{ $role->description }}</p>
        <a href="{{ route('role.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection




