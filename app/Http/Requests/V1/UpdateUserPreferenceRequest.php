<?php

namespace App\Http\Requests\V1;

use App\Enums\Language;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class UpdateUserPreferenceRequest extends BaseFormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.language' => ['sometimes', Rule::enum(Language::class)],
            'data.attributes.pushNotifications' => ['sometimes', 'boolean'],
            'data.attributes.emailNotifications' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.language' => 'The language field must be one of '.implode(', ', Language::values()),
        ];
    }

    public function mappedAttributes(array $extraAttributes = []): Collection
    {
        return $this->mapped([
            'data.attributes.language' => 'language',
            'data.attributes.pushNotifications' => 'push_notifications',
            'data.attributes.emailNotifications' => 'email_notifications',
        ], $extraAttributes);
    }
}
