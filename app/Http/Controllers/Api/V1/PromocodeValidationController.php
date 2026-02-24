<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ResponseBuilder;
use App\Enums\PromocodeValidationReason;
use App\Enums\ServiceType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StorePromocodeValidationRequest;
use App\Http\Resources\V1\PromocodeValidationResource;
use App\Models\Extra;
use App\Models\Pricing;
use App\Models\Service;
use App\Services\Pricing\BookingPricingData;
use App\Services\Pricing\BookingPricingEngine;
use App\Services\Promocodes\PromocodeValidator;
use Dedoc\Scramble\Attributes\Group;
use InvalidArgumentException;

#[Group('Promocodes')]
class PromocodeValidationController extends ApiController
{
    public function __construct(
        protected ResponseBuilder $response,
        private readonly PromocodeValidator $promocodeValidator,
        private readonly BookingPricingEngine $pricingEngine,
    ) {
        parent::__construct($response);
    }

    public function store(StorePromocodeValidationRequest $request): PromocodeValidationResource
    {
        $service = Service::findOrFail($request->serviceId());
        $pricing = null;

        if ($service->type !== ServiceType::Residential) {
            $pricing = Pricing::findOrFail($request->pricingId());
        }

        $extras = Extra::query()->whereIn('id', $request->extraIds())->get();
        $validation = $this->promocodeValidator->validate($request->code());

        if (! $validation->valid) {
            return PromocodeValidationResource::make([
                'valid' => false,
                'reason' => $validation->reason,
                'promocode' => null,
                'priceBreakdown' => null,
            ]);
        }

        try {
            $priceBreakdown = $this->pricingEngine->calculate(new BookingPricingData(
                service: $service,
                pricing: $pricing,
                area: $request->area(),
                extras: $extras,
                promocode: $validation->promocode,
                currency: $service->currency ?? 'HUF',
            ));
        } catch (InvalidArgumentException) {
            return PromocodeValidationResource::make([
                'valid' => false,
                'reason' => PromocodeValidationReason::BookingNotEligible,
                'promocode' => null,
                'priceBreakdown' => null,
            ]);
        } catch (\Throwable) {
            return PromocodeValidationResource::make([
                'valid' => false,
                'reason' => PromocodeValidationReason::Unknown,
                'promocode' => null,
                'priceBreakdown' => null,
            ]);
        }

        return PromocodeValidationResource::make([
            'valid' => true,
            'reason' => null,
            'promocode' => $validation->promocode,
            'priceBreakdown' => $priceBreakdown,
        ]);
    }
}
