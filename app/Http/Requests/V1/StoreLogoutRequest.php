<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;

class StoreLogoutRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.relationships.deviceTokens.data.attributes.token' => 'nullable|string',
        ];
    }

    public function deviceToken(): ?string
    {
        return $this->input('data.relationships.deviceTokens.data.attributes.token');
    }
}
