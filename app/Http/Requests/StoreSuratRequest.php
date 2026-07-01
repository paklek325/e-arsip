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
                Rule::unique('surat', 'no_surat')
                    ->where(function ($q) {
                        $instansi = $this->input('instansi');
                        $tanggal  = $this->input('tanggal_surat');

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
            'perihal'       => 'required|string|max:255',
            'instansi'      => 'required|string|max:255',
            'pengirim'      => 'nullable|string|max:255',
            'penerima'      => 'nullable|string|max:255',

            'kode_surat' => [
                'required_if:jenis_surat,Keluar',
                'nullable',
                'string',
                'max:10', // samakan dengan panjang kolom surat.kode_surat & kode.kode (varchar(10))
                Rule::when($this->jenis_surat === 'Keluar', [
                    'exists:kode,kode',
                ]),
            ],

            'file_surat'   => 'required|array|min:1',
            'file_surat.*' => 'required|file',
        ];
    }

    public function messages(): array
    {
        return [
            'no_surat.unique'        => 'Nomor surat sudah digunakan untuk instansi dan tanggal tersebut.',
            'instansi.required'      => 'Instansi wajib diisi.',
            'kode_surat.required_if' => 'Kode surat wajib diisi untuk Surat Keluar.',
            'kode_surat.exists'      => 'Kode surat yang dipilih tidak valid. Pilih dari daftar kode yang tersedia.',
        ];
    }
}
