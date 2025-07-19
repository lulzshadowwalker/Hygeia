<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class BaseFormRequest extends FormRequest
{
    protected function mapped(array $allowedAttributes = [], array $extraAttributes = []): Collection
    {
        $availabledAttributes = [];
        foreach ($allowedAttributes as $key => $value) {
            if ($this->has($key)) {
                $availabledAttributes[$value] = $this->input($key);
            }
        }

        $availabledAttributes = array_merge($availabledAttributes, $extraAttributes);

        return collect($availabledAttributes);
    }

    public function authorize(): bool
    {
        return true;
    }
}
