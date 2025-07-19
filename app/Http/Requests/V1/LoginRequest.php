<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //  NOTE: Could be either email or username
            'data.attributes.identifier' => 'required|string|max:255',
            'data.attributes.password' => 'required|string|min:8|max:255',
        ];
    }

    public function messages()
    {
        return [
            //  NOTE: Not sure which validation rule will be used, so we provide both
            'data.attributes.identifier.*' => 'The identifier must be a valid email or username.',
            'data.attributes.identifier' => 'The identifier must be a valid email or username.',
        ];
    }

    public function identifier(): string
    {
        return $this->input('data.attributes.identifier');
    }

    public function password(): string
    {
        return $this->input('data.attributes.password');
    }
}
