<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateCleanerProfileRequest extends BaseFormRequest
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
            'data.attributes.yearsOfExperience' => 'sometimes|integer|min:0|max:100',
            'data.attributes.maxHoursPerWeek' => 'sometimes|integer|min:1|max:100',
            'data.attributes.serviceRadius' => 'sometimes|integer|min:1|max:1000',
            'data.attributes.availableDays' => 'sometimes|array|min:1',
            'data.attributes.availableDays.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'data.attributes.timeSlots' => 'sometimes|array|min:1',
            'data.attributes.timeSlots.*' => 'in:morning,afternoon,evening',
            'data.attributes.hasCleaningSupplies' => 'sometimes|boolean',
            'data.attributes.comfortableWithPets' => 'sometimes|boolean',
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
            'data.attributes.yearsOfExperience.integer' => 'The years of experience must be an integer.',
            'data.attributes.yearsOfExperience.min' => 'The years of experience must be at least 0.',
            'data.attributes.yearsOfExperience.max' => 'The years of experience may not be greater than 100.',
            'data.attributes.maxHoursPerWeek.integer' => 'The max hours per week must be an integer.',
            'data.attributes.maxHoursPerWeek.min' => 'The max hours per week must be at least 1.',
            'data.attributes.maxHoursPerWeek.max' => 'The max hours per week may not be greater than 100.',
            'data.attributes.serviceRadius.integer' => 'The service radius must be an integer.',
            'data.attributes.serviceRadius.min' => 'The service radius must be at least 1.',
            'data.attributes.serviceRadius.max' => 'The service radius may not be greater than 1000.',
            'data.attributes.availableDays.array' => 'The available days must be an array.',
            'data.attributes.availableDays.min' => 'You must select at least one available day.',
            'data.attributes.availableDays.*.in' => 'Invalid day selected.',
            'data.attributes.timeSlots.array' => 'The time slots must be an array.',
            'data.attributes.timeSlots.min' => 'You must select at least one time slot.',
            'data.attributes.timeSlots.*.in' => 'Invalid time slot selected.',
            'data.attributes.hasCleaningSupplies.boolean' => 'The has cleaning supplies field must be true or false.',
            'data.attributes.comfortableWithPets.boolean' => 'The comfortable with pets field must be true or false.',
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

    public function yearsOfExperience(): ?int
    {
        return $this->has('data.attributes.yearsOfExperience')
            ? (int) $this->input('data.attributes.yearsOfExperience')
            : null;
    }

    public function maxHoursPerWeek(): ?int
    {
        return $this->has('data.attributes.maxHoursPerWeek')
            ? (int) $this->input('data.attributes.maxHoursPerWeek')
            : null;
    }

    public function serviceRadius(): ?int
    {
        return $this->has('data.attributes.serviceRadius')
            ? (int) $this->input('data.attributes.serviceRadius')
            : null;
    }

    public function availableDays(): ?array
    {
        return $this->input('data.attributes.availableDays');
    }

    public function timeSlots(): ?array
    {
        return $this->input('data.attributes.timeSlots');
    }

    public function hasCleaningSupplies(): ?bool
    {
        return $this->has('data.attributes.hasCleaningSupplies')
            ? (bool) $this->input('data.attributes.hasCleaningSupplies')
            : null;
    }

    public function comfortableWithPets(): ?bool
    {
        return $this->has('data.attributes.comfortableWithPets')
            ? (bool) $this->input('data.attributes.comfortableWithPets')
            : null;
    }
}
