<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;

class DatabaseNotification implements Arrayable
{
    public function __construct(
        public string $title,
        public string $message,
        public array $data = []
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'data' => (object) $this->data,
        ];
    }
}
