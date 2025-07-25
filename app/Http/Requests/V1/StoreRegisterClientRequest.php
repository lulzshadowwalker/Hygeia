<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegisterClientRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.name' => 'required|string|max:255',
            'data.attributes.username' => 'required|string|max:255|unique:users,username',
            'data.attributes.email' => 'required|email|max:255|unique:users,email',
            'data.attributes.password' => 'required|string|min:8',
            'data.attributes.avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
        ];
    }

    public function messages(): array
    {
        return [
            
            'data.attributes.name.required' => 'The name field is required.',
            'data.attributes.username.required' => 'The username field is required.',
            'data.attributes.email.required' => 'The email field is required.',
            'data.attributes.password.required' => 'The password field is required.',
            'data.attributes.phone.phone' => 'The phone number must be a valid phone number.',
            'data.attributes.avatar.image' => 'The avatar must be an image.',
        ];
    }

    public function name(): string
    {
        return $this->input('data.attributes.name');
    }

    public function username(): string
    {
        return $this->input('data.attributes.username');
    }

    public function email(): string
    {
        return $this->input('data.attributes.email');
    }

    public function password(): string 
    {
        return $this->input('data.attributes.password');
    }

    public function avatar() 
    {
        return $this->file('data.attributes.avatar');
    }
}
