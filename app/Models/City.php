<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory, HasTranslations;

    protected $fillable = ['name'];

    public $translatable = ['name'];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
