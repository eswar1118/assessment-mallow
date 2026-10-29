<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class GetLowStockProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'threshold' => [
                'nullable',
                'integer',
                'min:0',
                'max:10000',
            ],
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom error messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'threshold.integer' => 'The threshold must be a valid integer.',
            'threshold.min' => 'The threshold cannot be negative.',
            'threshold.max' => 'The threshold cannot exceed 10,000.',
            'limit.integer' => 'The limit must be a valid integer.',
            'limit.min' => 'The limit must be at least 1.',
            'limit.max' => 'The limit cannot exceed 500.',
        ];
    }

    /**
     * Handle a failed validation attempt for JSON / API requests.
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->is('api/*') || $this->isJson() || $this->header('Content-Type') === 'application/json') {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        parent::failedValidation($validator);
    }
}
