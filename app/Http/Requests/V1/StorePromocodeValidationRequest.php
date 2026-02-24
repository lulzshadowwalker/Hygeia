<?php

namespace App\Http\Requests\V1;

use App\Enums\ServiceType;
use App\Http\Requests\BaseFormRequest;
use App\Models\Service;

class StorePromocodeValidationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data.attributes.code' => ['required', 'string', 'max:255'],
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

            if ($service->type === ServiceType::Residential) {
                if (empty($this->area())) {
                    $validator->errors()->add(
                        'data.attributes.area',
                        'The area field is required for residential services.'
                    );
                }

                return;
            }

            if (empty($this->pricingId())) {
                $validator->errors()->add(
                    'data.relationships.pricing.data.id',
                    'The pricing field is required for this service type.'
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
}
