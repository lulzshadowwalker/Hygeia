<?php

namespace App\Http\Requests\V1;

use App\Enums\Role;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class OAuthLoginRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'data.attributes.provider' => ['required', 'string', Rule::in(['google', 'facebook', 'apple'])],
            'data.attributes.oauthToken' => 'required|string',
            'data.attributes.role' => ['required', 'string', Rule::in([Role::Client->value, Role::Cleaner->value])],
            'data.relationships.deviceTokens.data.attributes.token' => 'nullable|string',
        ];

        // If registering as cleaner, require additional data
        if ($this->input('data.attributes.role') === Role::Cleaner->value) {
            $rules = array_merge($rules, [
                'data.attributes.additionalData.phone' => 'nullable|string|max:255',
                'data.attributes.additionalData.availableDays' => 'nullable|array',
                'data.attributes.additionalData.availableDays.*' => 'string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
                'data.attributes.additionalData.timeSlots' => 'nullable|array',
                'data.attributes.additionalData.timeSlots.*' => 'string|in:morning,afternoon,evening',
                'data.attributes.additionalData.maxHoursPerWeek' => 'nullable|integer|min:1|max:168',
                'data.attributes.additionalData.acceptsUrgentOffers' => 'nullable|boolean',
                'data.attributes.additionalData.yearsOfExperience' => 'nullable|integer|min:0|max:100',
                'data.attributes.additionalData.hasCleaningSupplies' => 'nullable|boolean',
                'data.attributes.additionalData.comfortableWithPets' => 'nullable|boolean',
                'data.attributes.additionalData.serviceRadius' => 'nullable|integer|min:1|max:1000',
                'data.attributes.additionalData.agreedToTerms' => 'nullable|boolean',
                'data.attributes.additionalData.idCard' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
                'data.attributes.additionalData.avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
                'data.relationships.previousServices.data' => 'nullable|array',
                'data.relationships.previousServices.data.*.type' => 'required_with:data.relationships.previousServices.data|string|in:service',
                'data.relationships.previousServices.data.*.id' => 'required_with:data.relationships.previousServices.data|integer|exists:services,id',
                'data.relationships.preferredServices.data' => 'nullable|array',
                'data.relationships.preferredServices.data.*.type' => 'required_with:data.relationships.preferredServices.data|string|in:service',
                'data.relationships.preferredServices.data.*.id' => 'required_with:data.relationships.preferredServices.data|integer|exists:services,id',
            ]);
        } else {
            // Client can optionally provide avatar
            $rules['data.attributes.additionalData.avatar'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'data.attributes.provider.required' => 'The OAuth provider is required.',
            'data.attributes.provider.in' => 'The OAuth provider must be one of: google, facebook, apple.',
            'data.attributes.oauthToken.required' => 'The OAuth token is required.',
            'data.attributes.role.required' => 'The user role is required.',
            'data.attributes.role.in' => 'The role must be either client or cleaner.',
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

    public function role(): Role
    {
        return Role::from($this->input('data.attributes.role'));
    }

    public function deviceToken(): ?string
    {
        return $this->input('data.relationships.deviceTokens.data.attributes.token');
    }

    public function additionalData(): array
    {
        $data = [];

        // Get additional data from attributes
        $additionalDataInput = $this->input('data.attributes.additionalData', []);

        if (! empty($additionalDataInput)) {
            $data = array_merge($data, $additionalDataInput);
        }

        // Get relationship data
        if ($this->has('data.relationships.previousServices.data')) {
            $data['previousServices'] = collect($this->input('data.relationships.previousServices.data'))
                ->pluck('id')
                ->toArray();
        }

        if ($this->has('data.relationships.preferredServices.data')) {
            $data['preferredServices'] = collect($this->input('data.relationships.preferredServices.data'))
                ->pluck('id')
                ->toArray();
        }

        return $data;
    }
}
