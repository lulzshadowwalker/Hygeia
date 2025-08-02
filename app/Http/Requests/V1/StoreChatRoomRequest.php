<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;

class StoreChatRoomRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.relationships.participants' => 'required|array',
            'data.relationships.participants.*.id' => 'required|exists:users,id',
        ];
    }

    public function participants(): array
    {
        return collect($this->input('data.relationships.participants', []))
            ->pluck('id')
            ->toArray();
    }
}
