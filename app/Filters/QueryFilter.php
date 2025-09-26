<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected $sortable = [];

    protected Request $request;

    protected Builder $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function filter($arr): Builder
    {
        foreach ($arr as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }

    public function sort($value)
    {
        $attrs = explode(',', $value);

        foreach ($attrs as $attr) {
            $direction = 'asc';

            if (substr($attr, 0, 1) === '-') {
                $direction = 'desc';
                $attr = substr($attr, 1);
            }

            if (! in_array($attr, $this->sortable) && ! array_key_exists($attr, $this->sortable)) {
                continue;
            }

            $column = $this->sortable[$attr] ?? $attr;
            $this->builder->orderBy($column, $direction);
        }
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->request->all() as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }
}
