<?php

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_code' => ['required', 'string', 'max:50', 'unique:listings,unit_code'],
            'unit_name' => ['required', 'string', 'max:150'],
            'tower' => ['required', 'string', 'max:80'],
            'floor' => ['required', 'integer', 'min:0'],
            'room_number' => ['required', 'string', 'max:50'],
            'unit_type' => ['required', 'string', 'max:80'],
            'status' => ['required', 'string', Rule::in(['available', 'occupied', 'maintenance'])],
            'tenant_name' => ['nullable', 'string', 'max:150'],
            'tenant_phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error('Validation failed', $validator->errors(), 422)
        );
    }
}
