<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Collection;

class StoreSupportTicketRequest extends BaseFormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.subject' => 'sometimes|string',
            'data.attributes.message' => 'required|string',
        ];
    }

    public function mappedAttributes(array $extraAttributes = []): Collection
    {
        return $this->mapped([
            'data.attributes.subject' => 'subject',
            'data.attributes.message' => 'message',
        ], $extraAttributes);
    }
}
