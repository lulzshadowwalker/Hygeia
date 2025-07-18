<?php

namespace App\Contracts;

use Illuminate\Http\Response;

interface ResponseBuilder
{
    public function data(array $data): self;
    public function meta(array $meta): self;
    public function message(string $message): self;
    public function error(string $title, string $detail, int $code = null, array $meta = [], string $pointer = null, string $indicator = null): self;
    public function build(int $code = Response::HTTP_OK): mixed;
}
