<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;

class StoreRegisterCleanerRequest extends BaseFormRequest
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
            'data.attributes.phone' => 'phone',
            'data.attributes.username' => ['required', 'string', 'max:255', 'unique:users,username', new UniqueUsernameRule],
            'data.attributes.email' => ['required', 'email', 'max:255', new UniqueEmailRule],
            'data.attributes.password' => 'required|string|min:8',
            'data.attributes.availableDays' => 'required|array|min:1',
            'data.attributes.availableDays.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'data.attributes.timeSlots' => 'required|array|min:1',
            'data.attributes.timeSlots.*' => 'in:morning,afternoon,evening',
            'data.attributes.maxHoursPerWeek' => 'required|integer|min:1|max:100',
            'data.attributes.acceptsUrgentOffers' => 'required|boolean',
            'data.attributes.yearsOfExperience' => 'required|integer|min:0|max:100',
            'data.attributes.hasCleaningSupplies' => 'required|boolean',
            'data.attributes.comfortableWithPets' => 'required|boolean',
            'data.attributes.serviceRadius' => 'required|integer|min:1|max:1000',
            'data.attributes.agreedToTerms' => 'accepted',
            'data.attributes.avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'data.attributes.idCard' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4048',
            'data.relationships.deviceTokens.data.attributes.token' => 'nullable|string',
            'data.relationships.previousServices.data' => 'sometimes|array',
            'data.relationships.previousServices.data.*.type' => 'in:service',
            'data.relationships.previousServices.data.*.id' => 'integer|exists:services,id',
            'data.relationships.preferredServices.data' => 'sometimes|array',
            'data.relationships.preferredServices.data.*.type' => 'in:service',
            'data.relationships.preferredServices.data.*.id' => 'integer|exists:services,id',
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
            'data.attributes.avatar.mimes' => 'The avatar must be a file of type: jpeg, png, jpg, gif, svg.',
            'data.attributes.avatar.max' => 'The avatar may not be greater than 4048 kilobytes.',
            'data.attributes.idCard.image' => 'The ID card must be an image.',
            'data.attributes.idCard.mimes' => 'The ID card must be a file of type: jpeg, png, jpg',
            'data.attributes.idCard.max' => 'The ID card may not be greater than 4048 kilobytes.',
            'data.attributes.agreedToTerms.accepted' => 'You must agree to the terms and conditions.',
            'data.attributes.availableDays.required' => 'The available days field is required.',
            'data.attributes.timeSlots.required' => 'The time slots field is required.',
            'data.attributes.maxHoursPerWeek.required' => 'The max hours per week field is required.',
            'data.attributes.acceptsUrgentOffers.required' => 'The accepts urgent offers field is required.',
            'data.attributes.yearsOfExperience.required' => 'The years of experience field is required.',
            'data.attributes.hasCleaningSupplies.required' => 'The has cleaning supplies field is required.',
            'data.attributes.comfortableWithPets.required' => 'The comfortable with pets field is required.',
            'data.attributes.serviceRadius.required' => 'The service radius field is required.',
            'data.relationships.previousServices.data.*.id.exists' => 'One or more of the selected previous services are invalid.',
            'data.relationships.preferredServices.data.*.id.exists' => 'One or more of the selected preferred services are invalid.',
            'data.relationships.previousServices.data.*.type.in' => 'The type of previous services must be service.',
            'data.relationships.preferredServices.data.*.type.in' => 'The type of preferred services must be service.',
            'data.relationships.previousServices.data.array' => 'The previous services must be an array.',
            'data.relationships.preferredServices.data.array' => 'The preferred services must be an array.',
            'data.relationships.deviceTokens.data.attributes.token.string' => 'The device token must be a string.',
            'data.relationships.deviceTokens.data.attributes.token.nullable' => 'The device token field is nullable.',
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

    public function deviceToken(): ?string
    {
        return $this->input('data.relationships.deviceTokens.data.attributes.token');
    }

    public function phone(): ?string
    {
        return $this->input('data.attributes.phone');
    }

    public function availableDays(): array
    {
        return $this->input('data.attributes.availableDays', []);
    }

    public function timeSlots(): array
    {
        return $this->input('data.attributes.timeSlots', []);
    }

    public function maxHoursPerWeek(): int
    {
        return (int) $this->input('data.attributes.maxHoursPerWeek', 0);
    }

    public function acceptsUrgentOffers(): bool
    {
        return $this->input('data.attributes.acceptsUrgentOffers');
    }

    public function yearsOfExperience(): int
    {
        return (int) $this->input('data.attributes.yearsOfExperience', 0);
    }

    public function hasCleaningSupplies(): bool
    {
        return (bool) $this->input('data.attributes.hasCleaningSupplies');
    }

    public function comfortableWithPets(): bool
    {
        return (bool) $this->input('data.attributes.comfortableWithPets');
    }

    public function serviceRadius(): int
    {
        return (int) $this->input('data.attributes.serviceRadius', 0);
    }

    public function agreedToTerms(): bool
    {
        return (bool) $this->input('data.attributes.agreedToTerms', false);
    }

    public function idCard()
    {
        return $this->file('data.attributes.idCard');
    }

    public function previousServices(): array
    {
        return collect($this->input('data.relationships.previousServices.data', []))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    public function preferredServices(): array
    {
        return collect($this->input('data.relationships.preferredServices.data', []))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }
}
