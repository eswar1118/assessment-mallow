<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation:
     * - Trim and lowercase email to prevent duplicate customers due to casing or whitespace.
     * - Normalize top-level or nested customer name and email.
     * - Normalize product item keys if passed as 'id' instead of 'product_id'.
     */
    protected function prepareForValidation(): void
    {
        $email = $this->input('customer_email')
            ?? $this->input('email')
            ?? $this->input('customer.email');

        $name = $this->input('customer_name')
            ?? $this->input('name')
            ?? $this->input('customer.name');

        $merged = [];

        if ($email !== null && is_string($email)) {
            $merged['customer_email'] = strtolower(trim($email));
        }

        if ($name !== null && is_string($name)) {
            $merged['customer_name'] = trim($name);
        }

        $products = $this->input('products');
        if (is_array($products)) {
            $merged['products'] = array_map(function ($item) {
                if (is_array($item)) {
                    if (!isset($item['product_id']) && isset($item['id'])) {
                        $item['product_id'] = $item['id'];
                    }
                }
                return $item;
            }, $products);
        }

        if (!empty($merged)) {
            $this->merge($merged);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],

            'customer_name' => [
                Rule::requiredIf(function () {
                    $email = $this->input('customer_email');
                    if (empty($email) || !is_string($email)) {
                        return false;
                    }

                    return !Customer::where('email', strtolower(trim($email)))->exists();
                }),
                'nullable',
                'string',
                'max:255',
            ],

            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],

            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_email' => 'customer email',
            'customer_name' => 'customer name',
            'products' => 'products',
            'products.*.product_id' => 'product ID',
            'products.*.quantity' => 'quantity',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_email.required' => 'Customer email is required.',
            'customer_email.email' => 'Customer email must be a valid email address.',
            'customer_name.required' => 'Customer name is required when the customer does not already exist.',
            'products.required' => 'Products are required.',
            'products.array' => 'Products must be an array.',
            'products.min' => 'Products cannot be empty; at least one product is required.',
            'products.*.product_id.required' => 'Every product must have a product ID.',
            'products.*.product_id.exists' => 'One or more selected products do not exist.',
            'products.*.product_id.distinct' => 'Duplicate products are not allowed in the same order.',
            'products.*.quantity.required' => 'Every product must have a quantity.',
            'products.*.quantity.integer' => 'Every product quantity must be an integer.',
            'products.*.quantity.min' => 'Every product quantity must be greater than zero.',
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
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}
