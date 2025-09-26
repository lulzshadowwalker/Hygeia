<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Spatie\Translatable\HasTranslations;

class District extends Model
{
    /** @use HasFactory<\Database\Factories\DistrictFactory> */
    use HasFactory, HasSpatial, HasTranslations;

    protected $fillable = ['name', 'city_id', 'boundaries'];

    public $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'boundaries' => Polygon::class,
        ];
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
