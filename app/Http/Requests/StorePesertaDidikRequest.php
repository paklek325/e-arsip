<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesertaDidikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_peserta_didik'    => 'required|string|max:150',
            'tempat_lahir'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'tahun_angkatan' => 'required|string|max:10',
            'rombel'        => 'required|in:A,B',
            'alamat'        => 'nullable|string',
        ];
    }
}
