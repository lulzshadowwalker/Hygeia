<?php

namespace App\Filters;

class BookingFilter extends QueryFilter
{
    protected $sortable = [
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'status',
    ];

    public function include($relationships)
    {
        $allowedRelationships = [];

        $relationships = explode(',', $relationships);
        $relationships = array_intersect($relationships, $allowedRelationships);

        return $this->builder->with($relationships);
    }

    public function createdAt($value)
    {
        $dates = explode(',', $value);

        if (count($dates) > 1) {
            return $this->builder->whereBetween('created_at', $dates);
        }

        return $this->builder->whereDate('created_at', $value);
    }

    public function updatedAt($value)
    {
        $dates = explode(',', $value);

        if (count($dates) > 1) {
            return $this->builder->whereBetween('updated_at', $dates);
        }

        return $this->builder->whereDate('updated_at', $value);
    }

    public function status($value)
    {
        $values = explode(',', $value);

        return $this->builder->whereIn('status', $values);
    }
}
