<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateClientProfileRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => 'sometimes|string|max:255',
            'data.attributes.phone' => [
                'sometimes',
                'phone',
                'nullable',
                Rule::unique(User::class, 'phone')->ignore($this->user()?->id),
            ],
            'data.attributes.avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:4048|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => 'The name must be a string.',
            'data.attributes.name.max' => 'The name may not be greater than 255 characters.',
            'data.attributes.phone.phone' => 'The phone number must be a valid phone number.',
            'data.attributes.phone.unique' => 'The phone number has already been taken.',
            'data.attributes.avatar.image' => 'The avatar must be an image.',
            'data.attributes.avatar.mimes' => 'The avatar must be a file of type: jpeg, png, jpg, gif, svg.',
            'data.attributes.avatar.max' => 'The avatar may not be greater than 4048 kilobytes.',
        ];
    }

    public function name(): ?string
    {
        return $this->input('data.attributes.name');
    }

    public function phone(): ?string
    {
        return $this->input('data.attributes.phone');
    }

    public function avatar()
    {
        return $this->file('data.attributes.avatar');
    }
}
