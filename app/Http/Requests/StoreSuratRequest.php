<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_surat' => [
                'required',
                'string',
                'max:100',
                // Alphanumeric + spasi + pemisah lazim nomor surat ( / - . )
                'regex:/^[A-Za-z0-9 .\/-]+$/',
                Rule::unique('surat', 'no_surat')
                    ->where(function ($q) {
                        $instansi = $this->input('instansi');
                        $tanggal  = $this->input('tanggal_surat');
                        $jenis    = $this->input('jenis_surat');

                        if ($jenis) {
                            $q->where('jenis_surat', $jenis);
                        }

                        if ($instansi) {
                            $q->where('instansi', $instansi);
                        }

                        if ($tanggal) {
                            $q->whereDate('tanggal_surat', $tanggal);
                        }
                    }),
            ],
            'jenis_surat'   => 'required|in:Masuk,Keluar',
            'tanggal_surat' => 'required|date',
            // Field teks: hanya huruf, angka, dan spasi (tanpa simbol)
            'perihal'       => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'instansi'      => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'pengirim'      => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'penerima'      => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],

            'kode_surat' => [
                'required_if:jenis_surat,Keluar',
                'nullable',
                'string',
                'max:10', // samakan dengan panjang kolom surat.kode_surat & kode.kode (varchar(10))
                // Alphanumeric + spasi + pemisah ( / - . )
                'regex:/^[A-Za-z0-9 .\/-]+$/',
                Rule::when($this->jenis_surat === 'Keluar', [
                    'exists:kode,kode',
                ]),
            ],

            'file_surat'   => 'required|array|min:1',
            'file_surat.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'no_surat.unique'        => 'Nomor surat sudah digunakan untuk instansi dan tanggal tersebut.',
            'instansi.required'      => 'Instansi wajib diisi.',
            'kode_surat.required_if' => 'Kode surat wajib diisi untuk Surat Keluar.',
            'kode_surat.exists'      => 'Kode surat yang dipilih tidak valid. Pilih dari daftar kode yang tersedia.',

            'no_surat.regex'    => 'Nomor surat hanya boleh berisi huruf, angka, spasi, dan tanda / - .',
            'kode_surat.regex'  => 'Kode surat hanya boleh berisi huruf, angka, spasi, dan tanda / - .',
            'perihal.regex'     => 'Perihal hanya boleh berisi huruf, angka, dan spasi (tanpa simbol).',
            'instansi.regex'    => 'Instansi hanya boleh berisi huruf, angka, dan spasi (tanpa simbol).',
            'pengirim.regex'    => 'Pengirim hanya boleh berisi huruf, angka, dan spasi (tanpa simbol).',
            'penerima.regex'    => 'Penerima hanya boleh berisi huruf, angka, dan spasi (tanpa simbol).',
            'file_surat.*.mimes' => 'File hanya boleh berupa PDF, Word (doc/docx), atau gambar (jpg/jpeg/png).',
            'file_surat.*.max'   => 'Ukuran file maksimal 10 MB.',
        ];
    }
}
