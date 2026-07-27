<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'periode_pendaftaran_mulai' => [
                'required',
                'date',
            ],

            'periode_pendaftaran_selesai' => [
                'required',
                'date',
                'after_or_equal:periode_pendaftaran_mulai',
            ],

            'tanggal_pelaksanaan_mulai' => [
                'required',
                'date',
            ],

            'tanggal_pelaksanaan_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_pelaksanaan_mulai',
            ],

            'status' => [
                'required',
                'in:draft,aktif,selesai',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama event wajib diisi.',
            'periode_pendaftaran_mulai.required' => 'Tanggal mulai pendaftaran wajib diisi.',
            'periode_pendaftaran_selesai.required' => 'Tanggal selesai pendaftaran wajib diisi.',
            'periode_pendaftaran_selesai.after_or_equal' => 'Tanggal selesai pendaftaran tidak boleh sebelum tanggal mulai pendaftaran.',
            'tanggal_pelaksanaan_mulai.required' => 'Tanggal mulai pelaksanaan wajib diisi.',
            'tanggal_pelaksanaan_selesai.required' => 'Tanggal selesai pelaksanaan wajib diisi.',
            'tanggal_pelaksanaan_selesai.after_or_equal' => 'Tanggal selesai pelaksanaan tidak boleh sebelum tanggal mulai pelaksanaan.',
            'status.in' => 'Status event tidak valid.',
        ];
    }
}