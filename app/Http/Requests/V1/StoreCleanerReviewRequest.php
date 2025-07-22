<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreCleanerReviewRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.rating' => 'required|integer|min:1|max:5',
            'data.attributes.comment' => 'nullable|string|max:1000',
        ];
    }

    public function mappedAttributes(): array
    {
        return $this->mapped(
            [
                'data.attributes.rating' => 'rating',
                'data.attributes.comment' => 'comment',
            ],
        )->toArray();
    }
}
