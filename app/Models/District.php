<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class District extends Model
{
    /** @use HasFactory<\Database\Factories\DistrictFactory> */
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'city_id'];

    public $translatable = ['name'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
