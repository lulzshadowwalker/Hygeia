<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ServiceType;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Service;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Validation\Rules\Enum;

#[Group('Services')]
class ServiceController extends Controller
{
    /**
     * List services
     *
     * Get a list of all services.
     *
     * @unauthenticated
     */
    public function index()
    {
        request()->validate([
            'type' => ['nullable', new Enum(ServiceType::class)],
        ]);

        $query = Service::query();

        if ($type = request('type')) {
            $query->where('type', $type);
        }

        $services = $query->with('pricings')
            ->get();

        return ServiceResource::collection($services);
    }

    /**
     * Get a service
     *
     * Get the details of a specific service.
     *
     * @unauthenticated
     */
    public function show(Service $service)
    {
        $service->load('pricings');

        return ServiceResource::make($service);
    }
}
