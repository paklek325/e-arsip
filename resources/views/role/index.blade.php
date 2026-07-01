@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center border-bottom mb-3">
    <h2>Daftar Role</h2>
    <a href="{{ route('role.create') }}" class="btn btn-primary">Tambah Role</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Role</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($roles as $r)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $r->name }}</td>
            <td>{{ $r->description }}</td>
            <td>
                <a href="{{ route('role.show',$r->id_role) }}" class="btn btn-info btn-sm">Detail</a>
                <a href="{{ route('role.edit',$r->id_role) }}" class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('role.destroy',$r->id_role) }}" method="POST" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus role ini?')">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection




