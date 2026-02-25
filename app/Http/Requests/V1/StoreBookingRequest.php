<?php

namespace App\Http\Requests\V1;

use App\Enums\BookingUrgency;
use App\Http\Requests\BaseFormRequest;
use App\Models\Pricing;
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
            'data.attributes.hasCleaningMaterials' => 'nullable|boolean',
            'data.attributes.hasCleaningSupplies' => 'nullable|boolean',
            'data.attributes.urgency' => [
                'required',
                new Enum(BookingUrgency::class),
            ],
            'data.attributes.location.description' => 'sometimes|nullable|string|max:255',
            'data.attributes.images' => 'sometimes|array|max:5',
            'data.attributes.images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'data.attributes.promocode' => 'nullable|string|max:255',
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
            if (
                ! $this->has('data.attributes.hasCleaningMaterials')
                && ! $this->has('data.attributes.hasCleaningSupplies')
            ) {
                $validator->errors()->add(
                    'data.attributes.hasCleaningMaterials',
                    'The has cleaning materials field is required.'
                );
            }

            $serviceId = $this->input('data.relationships.service.data.id');

            if (! $serviceId) {
                return;
            }

            $service = Service::find($serviceId);

            if (! $service) {
                return;
            }

            $pricingId = $this->input('data.relationships.pricing.data.id');
            $area = $this->input('data.attributes.area');

            if ($service->usesAreaRangePricing()) {
                if (empty($pricingId)) {
                    $validator->errors()->add(
                        'data.relationships.pricing.data.id',
                        'The pricing field is required for this service.'
                    );
                }

                if ($area !== null && $area !== '') {
                    $validator->errors()->add(
                        'data.attributes.area',
                        'The area field is not allowed for area-range priced services.'
                    );
                }

                if (! empty($pricingId)) {
                    $pricingBelongsToService = Pricing::query()
                        ->whereKey($pricingId)
                        ->where('service_id', $service->id)
                        ->exists();

                    if (! $pricingBelongsToService) {
                        $validator->errors()->add(
                            'data.relationships.pricing.data.id',
                            'The selected pricing does not belong to the selected service.'
                        );
                    }
                }

                return;
            }

            if ($area === null || $area === '') {
                $validator->errors()->add(
                    'data.attributes.area',
                    'The area field is required for per-meter priced services.'
                );
            }

            if (! empty($pricingId)) {
                $validator->errors()->add(
                    'data.relationships.pricing.data.id',
                    'The pricing field is not allowed for per-meter priced services.'
                );
            }

            if ($service->min_area !== null && is_numeric($area) && (float) $area < $service->min_area) {
                $validator->errors()->add(
                    'data.attributes.area',
                    'The area must be at least '.$service->min_area.' for this service.'
                );
            }

            if ($service->price_per_meter === null) {
                $validator->errors()->add(
                    'data.relationships.service.data.id',
                    'The selected service is not configured for per-meter pricing.'
                );
            }
        });
    }

    public function hasCleaningMaterials(): bool
    {
        if ($this->has('data.attributes.hasCleaningMaterials')) {
            return (bool) $this->input('data.attributes.hasCleaningMaterials');
        }

        return (bool) $this->input('data.attributes.hasCleaningSupplies', true);
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

    public function promocode(): ?string
    {
        $code = $this->input('data.attributes.promocode');

        if (! is_string($code)) {
            return null;
        }

        return trim($code);
    }
}
