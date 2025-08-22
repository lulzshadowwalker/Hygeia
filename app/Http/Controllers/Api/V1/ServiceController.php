<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ServiceType;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Service;
use Illuminate\Validation\Rules\Enum;

class ServiceController extends Controller
{
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

    public function show(Service $service)
    {
        $service->load('pricings');

        return ServiceResource::make($service);
    }
}
