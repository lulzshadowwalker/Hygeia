<?php

namespace App\Http\Requests\V1;

use App\Enums\BookingUrgency;
use App\Enums\ServiceType;
use App\Http\Requests\BaseFormRequest;
use App\Models\Service;
use Illuminate\Validation\Rules\Enum;

class StoreBookingRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.hasCleaningMaterials' => 'required|boolean',
            'data.attributes.urgency' => [
                'required',
                new Enum(BookingUrgency::class),
            ],
            'data.attributes.location.description' => 'sometimes|nullable|string|max:255',
            'data.attributes.images' => 'sometimes|array|max:5',
            'data.attributes.images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'data.attributes.promocodeCode' => 'nullable|string|max:255',
            'data.attributes.location.lat' => 'sometimes|nullable|numeric|between:-90,90',
            'data.attributes.location.lng' => 'sometimes|nullable|numeric|between:-180,180',
            'data.attributes.scheduledAt' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (
                        $this->input('data.attributes.urgency') ===
                        BookingUrgency::Scheduled->value &&
                        empty($value)
                    ) {
                        $fail(
                            'The scheduledAt field is required when urgency is scheduled.',
                        );
                    }
                },
            ],
            'data.relationships.service.data.id' => 'required|exists:services,id',
            'data.relationships.pricing.data.id' => [
                'nullable',
                'exists:pricings,id',
            ],
            'data.attributes.area' => [
                'nullable',
                'numeric',
                'min:1',
            ],
            'data.relationships.extras.data.*.id' => 'sometimes|required|exists:extras,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $serviceId = $this->input('data.relationships.service.data.id');

            if (! $serviceId) {
                return;
            }

            $service = Service::find($serviceId);

            if (! $service) {
                return;
            }

            if ($service->type === ServiceType::Residential) {
                if (empty($this->input('data.attributes.area'))) {
                    $validator->errors()->add(
                        'data.attributes.area',
                        'The area field is required for residential services.'
                    );
                }

                return;
            }

            if (empty($this->input('data.relationships.pricing.data.id'))) {
                $validator->errors()->add(
                    'data.relationships.pricing.data.id',
                    'The pricing field is required for this service type.'
                );
            }
        });
    }

    public function hasCleaningMaterials(): bool
    {
        return $this->input('data.attributes.hasCleaningMaterials', false);
    }

    public function urgency(): BookingUrgency
    {
        return BookingUrgency::from($this->input('data.attributes.urgency'));
    }

    public function serviceId(): int
    {
        return (int) $this->input('data.relationships.service.data.id');
    }

    public function pricingId(): ?int
    {
        $id = $this->input('data.relationships.pricing.data.id');

        return $id ? (int) $id : null;
    }

    public function area(): ?float
    {
        return $this->input('data.attributes.area');
    }

    public function extraIds(): array
    {
        return collect($this->input('data.relationships.extras.data', []))
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    public function scheduledAt(): ?string
    {
        return $this->input('data.attributes.scheduledAt', null);
    }

    public function location(): ?string
    {
        return $this->input('data.attributes.location.description');
    }

    public function lat(): ?float
    {
        return $this->input('data.attributes.location.lat');
    }

    public function lng(): ?float
    {
        return $this->input('data.attributes.location.lng');
    }

    public function images(): array
    {
        return $this->file('data.attributes.images', []);
    }

    public function promocodeCode(): ?string
    {
        $code = $this->input('data.attributes.promocodeCode');

        if (! is_string($code)) {
            return null;
        }

        return trim($code);
    }
}
