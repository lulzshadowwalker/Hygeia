<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class OAuthCheckRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.provider' => ['required', 'string', Rule::in(['google', 'facebook', 'apple'])],
            'data.attributes.oauthToken' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.provider.required' => 'The OAuth provider is required.',
            'data.attributes.provider.in' => 'The OAuth provider must be one of: google, facebook, apple.',
            'data.attributes.oauthToken.required' => 'The OAuth token is required.',
        ];
    }

    public function provider(): string
    {
        return $this->input('data.attributes.provider');
    }

    public function oauthToken(): string
    {
        return $this->input('data.attributes.oauthToken');
    }
}
