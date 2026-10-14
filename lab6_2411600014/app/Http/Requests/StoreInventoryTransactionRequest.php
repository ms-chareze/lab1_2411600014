<?php

namespace App\Http\Requests;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Part 3 Step 3 - validation rules for recording a stock movement.
 *
 * The "sufficient stock" check is done in after(): it runs only once the basic
 * rules pass, so we know product_id/type/quantity are well-formed and can read
 * the product safely. (A custom Rule class would work too; after() keeps the
 * cross-field logic in one place.) The observer re-checks inside the DB
 * transaction as the final safety net against race conditions.
 */
class StoreInventoryTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // any authenticated user may record stock movements (route is behind auth)
    }

    public function rules(): array
    {
        return [
            'product_id'       => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'type'             => ['required', Rule::in(array_keys(InventoryTransaction::manualTypes()))],
            // Entered as a positive number; the service applies the +/- sign from the type.
            'quantity'         => ['required', 'integer', 'min:1', 'max:1000000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'The selected product does not exist.',
            'type.in'           => 'Please choose Stock In or Stock Out.',
            'quantity.min'      => 'The quantity must be at least 1.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->input('type') !== InventoryTransaction::TYPE_OUT) {
                    return;
                }

                $product = Product::find($this->input('product_id'));

                if ($product && $this->integer('quantity') > $product->quantity) {
                    $validator->errors()->add(
                        'quantity',
                        "Insufficient stock for \"{$product->name}\". Available: {$product->quantity}, requested: {$this->integer('quantity')}."
                    );
                }
            },
        ];
    }
}
