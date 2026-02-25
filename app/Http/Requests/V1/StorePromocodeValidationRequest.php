<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use App\Models\Pricing;
use App\Models\Service;

class StorePromocodeValidationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data.attributes.code' => ['required', 'string', 'max:255'],
            'data.attributes.hasCleaningMaterials' => ['nullable', 'boolean'],
            'data.attributes.hasCleaningSupplies' => ['nullable', 'boolean'],
            'data.relationships.service.data.id' => ['required', 'exists:services,id'],
            'data.relationships.pricing.data.id' => ['nullable', 'exists:pricings,id'],
            'data.attributes.area' => ['nullable', 'numeric', 'min:1'],
            'data.relationships.extras.data.*.id' => ['sometimes', 'required', 'exists:extras,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $serviceId = $this->serviceId();

            if (! $serviceId) {
                return;
            }

            $service = Service::find($serviceId);

            if (! $service) {
                return;
            }

            $pricingId = $this->pricingId();
            $area = $this->area();

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

    public function code(): string
    {
        return (string) $this->input('data.attributes.code');
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

    public function hasCleaningMaterials(): bool
    {
        if ($this->has('data.attributes.hasCleaningMaterials')) {
            return (bool) $this->input('data.attributes.hasCleaningMaterials');
        }

        return (bool) $this->input('data.attributes.hasCleaningSupplies', true);
    }
}
