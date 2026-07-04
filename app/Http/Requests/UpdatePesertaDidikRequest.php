<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePesertaDidikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Teks: hanya huruf, angka, spasi (tanpa simbol)
            'nama_peserta_didik' => ['required', 'string', 'max:150', 'regex:/^[A-Za-z0-9 ]+$/'],
            'tempat_lahir'  => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9 ]+$/'],
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            // Tahun angkatan boleh format "2026" atau "2025/2026"
            'tahun_angkatan' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9 \/-]+$/'],
            'rombel'        => 'required|in:A,B',
            // Alamat: alphanumeric + spasi + pemisah alamat ( . , / - )
            'alamat'        => ['nullable', 'string', 'regex:/^[A-Za-z0-9 .,\/-]+$/'],

            // File: hanya PDF, Word, dan gambar
            'file_ppdb'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_kk'         => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_akte'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_ktp'        => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_kts'        => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_foto'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_ijazah_smp' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_ijazah_sma' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_peserta_didik.regex' => 'Nama hanya boleh berisi huruf, angka, dan spasi (tanpa simbol).',
            'tempat_lahir.regex'       => 'Tempat lahir hanya boleh berisi huruf, angka, dan spasi (tanpa simbol).',
            'tahun_angkatan.regex'     => 'Tahun angkatan hanya boleh berisi angka, huruf, dan tanda / -.',
            'alamat.regex'             => 'Alamat hanya boleh berisi huruf, angka, spasi, dan tanda . , / -.',
        ];
    }
}
