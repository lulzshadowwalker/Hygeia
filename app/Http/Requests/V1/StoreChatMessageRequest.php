<?php

namespace App\Http\Requests\V1;

use App\Enums\MessageType;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreChatMessageRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.content' => 'required|string|max:65535',
            'data.attributes.type' => ['required', 'string', Rule::in(MessageType::cases())],
        ];
    }

    public function content(): string
    {
        return $this->input('data.attributes.content');
    }

    public function type(): string
    {
        return $this->input('data.attributes.type');
    }
}
