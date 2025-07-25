<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;

class PushNotification implements Arrayable
{
    public function __construct(
        public string $title,
        public string $body,
        public string $image = ''
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->image,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            body: $data['body'],
            image: $data['image'] ?? '',
        );
    }
}
